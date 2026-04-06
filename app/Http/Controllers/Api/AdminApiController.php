<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AdminProfile;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminApiController extends Controller
{
    /**
     * GET /api/web/admin/analytics
     * Returns system-wide active users and platform health
     */
    public function getAnalytics()
    {
        // Verify admin role
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $activeDoctors = User::where('role', 'Doctor')->count();
        $activeGuardians = User::where('role', 'Guardian')->count();
        $activeChildren = User::where('role', 'Child')->count();
        $admins = User::where('role', 'Admin')->count();

        return response()->json([
            'platform_health' => [
                'total_users' => $activeDoctors + $activeGuardians + $activeChildren + $admins,
                'doctors' => $activeDoctors,
                'guardians' => $activeGuardians,
                'children' => $activeChildren,
                'admins' => $admins,
            ]
        ], 200);
    }

    /**
     * POST /api/web/admin/create-professional
     * Provisions a verified doctor account directly
     */
    public function createProfessional(Request $request)
    {
        // Verify admin role
        if (auth()->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'license_number' => 'required|string|unique:doctor_profile,license_number',
            'password' => 'required|string|min:8',
        ]);

        // Create doctor user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role' => 'Doctor',
        ]);

        // Create doctor profile (pre-validated)
        $doctor = DoctorProfile::create([
            'user_id' => $user->id,
            'license_number' => $request->license_number,
            'is_validated' => 1, // Admin-created doctors are automatically validated
        ]);

        return response()->json([
            'message' => 'Professional account created successfully',
            'doctor' => [
                'doctor_id' => $doctor->doctor_id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'license_number' => $doctor->license_number,
                'is_validated' => (bool)$doctor->is_validated,
            ]
        ], 201);
    }
}
