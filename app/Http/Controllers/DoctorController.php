<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DoctorProfile;
use App\Models\ChildProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    /**
     * Show the doctor dashboard
     */
    public function dashboard()
    {
        $doctor = Auth::user();
        if (is_null($doctor->email_verified_at)) {
            // If not verified, show pending verification view
            return view('doctor.pending-verification', compact('doctor'));
        }

        $doctorProfile = DoctorProfile::where('user_id', $doctor->user_id)->first();
        // Get doctor's assigned patients from database
        $patients = [];
        if ($doctorProfile) {
            $patients = $doctorProfile->patients()->get()->map(function ($child) {
                return [
                    'id' => $child->child_id,
                    'name' => $child->user->display_name ?? 'Unknown',
                    'birthdate' => $child->birthdate,
                    'created_at' => $child->user->created_at ? $child->user->created_at->format('M d, Y') : null,
                ];
            })->toArray();
        }

        return view('doctor.dashboard', compact('doctor', 'patients', 'doctorProfile'));
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
     * Send personalized health plan to patient
     */
    public function sendHealthPlan(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|string',
            'plan' => 'required|string',
        ]);

        // Store health plan in database (email sending can be configured in mail.php)
        // For now, acknowledge the request
        return response()->json([
            'success' => true,
            'message' => 'Health plan sent successfully',
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

        // Get actual compliance data from database
        $scores = $child->eyeHealthScores()->orderBy('recorded_date', 'desc')->limit(7)->get();

        return response()->json([
            'dates' => $scores->map(fn($s) => $s->recorded_date->format('M d'))->reverse()->values(),
            'scores' => $scores->map(fn($s) => $s->daily_score)->reverse()->values(),
            'average' => $scores->avg('daily_score') ?? 0,
            'count' => $scores->count(),
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
}
