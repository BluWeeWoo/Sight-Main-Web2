<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChildProfile;
use App\Models\SessionLimits;
use App\Models\EyeHealthMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class SharedApiController extends Controller
{
    /**
     * POST /api/shared/login
     * Authenticates Guardians and Admins via email/password
     * Enforces 3-Strike UDS (User Data Security) lockout rule
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
            ->whereIn('role', ['Guardian', 'Admin'])
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Check lockout (3-strike rule)
        if ($user->locked_until && now() < $user->locked_until) {
            return response()->json([
                'message' => 'Account temporarily locked due to failed login attempts',
                'locked_until' => $user->locked_until
            ], 429);
        }

        // Verify password
        if (!Hash::check($request->password, $user->password_hash)) {
            $user->increment('failed_login_attempts');
            
            // Lock account after 3 failed attempts
            if ($user->failed_login_attempts >= 3) {
                $user->update(['locked_until' => now()->addMinutes(15)]);
                return response()->json([
                    'message' => 'Account locked due to 3 failed login attempts. Try again in 15 minutes.'
                ], 429);
            }
            
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Reset failed attempts on success
        $user->update([
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);

        // Create token
        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
                'name' => $user->name ?? null,
            ]
        ], 200);
    }

    /**
     * POST /api/shared/logout
     * Revokes the active JWT/Sanctum token
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    /**
     * GET /api/shared/child/{child_id}/limits
     * Fetches the current tracking rules and updated_at timestamp
     */
    public function getChildLimits($child_id)
    {
        $limits = SessionLimits::where('child_id', $child_id)->first();

        if (!$limits) {
            return response()->json(['message' => 'Limits not found'], 404);
        }

        return response()->json([
            'limit_id' => $limits->limit_id,
            'child_id' => $limits->child_id,
            'daily_limit_minutes' => $limits->daily_limit_minutes,
            'mode' => $limits->mode,
            'is_active' => (bool)$limits->is_active,
            'harmful_distance_threshold' => $limits->harmful_distance_threshold,
            'critical_distance_threshold' => $limits->critical_distance_threshold,
            'auto_enforce_breaks' => (bool)$limits->auto_enforce_breaks,
            'updated_at' => $limits->updated_at->toIso8601String(),
        ], 200);
    }

    /**
     * GET /api/shared/child/{child_id}/metrics
     * Retrieves paginated or date-ranged eye_health_metrics for UI charts
     */
    public function getChildMetrics(Request $request, $child_id)
    {
        $request->validate([
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after:from_date',
        ]);

        $query = EyeHealthMetrics::where('child_id', $child_id);

        // Date range filtering
        if ($request->has('from_date')) {
            $query->whereDate('timestamp', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('timestamp', '<=', $request->to_date);
        }

        $perPage = $request->input('per_page', 50);
        $metrics = $query->orderBy('timestamp', 'desc')->paginate($perPage);

        return response()->json([
            'data' => $metrics->items(),
            'pagination' => [
                'current_page' => $metrics->currentPage(),
                'total_pages' => $metrics->lastPage(),
                'total_records' => $metrics->total(),
                'per_page' => $metrics->perPage(),
            ]
        ], 200);
    }
}
