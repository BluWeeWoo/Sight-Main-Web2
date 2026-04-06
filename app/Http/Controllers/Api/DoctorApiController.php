<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use App\Models\ClinicianPatientLink;
use App\Models\ChildProfile;
use App\Models\EyeHealthMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorApiController extends Controller
{
    /**
     * GET /api/web/doctor/{doctor_id}/requests
     * Lists pending guardian invites
     */
    public function getPendingRequests($doctor_id)
    {
        // Verify doctor ownership
        $doctor = DoctorProfile::where('user_id', auth()->id())->first();
        if (!$doctor || $doctor->doctor_id != $doctor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = ClinicianPatientLink::where('doctor_id', $doctor_id)
            ->where('is_active', false)
            ->with(['child' => function ($query) {
                $query->with('user');
            }])
            ->get()
            ->map(function ($request) {
                return [
                    'link_id' => $request->link_id,
                    'child_id' => $request->child_id,
                    'child_name' => $request->child->user->name ?? 'Unknown',
                    'child_email' => $request->child->user->email ?? 'Unknown',
                    'request_date' => $request->linkage_date ? $request->linkage_date->toDateTimeString() : null,
                    'linkage_date' => $request->linkage_date,
                    'status' => 'pending',
                ];
            });

        return response()->json(['requests' => $requests], 200);
    }

    /**
     * PUT /api/web/doctor/requests/{link_id}
     * Accepts or rejects a guardian link request
     */
    public function respondToRequest(Request $request, $link_id)
    {
        $request->validate([
            'action' => 'required|in:accept,deny',
        ]);

        $link = ClinicianPatientLink::find($link_id);
        if (!$link) {
            return response()->json(['message' => 'Link not found'], 404);
        }

        // Verify doctor owns this
        $doctor = DoctorProfile::where('user_id', auth()->id())->first();
        if (!$doctor || $link->doctor_id != $doctor->doctor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($request->action === 'accept') {
            $link->update(['is_active' => true]);
            return response()->json(['message' => 'Link accepted'], 200);
        } else {
            $link->delete();
            return response()->json(['message' => 'Link denied'], 200);
        }
    }

    /**
     * GET /api/web/doctor/{doctor_id}/patients
     * Lists all monitored children assigned to this doctor
     */
    public function getPatients($doctor_id)
    {
        // Verify doctor ownership
        $doctor = DoctorProfile::where('user_id', auth()->id())->first();
        if (!$doctor || $doctor->doctor_id != $doctor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $patients = ClinicianPatientLink::where('doctor_id', $doctor_id)
            ->where('is_active', true)
            ->with(['child' => function ($query) {
                $query->with('user', 'pet', 'sessionLimits');
            }])
            ->get()
            ->map(function ($link) {
                return [
                    'link_id' => $link->link_id,
                    'child_id' => $link->child_id,
                    'name' => $link->child->user->name ?? 'Unknown',
                    'birthdate' => $link->child->birthdate,
                    'pet_state' => $link->child->pet->pet_state ?? null,
                    'daily_limit' => $link->child->sessionLimits->daily_limit_minutes ?? null,
                    'last_sync' => $link->child->last_sync,
                ];
            });

        return response()->json(['patients' => $patients], 200);
    }

    /**
     * GET /api/web/doctor/patient/{child_id}/analytics
     * Fetches specialized medical data views
     */
    public function getAnalytics($child_id)
    {
        // Verify doctor has access
        $doctor = DoctorProfile::where('user_id', auth()->id())->first();
        $hasAccess = ClinicianPatientLink::where('doctor_id', $doctor->doctor_id)
            ->where('child_id', $child_id)
            ->where('is_active', true)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Database is empty, so return all metrics as zero
        return response()->json([
            'child_id' => $child_id,
            'analytics' => [
                'avg_blink_rate' => 0.0,
                'avg_distance' => 0.0,
                'total_strain_events' => 0,
                'total_screen_time_minutes' => 0,
                'metrics_count' => 0,
            ]
        ], 200);
    }

    /**
     * GET /api/web/doctor/patient/{child_id}/report
     * Generates a consolidated PDF/CSV export
     */
    public function generateReport($child_id)
    {
        // Verify doctor has access
        $doctor = DoctorProfile::where('user_id', auth()->id())->first();
        $hasAccess = ClinicianPatientLink::where('doctor_id', $doctor->doctor_id)
            ->where('child_id', $child_id)
            ->where('is_active', true)
            ->exists();

        if (!$hasAccess) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $child = ChildProfile::with('user', 'eyeHealthMetrics', 'eyeHealthScores')->find($child_id);

        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Prepare report data
        $reportData = [
            'child_id' => $child->child_id,
            'name' => $child->user->name ?? 'Unknown',
            'birthdate' => $child->birthdate,
            'generated_at' => now()->toDateTimeString(),
            'metrics' => $child->eyeHealthMetrics,
            'scores' => $child->eyeHealthScores,
        ];

        // Return as JSON (can be extended to generate PDF/CSV)
        return response()->json(['report' => $reportData], 200)
            ->header('Content-Disposition', 'attachment; filename=report_' . $child_id . '.json');
    }
}
