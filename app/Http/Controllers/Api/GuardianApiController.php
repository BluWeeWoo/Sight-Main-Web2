<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\GuardianProfile;
use App\Models\ChildProfile;
use App\Models\SessionLimits;
use App\Models\VirtualPet;
use App\Models\ClinicianPatientLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class GuardianApiController extends Controller
{
    /**
     * POST /api/web/guardian/register
     * Creates a new Guardian account
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|string|min:8|confirmed',
            'contact_number' => 'nullable|string|max:11',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password_hash' => Hash::make($request->password),
            'role' => 'Guardian',
        ]);

        // Create guardian profile
        GuardianProfile::create([
            'user_id' => $user->id,
            'contact_number' => $request->contact_number ?? null,
        ]);

        return response()->json([
            'message' => 'Guardian registered successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ]
        ], 201);
    }

    /**
     * POST /api/web/guardian/child/add
     * Creates a child_profile and generates a secure 6-digit login_code
     */
    public function addChild(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'birthdate' => 'required|date|before:today',
        ]);

        // Get authenticated guardian
        $guardian = GuardianProfile::where('user_id', auth()->id())->first();
        if (!$guardian) {
            return response()->json(['message' => 'Guardian profile not found'], 404);
        }

        // Create child user
        $childUser = User::create([
            'name' => $request->name,
            'email' => 'child_' . uniqid() . '@sightapp.local',
            'password_hash' => Hash::make(uniqid()),
            'role' => 'Child',
        ]);

        // Generate unique 6-digit login code
        $loginCode = $this->generateUniqueLoginCode();

        // Create child profile
        $child = ChildProfile::create([
            'user_id' => $childUser->id,
            'birthdate' => $request->birthdate,
            'login_code' => $loginCode,
        ]);

        // Link guardian to child
        DB::table('guardian_child_link')->insert([
            'guardian_id' => $guardian->guardian_id,
            'child_id' => $child->child_id,
        ]);

        // Create default session limits
        SessionLimits::create([
            'child_id' => $child->child_id,
            'daily_limit_minutes' => 120,
            'mode' => 'Relaxed',
            'is_active' => true,
            'harmful_distance_threshold' => 30,
            'critical_distance_threshold' => 10,
            'auto_enforce_breaks' => true,
        ]);

        // Create virtual pet
        VirtualPet::create([
            'child_id' => $child->child_id,
            'pet_state' => 'Healthy',
            'currency' => 0,
            'xp_points' => 0,
        ]);

        return response()->json([
            'message' => 'Child profile created successfully',
            'child' => [
                'child_id' => $child->child_id,
                'name' => $childUser->name,
                'birthdate' => $child->birthdate,
                'login_code' => $loginCode,
            ]
        ], 201);
    }

    /**
     * PUT /api/web/guardian/child/{child_id}/limits
     * Updates distance thresholds and screen time limits
     */
    public function updateChildLimits(Request $request, $child_id)
    {
        $request->validate([
            'daily_limit_minutes' => 'nullable|integer|min:1|max:1440',
            'mode' => 'nullable|in:Strict,Relaxed',
            'harmful_distance_threshold' => 'nullable|numeric|min:1|max:100',
            'critical_distance_threshold' => 'nullable|numeric|min:1|max:100',
            'auto_enforce_breaks' => 'nullable|boolean',
        ]);

        $limits = SessionLimits::where('child_id', $child_id)->first();
        if (!$limits) {
            return response()->json(['message' => 'Session limits not found'], 404);
        }

        $limits->update([
            'daily_limit_minutes' => $request->input('daily_limit_minutes', $limits->daily_limit_minutes),
            'mode' => $request->input('mode', $limits->mode),
            'harmful_distance_threshold' => $request->input('harmful_distance_threshold', $limits->harmful_distance_threshold),
            'critical_distance_threshold' => $request->input('critical_distance_threshold', $limits->critical_distance_threshold),
            'auto_enforce_breaks' => $request->input('auto_enforce_breaks', $limits->auto_enforce_breaks),
            'updated_at' => now(),
        ]);

        return response()->json([
            'message' => 'Limits updated successfully',
            'updated_at' => $limits->updated_at->toIso8601String(),
        ], 200);
    }

    /**
     * POST /api/web/guardian/doctor/link
     * Sends a connection request to a specific clinician
     */
    public function linkDoctor(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctor_profile,doctor_id',
            'child_id' => 'required|exists:child_profile,child_id',
        ]);

        // Verify guardian owns this child
        $guardian = GuardianProfile::where('user_id', auth()->id())->first();
        $isOwner = DB::table('guardian_child_link')
            ->where('guardian_id', $guardian->guardian_id)
            ->where('child_id', $request->child_id)
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if link already exists
        $existing = ClinicianPatientLink::where('doctor_id', $request->doctor_id)
            ->where('child_id', $request->child_id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Link already exists'], 409);
        }

        $link = ClinicianPatientLink::create([
            'doctor_id' => $request->doctor_id,
            'child_id' => $request->child_id,
            'linkage_key' => bin2hex(random_bytes(16)),
            'is_active' => 0, // Pending acceptance
        ]);

        return response()->json([
            'message' => 'Doctor link request sent',
            'link_id' => $link->link_id,
        ], 201);
    }

    /**
     * DELETE /api/web/guardian/child/{child_id}
     * GDPR Compliance - cascade delete of child's data
     */
    public function deleteChild($child_id)
    {
        $child = ChildProfile::find($child_id);
        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Verify guardian owns this child
        $guardian = GuardianProfile::where('user_id', auth()->id())->first();
        $isOwner = DB::table('guardian_child_link')
            ->where('guardian_id', $guardian->guardian_id)
            ->where('child_id', $child_id)
            ->exists();

        if (!$isOwner) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Delete cascades through foreign keys
        $child->delete();
        $child->user()->delete();

        return response()->json(['message' => 'Child profile deleted successfully'], 200);
    }

    /**
     * Generate unique 6-digit login code
     */
    private function generateUniqueLoginCode()
    {
        do {
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (ChildProfile::where('login_code', $code)->exists());

        return $code;
    }
}
