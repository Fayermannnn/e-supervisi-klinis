<?php

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Auth\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated(), $request->ip());

        return ApiResponse::success([
            'token' => $result['token'],
            'pengguna' => [
                'id' => $result['pengguna']->id,
                'nama' => $result['pengguna']->nama,
                'email' => $result['pengguna']->email,
            ],
        ]);
    }
}
