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

        // Get professionals/doctors from database
        $professionals = User::where('role', 'doctor')->get()->map(function ($professional) {
            return [
                'id' => $professional->id,
                'name' => $professional->name,
                'email' => $professional->email,
                'phone' => $professional->phone ?? '+234 000 000 0000',
                'location' => $professional->location ?? 'Lagos, NG',
                'clinic' => $professional->clinic ?? 'N/A',
                'specialty' => $professional->specialty ?? 'General Practitioner',
                'license_number' => $professional->license_number ?? 'LICENSE-2024-001',
                'patients' => rand(50, 300), // Mock data - replace with actual patient count logic
                'status' => strtolower($professional->status ?? 'active'),
                'last_active' => '2 hours ago',
                'joined_date' => $professional->created_at ? $professional->created_at->format('M d, Y') : 'Jan 15, 2024',
            ];
        })->toArray();

        // Calculate stats
        $stats = [
            'total_professionals' => count($professionals),
            'active_professionals' => count(array_filter($professionals, fn($p) => strtolower($p['status']) === 'active')),
            'total_patients' => array_sum(array_column($professionals, 'patients')) ?? 0,
            'suspended' => count(array_filter($professionals, fn($p) => strtolower($p['status']) === 'suspended')),
        ];

        // Get other admins
        $otherAdmins = User::where('role', 'admin')->where('id', '!=', $admin->id)->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => 'Active',
            ];
        })->toArray();

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
            'specialty' => 'required|string',
            'license_number' => 'required|string',
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
            'specialty' => $validated['specialty'],
            'license_number' => $validated['license_number'],
            'status' => 'active',
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
            'specialty' => 'required|string',
            'license_number' => 'required|string',
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'phone' => 'nullable|string',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $admin->password)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }
            $admin->password = Hash::make($request->new_password);
        }

        $admin->name = $validated['name'];
        $admin->email = $validated['email'];
        $admin->phone = $validated['phone'];
        $admin->save();

        return redirect()->back()->with('success', 'Settings updated successfully');
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
            'active_professionals' => $professionals->filter(fn($p) => strtolower($p->status ?? '') === 'active')->count(),
            'total_patients' => 0, // Placeholder: sum patients once relationship is defined
            'suspended' => $professionals->filter(fn($p) => strtolower($p->status ?? '') === 'suspended')->count(),
        ]);
    }
}
