<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginAction;
use App\Actions\Auth\RegisterAction;
use App\DTOs\Auth\LoginDTO;
use App\DTOs\Auth\RegisterDTO;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterAction $registerAction,
        private readonly LoginAction $loginAction,
    ) {}

    public function register(Request $request): JsonResponse
    {
        try {

            $dto = RegisterDTO::fromRequest($request->all());

            return $this->registerAction->execute($dto);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function login(Request $request): JsonResponse
    {
        try {

            $dto = LoginDTO::fromRequest($request->all());

            return $this->loginAction->execute($dto);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    /**
     * @throws JWTException
     */
    public function me(): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        if (! $user) {
            return ApiResponse::unauthorized('User not found');
        }

        return ApiResponse::success($user);
    }

    /**
     * @throws JWTException
     */
    public function logout(): JsonResponse
    {
        JWTAuth::parseToken()->invalidate(true);

        return ApiResponse::success(null, 'Logged out successfully');
    }

    /**
     * @throws JWTException
     */
    public function refresh(): JsonResponse
    {
        $token = JWTAuth::parseToken()->refresh();

        return ApiResponse::success(['token' => $token], 'Token refreshed');
    }
}
