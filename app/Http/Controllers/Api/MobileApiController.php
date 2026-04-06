<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\User;
use App\Models\EyeHealthMetrics;
use App\Models\VirtualPet;
use Illuminate\Http\Request;

class MobileApiController extends Controller
{
    /**
     * POST /api/mobile/child/login
     * Authenticates via login_code to a specific child profile
     */
    public function loginChild(Request $request)
    {
        $request->validate([
            'login_code' => 'required|string|size:6',
            'device_id' => 'nullable|string',
        ]);

        $child = ChildProfile::where('login_code', $request->login_code)->first();

        if (!$child) {
            return response()->json(['message' => 'Invalid login code'], 401);
        }

        // Update device info
        if ($request->device_id) {
            $child->update(['device_id' => $request->device_id]);
        }

        // Create token for child
        $childUser = User::find($child->user_id);
        return response()->json([
            'message' => 'Child login successful',
            'child' => [
                'child_id' => $child->child_id,
                'name' => $childUser->name ?? 'Child',
                'birthdate' => $child->birthdate,
            ]
        ], 200);
    }

    /**
     * POST /api/mobile/child/{child_id}/sync/metrics
     * Ingests SQLite logs from phone into cloud database
     */
    public function syncMetrics(Request $request, $child_id)
    {
        $request->validate([
            'metrics' => 'required|array',
            'metrics.*.avg_blink_rate' => 'nullable|numeric',
            'metrics.*.avg_distance' => 'nullable|numeric',
            'metrics.*.strain_events' => 'nullable|integer',
            'metrics.*.screen_time_minutes' => 'nullable|integer',
            'metrics.*.timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $child = ChildProfile::find($child_id);
        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Verify auth is the child or their device
        if (auth()->id() != $child->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $inserted = 0;

        foreach ($request->metrics as $metric) {
            EyeHealthMetrics::create([
                'child_id' => $child_id,
                'avg_blink_rate' => $metric['avg_blink_rate'] ?? null,
                'avg_distance' => $metric['avg_distance'] ?? null,
                'strain_events' => $metric['strain_events'] ?? null,
                'screen_time_minutes' => $metric['screen_time_minutes'] ?? 0,
                'timestamp' => $metric['timestamp'],
            ]);
            $inserted++;
        }

        // Update last sync
        $child->update(['last_sync' => now()]);

        return response()->json([
            'message' => 'Metrics synced successfully',
            'inserted_records' => $inserted,
        ], 200);
    }

    /**
     * PUT /api/mobile/child/{child_id}/sync/pet
     * Backs up virtual pet progress with timestamp comparison
     */
    public function syncPet(Request $request, $child_id)
    {
        $request->validate([
            'xp_points' => 'required|integer|min:0',
            'currency' => 'required|integer|min:0',
            'current_streak_days' => 'nullable|integer|min:0',
            'last_streak_date' => 'nullable|date',
            'pet_state' => 'nullable|in:Healthy,Good,Critical,Dead',
            'device_timestamp' => 'required|date_format:Y-m-d H:i:s',
        ]);

        $child = ChildProfile::find($child_id);
        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Verify auth
        if (auth()->id() != $child->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pet = VirtualPet::where('child_id', $child_id)->first();
        if (!$pet) {
            return response()->json(['message' => 'Pet not found'], 404);
        }

        // Compare timestamps - only update if device data is newer
        $deviceTimestamp = strtotime($request->device_timestamp);
        $cloudTimestamp = strtotime($pet->updated_at);

        if ($deviceTimestamp >= $cloudTimestamp) {
            $pet->update([
                'xp_points' => $request->xp_points,
                'currency' => $request->currency,
                'current_streak_days' => $request->input('current_streak_days', $pet->current_streak_days),
                'last_streak_date' => $request->input('last_streak_date', $pet->last_streak_date),
                'pet_state' => $request->input('pet_state', $pet->pet_state),
                'updated_at' => now(),
            ]);

            return response()->json([
                'message' => 'Pet synced successfully',
                'synced' => true,
            ], 200);
        }

        return response()->json([
            'message' => 'Cloud data is newer, not updating',
            'synced' => false,
            'pet' => [
                'xp_points' => $pet->xp_points,
                'currency' => $pet->currency,
                'current_streak_days' => $pet->current_streak_days,
                'pet_state' => $pet->pet_state,
                'updated_at' => $pet->updated_at,
            ],
        ], 200);
    }

    /**
     * POST /api/mobile/child/{child_id}/sync/calibration
     * Uploads unique TFLite facial mesh baseline
     */
    public function syncCalibration(Request $request, $child_id)
    {
        $request->validate([
            'calibration_baseline' => 'required|json',
        ]);

        $child = ChildProfile::find($child_id);
        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Verify auth
        if (auth()->id() != $child->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $child->update([
            'calibration_baseline' => $request->calibration_baseline,
        ]);

        return response()->json([
            'message' => 'Calibration baseline synced successfully',
        ], 200);
    }

    /**
     * POST /api/mobile/device/register-token
     * Saves FCM token for push notifications
     */
    public function registerFCMToken(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:child_profile,child_id',
            'fcm_token' => 'required|string',
        ]);

        $child = ChildProfile::find($request->child_id);
        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Verify auth
        if (auth()->id() != $child->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $child->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'message' => 'FCM token registered successfully',
        ], 200);
    }

    /**
     * POST /api/mobile/device/ping
     * Lightweight heartbeat to update last_active
     */
    public function devicePing(Request $request)
    {
        $request->validate([
            'child_id' => 'required|exists:child_profile,child_id',
        ]);

        $child = ChildProfile::find($request->child_id);
        if (!$child) {
            return response()->json(['message' => 'Child not found'], 404);
        }

        // Verify auth
        if (auth()->id() != $child->user_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $child->update(['last_sync' => now()]);

        return response()->json([
            'message' => 'Ping received',
            'timestamp' => now()->toIso8601String(),
        ], 200);
    }

    /**
     * GET /api/mobile/config
     * Returns minimum app version requirement
     */
    public function getConfig()
    {
        return response()->json([
            'minimum_version' => '1.0.0',
            'latest_version' => '1.0.5',
            'force_update' => false,
            'api_version' => '1.0',
            'server_time' => now()->toIso8601String(),
        ], 200);
    }
}
