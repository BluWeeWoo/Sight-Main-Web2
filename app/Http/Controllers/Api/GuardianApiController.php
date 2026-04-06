<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RuleEngineService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GuardianApiController extends Controller
{
    public function __construct(private readonly RuleEngineService $ruleEngineService)
    {
    }

    /**
     * POST /api/web/guardian/register
     * Creates a new Guardian account
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|string|min:8|confirmed',
            'contact_number' => 'nullable|string|max:11',
        ]);

        $result = $this->ruleEngineService->registerGuardian($validated);

        return response()->json($result['body'], $result['http_code']);
    }

    /**
     * POST /api/web/guardian/child/add
     * Creates a child_profile and generates a secure 6-digit login_code
     */
    public function addChild(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'birthdate' => 'required|date|before:today',
        ]);

        $result = $this->ruleEngineService->addChild(
            (int) Auth::id(),
            $validated
        );

        return response()->json($result['body'], $result['http_code']);
    }

    /**
     * PUT /api/web/guardian/child/{child_id}/limits
     * Updates distance thresholds and screen time limits
     */
    public function updateChildLimits(Request $request, $child_id)
    {
        $validated = $request->validate([
            'daily_limit_minutes' => 'nullable|integer|min:1|max:1440',
            'mode' => 'nullable|in:Strict,Relaxed',
            'harmful_distance_threshold' => 'nullable|numeric|min:1|max:100',
            'critical_distance_threshold' => 'nullable|numeric|min:1|max:100',
            'auto_enforce_breaks' => 'nullable|boolean',
        ]);

        $result = $this->ruleEngineService->updateChildLimits((int) $child_id, $validated);

        return response()->json($result['body'], $result['http_code']);
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

        $result = $this->ruleEngineService->linkDoctor(
            (int) Auth::id(),
            (int) $request->input('doctor_id'),
            (int) $request->input('child_id')
        );

        return response()->json($result['body'], $result['http_code']);
    }

    /**
     * DELETE /api/web/guardian/child/{child_id}
     * GDPR Compliance - cascade delete of child's data
     */
    public function deleteChild($child_id)
    {
        $result = $this->ruleEngineService->deleteChild((int) Auth::id(), (int) $child_id);

        return response()->json($result['body'], $result['http_code']);
    }
}
