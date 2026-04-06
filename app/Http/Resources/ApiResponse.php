<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Standard success response
     */
    public static function success(array $data = [], string $message = 'Success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Standard error response
     */
    public static function error(string $message = 'Error', int $code = 400, array $data = []): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Not found response
     */
    public static function notFound(string $resource = 'Resource'): JsonResponse
    {
        return self::error("{$resource} not found", 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(): JsonResponse
    {
        return self::error('Unauthorized', 401);
    }

    /**
     * Validation error response
     */
    public static function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 422);
    }
}
