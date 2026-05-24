<?php

namespace App\Http\Controllers;

use App\Actions\Post\ToggleReactionAction;
use App\DTOs\Post\TogglePostReactionDTO;
use App\Models\Post;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class ReactionController extends Controller
{
    public function togglePost(Request $request, Post $post): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $dto = TogglePostReactionDTO::fromRequest($request, $user->id, $post->id);
        $result = (new ToggleReactionAction())->execute($dto);

        $message = match (true) {
            $result['my_reaction'] === null => 'Reaction removed',
            isset($result['previous_reaction']) => 'Reaction updated',
            default => 'Reaction added',
        };

        return ApiResponse::success($result, $message);
    }
}
