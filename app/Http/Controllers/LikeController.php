<?php

namespace App\Http\Controllers;

use App\Actions\Like\ToggleLikeAction;
use App\DTOs\Like\ToggleLikeDTO;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class LikeController extends Controller
{
    public function toggle(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $dto = ToggleLikeDTO::fromRequest($request, $user->id);
        $result = (new ToggleLikeAction())->execute($dto);

        $message = $result['liked'] ? 'Like added' : 'Like removed';

        return ApiResponse::success($result, $message);
    }
}
