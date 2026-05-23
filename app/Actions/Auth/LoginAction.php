<?php

namespace App\Actions\Auth;

use App\Data\DTOS\Auth\LoginDTO;
use App\Models\User;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LoginAction
{
    public function execute(LoginDTO $dto): JsonResponse
    {
        $user = User::where('email', $dto->email)->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            return ApiResponse::unauthorized('Invalid email or password');
        }

        $token = JWTAuth::fromUser($user);

        return ApiResponse::success([
            'token' => $token,
            'user' => $user,
        ], 'Login successful');
    }
}
