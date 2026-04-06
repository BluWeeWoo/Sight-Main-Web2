<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\EyeHealthMetrics;
use App\Models\User;
use App\Models\VirtualPet;
use Illuminate\Support\Carbon;

class MetricsService
{
    public function loginChild(string $loginCode, ?string $deviceId): array
    {
        $child = ChildProfile::where('login_code', $loginCode)->first();

        if (!$child) {
            return $this->response('error', 'Invalid login code', null, ['login_code' => ['Invalid login code']], 401);
        }

        if ($deviceId) {
            $child->update(['device_id' => $deviceId]);
        }

        $childUser = User::find($child->user_id);

        return $this->response('success', 'Child login successful', [
            'child' => [
                'child_id' => $child->child_id,
                'name' => $childUser->name ?? 'Child',
                'birthdate' => $child->birthdate,
            ],
        ]);
    }

    public function syncMetrics(int $childId, int $authUserId, array $metrics): array
    {
        $child = ChildProfile::find($childId);

        if (!$child) {
            return $this->response('error', 'Child not found', null, ['child_id' => ['Child not found']], 404);
        }

        if ((int) $child->user_id !== (int) $authUserId) {
            return $this->response('error', 'Unauthorized', null, ['authorization' => ['Unauthorized']], 403);
        }

        $inserted = 0;

        foreach ($metrics as $metric) {
            EyeHealthMetrics::create([
                'child_id' => $childId,
                'avg_blink_rate' => $metric['avg_blink_rate'] ?? null,
                'avg_distance' => $metric['avg_distance'] ?? null,
                'strain_events' => $metric['strain_events'] ?? null,
                'screen_time_minutes' => $metric['screen_time_minutes'] ?? 0,
                'timestamp' => $metric['timestamp'],
            ]);
            $inserted++;
        }

        $child->update(['last_sync' => now()]);

        return $this->response('success', 'Metrics synced successfully', [
            'inserted_records' => $inserted,
        ]);
    }

    public function syncPet(int $childId, int $authUserId, array $payload): array
    {
        $child = ChildProfile::find($childId);

        if (!$child) {
            return $this->response('error', 'Child not found', null, ['child_id' => ['Child not found']], 404);
        }

        if ((int) $child->user_id !== (int) $authUserId) {
            return $this->response('error', 'Unauthorized', null, ['authorization' => ['Unauthorized']], 403);
        }

        $pet = VirtualPet::where('child_id', $childId)->first();

        if (!$pet) {
            return $this->response('error', 'Pet not found', null, ['pet' => ['Pet not found']], 404);
        }

        $deviceTimestamp = Carbon::createFromFormat('Y-m-d H:i:s', $payload['device_timestamp'])->getTimestamp();
        $cloudTimestamp = $pet->updated_at ? Carbon::parse($pet->updated_at)->getTimestamp() : 0;

        if ($deviceTimestamp >= $cloudTimestamp) {
            $pet->update([
                'xp_points' => $payload['xp_points'],
                'currency' => $payload['currency'],
                'current_streak_days' => $payload['current_streak_days'] ?? $pet->current_streak_days,
                'last_streak_date' => $payload['last_streak_date'] ?? $pet->last_streak_date,
                'pet_state' => $payload['pet_state'] ?? $pet->pet_state,
                'updated_at' => now(),
            ]);

            return $this->response('success', 'Pet synced successfully', [
                'synced' => true,
                'pet' => [
                    'xp_points' => $pet->xp_points,
                    'currency' => $pet->currency,
                    'current_streak_days' => $pet->current_streak_days,
                    'pet_state' => $pet->pet_state,
                    'updated_at' => optional($pet->updated_at)->toIso8601String(),
                ],
            ]);
        }

        return $this->response('success', 'Cloud data is newer, not updating', [
            'synced' => false,
            'pet' => [
                'xp_points' => $pet->xp_points,
                'currency' => $pet->currency,
                'current_streak_days' => $pet->current_streak_days,
                'pet_state' => $pet->pet_state,
                'updated_at' => optional($pet->updated_at)->toIso8601String(),
            ],
        ]);
    }

    public function syncCalibration(int $childId, int $authUserId, string $calibrationBaseline): array
    {
        $child = ChildProfile::find($childId);

        if (!$child) {
            return $this->response('error', 'Child not found', null, ['child_id' => ['Child not found']], 404);
        }

        if ((int) $child->user_id !== (int) $authUserId) {
            return $this->response('error', 'Unauthorized', null, ['authorization' => ['Unauthorized']], 403);
        }

        $child->update([
            'calibration_baseline' => $calibrationBaseline,
        ]);

        return $this->response('success', 'Calibration baseline synced successfully');
    }

    public function registerFcmToken(int $childId, int $authUserId, string $fcmToken): array
    {
        $child = ChildProfile::find($childId);

        if (!$child) {
            return $this->response('error', 'Child not found', null, ['child_id' => ['Child not found']], 404);
        }

        if ((int) $child->user_id !== (int) $authUserId) {
            return $this->response('error', 'Unauthorized', null, ['authorization' => ['Unauthorized']], 403);
        }

        $child->update(['fcm_token' => $fcmToken]);

        return $this->response('success', 'FCM token registered successfully');
    }

    public function devicePing(int $childId, int $authUserId): array
    {
        $child = ChildProfile::find($childId);

        if (!$child) {
            return $this->response('error', 'Child not found', null, ['child_id' => ['Child not found']], 404);
        }

        if ((int) $child->user_id !== (int) $authUserId) {
            return $this->response('error', 'Unauthorized', null, ['authorization' => ['Unauthorized']], 403);
        }

        $child->update(['last_sync' => now()]);

        return $this->response('success', 'Ping received', [
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    public function getConfig(): array
    {
        return $this->response('success', 'Config fetched successfully', [
            'minimum_version' => '1.0.0',
            'latest_version' => '1.0.5',
            'force_update' => false,
            'api_version' => '1.0',
            'server_time' => now()->toIso8601String(),
        ]);
    }

    private function response(string $status, string $message, ?array $data = [], $errors = null, int $httpCode = 200): array
    {
        return [
            'http_code' => $httpCode,
            'body' => [
                'status' => $status,
                'message' => $message,
                'data' => $data ?? new \stdClass(),
                'errors' => $errors,
            ],
        ];
    }
}
