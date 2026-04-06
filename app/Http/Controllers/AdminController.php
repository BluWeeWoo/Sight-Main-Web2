<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ChildProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Show the admin dashboard
     */
    public function dashboard()
    {
        $admin = Auth::user();
        if (is_null($admin->email_verified_at)) {
            // If not verified, show pending verification view
            return view('admin.pending-verification', compact('admin'));
        }

        // Get professionals/doctors from database
        $professionals = User::where('role', 'doctor')->get()->map(function ($professional) {
            // Only get real data from database, no mock fallbacks
            return [
                'id' => $professional->user_id,
                'name' => $professional->name,
                'email' => $professional->email,
                'phone' => $professional->phone,
                'location' => $professional->location,
                'clinic' => $professional->clinic,
                'specialty' => $professional->specialty,
                'license_number' => $professional->license_number,
                'is_verified' => !is_null($professional->email_verified_at),
                'status' => strtolower($professional->status ?? 'active'),
                'created_at' => $professional->created_at ? $professional->created_at->format('M d, Y') : null,
                'patients' => 0, // Added for UI consistency
                'last_active' => 'Never', // Added for UI consistency
                'joined_date' => $professional->created_at ? $professional->created_at->format('M d, Y') : 'N/A',
            ];
        })->toArray();

        // Calculate stats - only from real data
        $stats = [
            'total_professionals' => count($professionals),
            'active_professionals' => count(array_filter($professionals, fn($p) => strtolower($p['status']) === 'active')),
        ];

        // Get other admins
        $otherAdmins = User::where('role', 'admin')->where('user_id', '!=', $admin->user_id)->get()->map(function ($user) {
            return [
                'id' => $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'status' => strtolower($user->status ?? 'active'),
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
            'email' => 'required|email|unique:user',
            'phone' => 'required|string',
            'clinic' => 'required|string',
            'specialty' => 'required|string',
            'license_number' => 'required|string',
            'location' => 'required|string',
        ]);

        // Generate a temporary password
        $tempPassword = Str::random(12);

        $professional = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($tempPassword),
            'role' => 'doctor',
            'phone' => $validated['phone'],
            'clinic' => $validated['clinic'],
            'location' => $validated['location'],
            'specialty' => $validated['specialty'],
            'license_number' => $validated['license_number'],
            'status' => 'pending',
            'email_verified_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Professional added successfully.',
            'temp_password' => $tempPassword,
            'professional' => [
                'id' => $professional->user_id,
                'name' => $professional->name,
                'email' => $professional->email,
                'phone' => $professional->phone,
                'clinic' => $professional->clinic,
                'specialty' => $professional->specialty,
                'license_number' => $professional->license_number,
                'is_verified' => false,
                'location' => $professional->location,
                'status' => 'pending',
                'patients' => 0,
                'last_active' => 'Just now',
                'joined_date' => $professional->created_at ? $professional->created_at->format('M d, Y') : now()->format('M d, Y'),
                'created_at' => $professional->created_at ? $professional->created_at->format('M d, Y') : now()->format('M d, Y'),
            ],
        ]);
    }

    /**
     * Edit a professional
     */
    public function editProfessional(Request $request, $professionalId)
    {
        $professional = User::findOrFail($professionalId);

        $admin = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:user,email,' . $professional->user_id . ',user_id',
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
            'email' => 'required|email|unique:user,email,' . $admin->user_id . ',user_id',
            'phone' => 'nullable|string',
            'current_password' => 'required_with:new_password',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Verify current password if changing password
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $admin->password_hash)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'current_password' => ['The provided password does not match your current password.'],
                ]);
            }
            $admin->password_hash = Hash::make($request->new_password);
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => 'admin',
            'status' => 'active',
            'email_verified_at' => now(), // Allow immediate login for added admins
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Admin added successfully',
            'admin' => [
                'id' => $admin->user_id,
                'name' => $admin->name,
                'email' => $admin->email,
                'status' => $admin->status,
            ],
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
            'total_patients' => ChildProfile::count(),
            'suspended' => $professionals->filter(fn($p) => strtolower($p->status ?? '') === 'suspended')->count(),
        ]);
    }

    /**
     * Toggle user status (active/inactive)
     */
    public function toggleUserStatus($userId)
    {
        $user = User::findOrFail($userId);

        if ($user->user_id === Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot disable your own account.',
            ], 422);
        }

        $user->status = (strtolower($user->status ?? 'active') === 'active') ? 'inactive' : 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Account ' . ($user->status === 'active' ? 'enabled' : 'disabled') . ' successfully.',
            'status' => $user->status,
        ]);
    }

    /**
     * Toggle professional verification status
     */
    public function toggleVerification($userId)
    {
        $user = User::findOrFail($userId);

        // Toggle between verified (now) and unverified (null)
        $user->email_verified_at = $user->email_verified_at ? null : now();
        $user->save();

        return response()->json([
            'success' => true,
            'is_verified' => !is_null($user->email_verified_at),
            'message' => $user->email_verified_at ? 'Professional verified successfully.' : 'Verification revoked successfully.',
        ]);
    }
}
