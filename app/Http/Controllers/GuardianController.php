<?php

namespace App\Http\Controllers;

use App\Models\ChildProfile;
use App\Models\SessionLimits;
use App\Models\EyeHealthMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class GuardianController extends Controller
{
    /**
     * Show the guardian dashboard
     */
    public function dashboard(Request $request)
    {
        $childId = $request->query('child_id');
        
        if (!$childId) {
            return redirect()->route('guardian.children');
        }

        $child = ChildProfile::find($childId);

        if (!$child) {
            return redirect()->route('guardian.children')->with('error', 'Child not found');
        }

        // Verify guardian owns this child
        $guardianOwnsChild = DB::table('guardian_child_link')
            ->where('guardian_id', Auth::user()->guardian->guardian_id ?? null)
            ->where('child_id', $childId)
            ->exists();

        if (!$guardianOwnsChild) {
            return redirect()->route('guardian.children')->with('error', 'Unauthorized access to this child');
        }

        // Fetch today's metrics
        $today = Carbon::now()->toDateString();
        $todayMetrics = EyeHealthMetrics::where('child_id', $childId)
            ->whereDate('timestamp', $today)
            ->get();

        $todayScreenTime = $todayMetrics->sum('screen_time_minutes');
        $avgBlinkRate = (int) ($todayMetrics->avg('avg_blink_rate') ?? 0);
        $avgEyeDistance = (int) ($todayMetrics->avg('avg_distance') ?? 0);
        $strainEventsToday = $todayMetrics->sum('strain_events');

        // Fetch session limits
        $limits = SessionLimits::where('child_id', $childId)->first();

        // Calculate eye health score (mock calculation - in production, use your algorithm)
        $eyeHealthScore = $this->calculateEyeHealthScore($todayMetrics, $limits);

        // Get weekly metrics for analytics
        $weekStart = Carbon::now()->subDays(6)->toDateString();
        $weekMetrics = EyeHealthMetrics::where('child_id', $childId)
            ->whereDate('timestamp', '>=', $weekStart)
            ->get();

        $avgWeeklyScreenTime = (int) ($weekMetrics->avg('screen_time_minutes') ?? 0);
        $avgWeeklyBlinkRate = (int) ($weekMetrics->avg('avg_blink_rate') ?? 0);
        $avgWeeklyEyeDistance = (int) ($weekMetrics->avg('avg_distance') ?? 0);

        return view('guardian.dashboard', [
            'child' => $child,
            'childId' => $childId,
            'todayScreenTime' => $todayScreenTime,
            'avgBlinkRate' => $avgBlinkRate,
            'avgEyeDistance' => $avgEyeDistance,
            'strainEventsToday' => $strainEventsToday,
            'eyeHealthScore' => $eyeHealthScore,
            'limits' => $limits,
            'avgWeeklyScreenTime' => $avgWeeklyScreenTime,
            'avgWeeklyBlinkRate' => $avgWeeklyBlinkRate,
            'avgWeeklyEyeDistance' => $avgWeeklyEyeDistance,
        ]);
    }

    /**
     * Show list of guardian's children
     */
    public function children()
    {
        $guardian = Auth::user()->guardian;
        $children = $guardian->children()->with('user')->get();

        return view('guardian.children', compact('children'));
    }

    /**
     * Calculate eye health score based on metrics and limits
     */
    private function calculateEyeHealthScore($metrics, $limits)
    {
        if ($metrics->isEmpty()) {
            return 75; // Default score
        }

        $score = 100;

        // Deduct points for high screen time
        $avgScreenTime = $metrics->avg('screen_time_minutes');
        if ($avgScreenTime > ($limits->daily_limit_minutes ?? 120) * 0.8) {
            $score -= 15;
        }

        // Deduct points for low blink rate
        $avgBlinkRate = $metrics->avg('avg_blink_rate');
        if ($avgBlinkRate && $avgBlinkRate < 12) {
            $score -= 20;
        }

        // Deduct points for close eye distance
        $avgDistance = $metrics->avg('avg_distance');
        if ($avgDistance && $avgDistance < ($limits->critical_distance_threshold ?? 10)) {
            $score -= 25;
        }

        // Deduct points for strain events
        $strainEvents = $metrics->sum('strain_events');
        if ($strainEvents > 5) {
            $score -= 15;
        }

        return max(0, min(100, (int) $score));
    }
}
