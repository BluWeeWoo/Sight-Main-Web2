<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        $admin = Auth::user();

        // Get professionals/doctors (mock data)
        $professionals = [
            [
                'id' => 1,
                'name' => 'Doc McStuffins',
                'email' => 'doc@doctorssg.com',
                'location' => 'Lagos, NG',
                'clinic' => 'Martha Eye Center',
                'patients' => 249,
                'status' => 'active',
                'last_active' => '2 hours ago',
            ],
            [
                'id' => 2,
                'name' => 'Dr. Sarah Smith',
                'email' => 'sarah@healthcare.com',
                'location' => 'Abuja, NG',
                'clinic' => 'Central Medical',
                'patients' => 156,
                'status' => 'active',
                'last_active' => '1 hour ago',
            ],
            [
                'id' => 3,
                'name' => 'Dr. James Wilson',
                'email' => 'james@healthcare.com',
                'location' => 'Lagos, NG',
                'clinic' => 'City Hospital',
                'patients' => 203,
                'status' => 'inactive',
                'last_active' => '5 days ago',
            ],
            [
                'id' => 4,
                'name' => 'Dr. Amara Okafor',
                'email' => 'amara@healthcare.com',
                'location' => 'Lagos, NG',
                'clinic' => 'West End Clinic',
                'patients' => 178,
                'status' => 'active',
                'last_active' => '30 minutes ago',
            ],
            [
                'id' => 5,
                'name' => 'Dr. Chioma Nwosu',
                'email' => 'chioma@healthcare.com',
                'location' => 'Enugu, NG',
                'clinic' => 'Rainbow Hospital',
                'patients' => 92,
                'status' => 'suspended',
                'last_active' => '2 weeks ago',
            ],
        ];

        // Calculate stats
        $stats = [
            'total_professionals' => count($professionals),
            'active_professionals' => count(array_filter($professionals, fn($p) => $p['status'] === 'active')),
            'total_patients' => array_sum(array_column($professionals, 'patients')),
            'suspended' => count(array_filter($professionals, fn($p) => $p['status'] === 'suspended')),
        ];

        // Get other admins
        $otherAdmins = [
            [
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'status' => 'Active',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'status' => 'Active',
            ],
            [
                'name' => 'Mike Johnson',
                'email' => 'mike.johnson@example.com',
                'status' => 'Active',
            ],
        ];

        return view('admin.dashboard', compact('admin', 'professionals', 'stats', 'otherAdmins'));
    }

    /**
     * Get all professionals
     */
    public function getProfessionals()
    {
        // Fetch from database
        $professionals = User::where('role', 'doctor')->get();

        return response()->json($professionals);
    }

    /**
     * Add a new professional
     */
    public function addProfessional(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'phone' => 'required|string',
            'clinic' => 'required|string',
            'location' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $professional = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'doctor',
            'phone' => $validated['phone'],
            'clinic' => $validated['clinic'],
            'location' => $validated['location'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Professional added successfully',
            'professional' => $professional,
        ]);
    }

    /**
     * Edit a professional
     */
    public function editProfessional(Request $request, $professionalId)
    {
        $professional = User::findOrFail($professionalId);

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $professionalId,
            'phone' => 'required|string',
            'clinic' => 'required|string',
            'location' => 'required|string',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $professional->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Professional updated successfully',
            'professional' => $professional,
        ]);
    }

    /**
     * Delete a professional
     */
    public function deleteProfessional($professionalId)
    {
        $professional = User::findOrFail($professionalId);
        $professional->delete();

        return response()->json([
            'success' => true,
            'message' => 'Professional deleted successfully',
        ]);
    }

    /**
     * Update admin account settings
     */
    public function updateSettings(Request $request)
    {
        $admin = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'required|string',
            'current_password' => 'required_if:new_password,!=null',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect',
                ], 422);
            }

            $admin->password = Hash::make($request->new_password);
        }

        $admin->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    /**
     * Add another admin
     */
    public function addAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin added successfully',
            'admin' => $admin,
        ]);
    }

    /**
     * Remove an admin
     */
    public function removeAdmin($adminId)
    {
        // Prevent deleting the last admin
        $adminCount = User::where('role', 'admin')->count();
        if ($adminCount <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot remove the last admin',
            ], 422);
        }

        $admin = User::findOrFail($adminId);
        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin removed successfully',
        ]);
    }

    /**
     * Get dashboard statistics
     */
    public function getStats()
    {
        $professionals = User::where('role', 'doctor')->get();
        
        return response()->json([
            'total_professionals' => $professionals->count(),
            'active_professionals' => $professionals->where('status', 'active')->count(),
            'total_patients' => $professionals->sum('patients_count'),
            'suspended' => $professionals->where('status', 'suspended')->count(),
        ]);
    }
}
