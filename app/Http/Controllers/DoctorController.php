<?php

namespace App\Http\Controllers;

use Carbon\CarbonPeriod;
use App\Models\DoctorProfile;
use App\Models\ChildProfile;
use App\Models\Prescription;
use App\Models\ClinicianPatientLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Show the doctor dashboard
     */
    public function dashboard(Request $request)
    {
        $doctor = Auth::user();
        if (is_null($doctor->email_verified_at)) {
            // If not verified, show pending verification view
            return view('doctor.pending-verification', compact('doctor'));
        }

        $doctorProfile = DoctorProfile::where('user_id', $doctor->user_id)->first();
        $patientModels = collect();

        if ($doctorProfile) {
            $patientModels = $doctorProfile->patients()
                ->wherePivot('is_active', true)
                ->with(['user', 'guardians.user'])
                ->get();

            // Fetch pending requests directly here
            $pendingRequests = ClinicianPatientLink::where('doctor_id', $doctorProfile->doctor_id)
                ->where('is_active', false)
                ->with(['child.user', 'child.guardians.user'])
                ->get()
                ->map(function ($link) {
                    $childUser = $link->child->user;
                    $guardianUser = $link->child->guardians->first()?->user;
                    return (object)[
                        'link_id' => $link->link_id,
                        'child_first_name' => $childUser?->first_name ?? 'Unknown',
                        'child_last_name' => $childUser?->last_name ?? '',
                        'guardian_first_name' => $guardianUser?->first_name ?? 'Unknown',
                        'guardian_last_name' => $guardianUser?->last_name ?? '',
                    ];
                });
        }

        $patients = $patientModels->map(function (ChildProfile $child) {
            $latestMetric = $child->eyeHealthMetrics()->orderByDesc('timestamp')->first();
            $latestScoreValue = $latestMetric?->health_score;

            return [
                'id' => $child->child_id,
                'name' => $child->user?->display_name ?? 'Unknown Patient',
                'guardian' => $child->guardians->first()?->user?->display_name ?? 'No guardian linked',
                'initials' => $this->getInitials($child->user?->display_name ?? 'Unknown Patient'),
                'birthdate' => $child->birthdate,
                'created_at' => $child->user?->created_at ? $child->user->created_at->format('M d, Y') : null,
                'patient_code' => 'PT-2026-' . str_pad((string) $child->child_id, 3, '0', STR_PAD_LEFT),
                'compliance_percent' => $latestScoreValue !== null ? (int) round($latestScoreValue) : null,
            ];
        })->values()->toArray();

        $selectedPatientId = (int) $request->query('patient', $patients[0]['id'] ?? 0);
        $selectedPatient = collect($patients)->firstWhere('id', $selectedPatientId) ?? ($patients[0] ?? null);
        $selectedPatientModel = $patientModels->firstWhere('child_id', $selectedPatient['id'] ?? null);
        $dashboardData = $selectedPatientModel ? $this->buildDashboardData($selectedPatientModel) : $this->emptyDashboardData();

        $activityPerPage = 6;
        $totalActivities = count($dashboardData['activity_items']);
        $activityTotalPages = max((int) ceil(max($totalActivities, 1) / $activityPerPage), 1);
        $activityPage = max((int) $request->query('activity_page', 1), 1);
        $activityPage = min($activityPage, $activityTotalPages);
        $activityOffset = ($activityPage - 1) * $activityPerPage;

        $dashboardData['activity_page_items'] = array_slice($dashboardData['activity_items'], $activityOffset, $activityPerPage);
        $dashboardData['activity_page'] = $activityPage;
        $dashboardData['activity_total_pages'] = $activityTotalPages;
        $dashboardData['activity_total_items'] = $totalActivities;

        return view('doctor.dashboard', compact('doctor', 'patients', 'doctorProfile', 'selectedPatient', 'selectedPatientId', 'dashboardData', 'pendingRequests'));
    }

    /**
     * Get patient details
     */
    public function getPatient($patientId)
    {
        $child = ChildProfile::find($patientId);
        
        if (!$child) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        return response()->json([
            'id' => $child->child_id,
            'name' => $child->user->display_name ?? 'Unknown',
            'birthdate' => $child->birthdate,
            'device_id' => $child->device_id,
            'login_code' => $child->login_code,
            'last_sync' => $child->last_sync,
            'created_at' => $child->user->created_at,
        ]);
    }

    /**
     * Accepts or rejects a guardian link request (Web Session)
     */
    public function respondToRequest(Request $request, $link_id)
    {
        $request->validate([
            'action' => 'required|in:accept,deny',
        ]);

        $link = ClinicianPatientLink::find($link_id);
        if (!$link) {
            return response()->json(['success' => false, 'message' => 'Link not found'], 404);
        }

        $doctor = DoctorProfile::where('user_id', Auth::id())->first();
        if (!$doctor || $link->doctor_id != $doctor->doctor_id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if ($request->action === 'accept') {
            $link->update(['is_active' => 1]);
            return response()->json(['success' => true, 'message' => 'Link accepted']);
        } else {
            $link->delete();
            return response()->json(['success' => true, 'message' => 'Link denied']);
        }
    }

    /**
     * Send personalized health plan to patient
     */
    public function sendHealthPlan(Request $request, $patientId)
    {
        $validated = $request->validate([
            'plan' => 'required|string|max:2000',
        ]);

        $doctorProfile = DoctorProfile::where('user_id', Auth::id())->first();
        if (! $doctorProfile) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor profile not found',
            ], 404);
        }

        $link = ClinicianPatientLink::where('doctor_id', $doctorProfile->doctor_id)
            ->where('child_id', (int) $patientId)
            ->where('is_active', true)
            ->first();

        if (! $link) {
            return response()->json([
                'success' => false,
                'message' => 'No active doctor-patient link found',
            ], 403);
        }

        $recommendation = Prescription::create([
            'link_id' => $link->link_id,
            'advice_text' => trim($validated['plan']),
            'date_issued' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Recommendation sent successfully',
            'recommendation_id' => $recommendation->recommendation_id,
        ]);
    }

    /**
     * Get patient compliance data
     */
    public function getComplianceData($patientId)
    {
        $child = ChildProfile::find($patientId);
        
        if (!$child) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $metrics = $child->eyeHealthMetrics()
            ->selectRaw('DATE(timestamp) as date, AVG(health_score) as avg_score')
            ->groupByRaw('DATE(timestamp)')
            ->orderByRaw('DATE(timestamp) DESC')
            ->limit(7)
            ->get();

        return response()->json([
            'dates' => $metrics->map(fn($m) => \Carbon\Carbon::parse($m->date)->format('M d'))->reverse()->values(),
            'scores' => $metrics->map(fn($m) => round((float) $m->avg_score))->reverse()->values(),
            'average' => round($metrics->avg('avg_score') ?? 0),
            'count' => $metrics->count(),
        ]);
    }

    /**
     * Get patient activity log
     */
    public function getActivityLog($patientId)
    {
        $child = ChildProfile::find($patientId);
        
        if (!$child) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        // Get actual activities from database
        $activities = [];

        // Add metric sync activity
        if ($child->last_sync) {
            $activities[] = [
                'type' => 'Metrics Synced',
                'date' => $child->last_sync->format('M d, Y'),
                'icon' => 'sync',
            ];
        }

        // Add profile creation activity
        if ($child->user && $child->user->created_at) {
            $activities[] = [
                'type' => 'Profile Created',
                'date' => $child->user->created_at->format('M d, Y'),
                'icon' => 'user',
            ];
        }

        return response()->json($activities);
    }

    private function buildDashboardData(ChildProfile $child): array
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        // UPDATED: Added AVG(health_score) and MAX(coins) to the query
        $metricRows = $child->eyeHealthMetrics()
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->selectRaw('DATE(`timestamp`) as metric_date, AVG(avg_blink_rate) as avg_blink_rate, AVG(avg_distance) as avg_distance, AVG(screen_time_minutes) as screen_time_minutes, SUM(strain_events) as strain_events, AVG(health_score) as health_score, MAX(coins) as coins')
            ->groupByRaw('DATE(`timestamp`)')
            ->orderBy('metric_date')
            ->get()
            ->keyBy('metric_date');

        $labels = [];
        $blinkRates = [];
        $distances = [];
        $screenTimes = [];
        $strainEvents = [];
        $healthScores = [];
        $coinsData = [];
        $activities = [];
        $previousScore = null;

        foreach (CarbonPeriod::create($startDate->toDateString(), $endDate->toDateString()) as $date) {
            $key = $date->format('Y-m-d');
            $metric = $metricRows->get($key);

            $labels[] = $date->format('D');
            $blinkRates[] = $metric ? round((float) $metric->avg_blink_rate, 1) : null;
            $distances[] = $metric ? round((float) $metric->avg_distance, 1) : null;
            $screenTimes[] = $metric ? round((float) $metric->screen_time_minutes, 1) : null;
            $strainEvents[] = $metric ? (int) $metric->strain_events : null;
            
            // Extract the new Gamification fields
            $currentHealth = $metric && $metric->health_score !== null ? round((float) $metric->health_score, 1) : null;
            $healthScores[] = $currentHealth;
            $coinsData[] = $metric && $metric->coins !== null ? (int) $metric->coins : null;

            if ($metric) {
                $blinkRate = (float) $metric->avg_blink_rate;
                $distance = (float) $metric->avg_distance;
                $screenTime = (float) $metric->screen_time_minutes;
                $strainEventCount = (int) $metric->strain_events;

                if ($screenTime <= 120 && $strainEventCount <= 1) {
                    $activities[] = [
                        'title' => 'Target screen-time window met',
                        'detail' => $this->formatActivityDetail('Average screen time stayed within the 2 hour target.', $date),
                        'timestamp' => $date->copy()->setTime(15, 0),
                        'icon' => 'clock',
                    ];
                }

                if ($blinkRate < 12) {
                    $activities[] = [
                        'title' => 'Low blink rate detected',
                        'detail' => $this->formatActivityDetail('Average blink rate dropped to ' . round($blinkRate, 1) . ' per minute.', $date),
                        'timestamp' => $date->copy()->setTime(12, 30),
                        'icon' => 'activity',
                    ];
                }

                if ($distance < 40) {
                    $activities[] = [
                        'title' => 'Viewing distance too close',
                        'detail' => $this->formatActivityDetail('Average viewing distance fell to ' . round($distance, 1) . ' cm.', $date),
                        'timestamp' => $date->copy()->setTime(11, 0),
                        'icon' => 'eye',
                    ];
                }

                if ($strainEventCount > 0) {
                    $activities[] = [
                        'title' => 'Strain events recorded',
                        'detail' => $this->formatActivityDetail($strainEventCount . ' strain event' . ($strainEventCount === 1 ? '' : 's') . ' were logged for the day.', $date),
                        'timestamp' => $date->copy()->setTime(9, 30),
                        'icon' => 'exclamation-triangle',
                    ];
                }
            }

            if ($currentHealth !== null && $previousScore !== null) {
                $difference = round($currentHealth - $previousScore, 1);

                if (abs($difference) >= 2) {
                    $activities[] = [
                        'title' => $difference > 0 ? 'Eye health score improved' : 'Eye health score dipped',
                        'detail' => $this->formatActivityDetail('Score changed by ' . ($difference > 0 ? '+' : '') . $difference . ' points compared with the previous day.', $date),
                        'timestamp' => $date->copy()->setTime(8, 0),
                        'icon' => $difference > 0 ? 'arrow-up-right' : 'arrow-down-right',
                    ];
                }
            }

            if ($currentHealth !== null) {
                $previousScore = $currentHealth;
            }
        }

        if ($child->last_sync) {
            $activities[] = [
                'title' => 'Metrics synced',
                'detail' => 'Last sync completed on ' . $child->last_sync->format('M d, Y \a\t g:i A') . '.',
                'timestamp' => $child->last_sync,
                'icon' => 'sync',
            ];
        }

        if ($child->user?->created_at) {
            $activities[] = [
                'title' => 'Profile created',
                'detail' => 'Patient profile created on ' . $child->user->created_at->format('M d, Y') . '.',
                'timestamp' => $child->user->created_at,
                'icon' => 'person',
            ];
        }

        usort($activities, function (array $left, array $right) {
            return $right['timestamp'] <=> $left['timestamp'];
        });

        $activities = array_map(function (array $activity) {
            return [
                'title' => $activity['title'],
                'detail' => $activity['detail'],
                'icon' => $activity['icon'],
            ];
        }, $activities);

        $metricValues = $metricRows->values();
        
        // Calculate averages based entirely on the new metrics payload
        $healthScoreValue = $this->latestNumericValue($metricValues->pluck('health_score')->all());
        $averageScore = $this->averageNumericValue($metricValues->pluck('health_score')->all());
        $latestCoins = $this->latestNumericValue($metricValues->pluck('coins')->all());
        
        $averageBlinkRate = $this->averageNumericValue($metricValues->pluck('avg_blink_rate')->all());
        $averageDistance = $this->averageNumericValue($metricValues->pluck('avg_distance')->all());
        $averageScreenTime = $this->averageNumericValue($metricValues->pluck('screen_time_minutes')->all());
        $daysWithinTarget = $metricValues->filter(function ($row) {
            return (float) $row->screen_time_minutes <= 120 && (int) $row->strain_events <= 1;
        })->count();

        return [
            'health_grade' => $this->gradeFromScore($healthScoreValue ?? $averageScore),
            'health_score' => $healthScoreValue ?? $averageScore,
            'health_score_display' => $this->formatPercentage($healthScoreValue ?? $averageScore),
            'latest_coins' => $latestCoins ?? 0, // ADDED
            'screen_time_display' => $this->formatDuration($averageScreenTime),
            'blink_rate_display' => $this->formatRate($averageBlinkRate),
            'distance_display' => $this->formatDistance($averageDistance),
            'target_days_display' => $daysWithinTarget . ' / ' . max(count($labels), 1),
            'low_blink_events' => $metricValues->filter(fn ($row) => (float) $row->avg_blink_rate < 12)->count(),
            'distance_violations' => $metricValues->filter(fn ($row) => (float) $row->avg_distance < 40)->count(),
            'labels' => $labels,
            'blink_rates' => $blinkRates,
            'distances' => $distances,
            'screen_times' => $screenTimes,
            'strain_events' => $strainEvents,
            'health_scores' => $healthScores,
            'coins_data' => $coinsData, // ADDED
            'activity_items' => $activities,
            'has_data' => ! empty($labels) && $metricValues->isNotEmpty(),
        ];
    }

    private function emptyDashboardData(): array
    {
        return [
            'health_grade' => 'No Data',
            'health_score' => null,
            'health_score_display' => '--',
            'screen_time_display' => '--',
            'blink_rate_display' => '--',
            'distance_display' => '--',
            'target_days_display' => '0 / 0',
            'low_blink_events' => 0,
            'distance_violations' => 0,
            'labels' => [],
            'blink_rates' => [],
            'distances' => [],
            'screen_times' => [],
            'strain_events' => [],
            'health_scores' => [],
            'activity_items' => [],
            'has_data' => false,
        ];
    }

    private function getInitials(?string $name): string
    {
        $parts = preg_split('/\s+/', trim((string) $name)) ?: [];

        if (count($parts) === 0 || $parts[0] === '') {
            return 'PT';
        }

        $first = strtoupper(substr($parts[0], 0, 1));
        $last = count($parts) > 1 ? strtoupper(substr($parts[count($parts) - 1], 0, 1)) : '';

        return trim($first . $last) ?: 'PT';
    }

    private function averageNumericValue(array $values): ?float
    {
        $filtered = array_values(array_filter($values, fn ($value) => $value !== null));

        if ($filtered === []) {
            return null;
        }

        return round(array_sum(array_map('floatval', $filtered)) / count($filtered), 1);
    }

    private function latestNumericValue(array $values): ?float
    {
        $filtered = array_values(array_filter($values, fn ($value) => $value !== null));

        if ($filtered === []) {
            return null;
        }

        return round((float) end($filtered), 1);
    }

    private function gradeFromScore(?float $score): string
    {
        if ($score === null) {
            return 'No Data';
        }

        if ($score >= 90) {
            return 'Excellent';
        }

        if ($score >= 80) {
            return 'Good';
        }

        if ($score >= 70) {
            return 'Fair';
        }

        return 'Needs Attention';
    }

    private function formatPercentage(?float $value): string
    {
        if ($value === null) {
            return '--';
        }

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . '%';
    }

    private function formatDuration(?float $minutes): string
    {
        if ($minutes === null) {
            return '--';
        }

        $roundedMinutes = (int) round($minutes);
        $hours = intdiv($roundedMinutes, 60);
        $remainingMinutes = $roundedMinutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return $hours . 'h ' . $remainingMinutes . 'm';
        }

        if ($hours > 0) {
            return $hours . 'h';
        }

        return $roundedMinutes . 'm';
    }

    private function formatRate(?float $value): string
    {
        if ($value === null) {
            return '--';
        }

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . '/min';
    }

    private function formatDistance(?float $value): string
    {
        if ($value === null) {
            return '--';
        }

        return rtrim(rtrim(number_format($value, 1, '.', ''), '0'), '.') . 'cm';
    }

    private function formatActivityDetail(string $detail, $date): string
    {
        return $detail . ' ' . $date->format('M d, Y') . '.';
    }
}
