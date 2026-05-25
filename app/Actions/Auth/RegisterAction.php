<?php

namespace App\Actions\Auth;

use App\DTOs\Auth\RegisterDTO;
use App\Models\User;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class RegisterAction
{
    public function execute(RegisterDTO $dto): JsonResponse
    {
        $user = User::query()->create([
            'first_name' => $dto->firstName,
            'last_name' => $dto->lastName,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);

        $token = JWTAuth::fromUser($user);

        return ApiResponse::created([
            'token' => $token,
            'user' => $user,
        ], 'Registration successful');
    }
}
