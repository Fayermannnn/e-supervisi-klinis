<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class ApiResponse
{
    /**
     * Bentuk envelope sukses {data, meta, errors} sesuai Dok 07 API Design.
     */
    public static function success(mixed $data, array $meta = [], int $status = 200): JsonResponse
    {
        return response()->json([
            'data' => $data,
            'meta' => (object) $meta,
            'errors' => null,
        ], $status);
    }

    /**
     * Bentuk envelope error {data, meta, errors} dengan kode UPPER_SNAKE_CASE
     * tertelusur ke Business Rule (Dok 07 API Design, Bagian 4 Contributing Guide).
     */
    public static function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json([
            'data' => null,
            'meta' => (object) [],
            'errors' => [
                ['code' => $code, 'message' => $message],
            ],
        ], $status);
    }
}
