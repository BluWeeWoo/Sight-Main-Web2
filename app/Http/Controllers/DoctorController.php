<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        
        // Get doctor's patients (this would come from your database)
        // For now, using mock data
        $patients = [
            [
                'id' => 'PT-20206-001',
                'name' => 'Emma Rodriguez',
                'avatar' => 'ER',
                'compliance' => 75,
                'guardian' => 'Maria Rodriguez',
                'guardian_email' => 'maria@email.com',
            ],
            [
                'id' => 'PT-20206-002',
                'name' => 'Juan Dela Cruz',
                'avatar' => 'JD',
                'compliance' => 75,
                'guardian' => 'Juan Dela Cruz Sr.',
                'guardian_email' => 'juan@email.com',
            ],
            [
                'id' => 'PT-20206-003',
                'name' => 'Daniel Padilla',
                'avatar' => 'DP',
                'compliance' => 75,
                'guardian' => 'Daniel Padilla Sr.',
                'guardian_email' => 'daniel@email.com',
            ],
            [
                'id' => 'PT-20206-004',
                'name' => 'Kathryn Bernardo',
                'avatar' => 'KB',
                'compliance' => 75,
                'guardian' => 'Kathryn Bernardo Sr.',
                'guardian_email' => 'kathryn@email.com',
            ],
        ];

        // Get patient metrics (mock data)
        $patientMetrics = [
            'duration' => '2h 15m',
            'breaks' => '12/min',
            'distance' => '52cm',
            'strain_events' => 3,
            'blink_rate' => 'Good',
            'health_score' => 78,
        ];

        // Get compliance trends (mock data)
        $complianceTrends = [95, 92, 95, 92, 90, 78, 82];
        $screenTimeTrends = [
            'work' => [6, 7, 8, 6, 7, 2, 1],
            'leisure' => [3, 2, 2, 3, 2, 5, 4],
        ];
        $healthScoreTrends = [75, 76, 76, 76, 77, 77, 78];

        return view('doctor.dashboard', compact(
            'doctor',
            'patients',
            'patientMetrics',
            'complianceTrends',
            'screenTimeTrends',
            'healthScoreTrends'
        ));
    }

    /**
     * Get patient details
     */
    public function getPatient($patientId)
    {
        // This would fetch from database
        // For now returning mock data
        return response()->json([
            'id' => $patientId,
            'name' => 'Emma Rodriguez',
            'email' => 'emma@email.com',
            'phone' => '+234123456789',
            'age' => 8,
            'diagnosis' => 'Myopia Management',
            'created_at' => '2025-01-15',
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

        // Send email to patient/guardian
        // TODO: Implement email sending logic

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
        // Mock data - replace with actual database queries
        return response()->json([
            'dates' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'compliance' => [95, 92, 95, 92, 90, 78, 82],
            'average' => 89.3,
        ]);
    }

    /**
     * Get patient activity log
     */
    public function getActivityLog($patientId)
    {
        // Mock data
        $activities = [
            [
                'type' => 'Eye Exam Completed',
                'date' => 'August 1, 2025',
                'icon' => 'eye',
            ],
            [
                'type' => 'Health Metrics Updated',
                'date' => 'July 31, 2025',
                'icon' => 'chart',
            ],
            [
                'type' => 'Compliance Report Sent',
                'date' => 'July 29, 2025',
                'icon' => 'clock',
            ],
        ];

        return response()->json($activities);
    }
}
