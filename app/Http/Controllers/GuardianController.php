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
        $eyeHealthScore = 100; // Default
        if ($todayMetrics->isNotEmpty()) {
            $latestMetric = $todayMetrics->sortByDesc('timestamp')->first();
            $eyeHealthScore = $latestMetric->health_score ?? 100;
        }

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
}
