<?php

namespace App\Http\Controllers;

use App\Actions\Comment\ToggleReactionAction as ToggleCommentReactionAction;
use App\Actions\Post\ToggleReactionAction;
use App\DTOs\Comment\ToggleCommentReactionDTO;
use App\DTOs\Post\TogglePostReactionDTO;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostReaction;
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
        $result = (new ToggleReactionAction)->execute($dto);

        $message = $result['my_reaction'] === null ? 'Reaction removed' : 'Reaction toggled';

        return ApiResponse::success($result, $message);
    }

    public function toggleComment(Request $request, Comment $comment): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $dto = ToggleCommentReactionDTO::fromRequest($request, $user->id, $comment->id);
        $result = (new ToggleCommentReactionAction)->execute($dto);

        $message = $result['my_reaction'] === null ? 'Reaction removed' : 'Reaction toggled';

        return ApiResponse::success($result, $message);
    }

    public function postReactions(Post $post): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($post->visibility === 'private' && $post->user_id !== $user->id) {
            return ApiResponse::notFound('Post not found');
        }

        $reactions = PostReaction::where('post_id', $post->id)
            ->with(['user', 'reaction'])
            ->latest()
            ->get()
            ->map(fn ($reactive) => [
                'id' => $reactive->id,
                'post_id' => $reactive->post_id,
                'user_id' => $reactive->user_id,
                'type' => $reactive->reaction->name,
                'user' => $reactive->user,
                'created_at' => $reactive->created_at,
            ]);

        return ApiResponse::success($reactions);
    }

    public function commentReactions(Comment $comment): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $reactions = CommentReaction::where('comment_id', $comment->id)
            ->with(['user', 'reaction'])
            ->latest()
            ->get()
            ->map(fn ($reactive) => [
                'id' => $reactive->id,
                'comment_id' => $reactive->comment_id,
                'user_id' => $reactive->user_id,
                'type' => $reactive->reaction->name,
                'user' => $reactive->user,
                'created_at' => $reactive->created_at,
            ]);

        return ApiResponse::success($reactions);
    }
}
