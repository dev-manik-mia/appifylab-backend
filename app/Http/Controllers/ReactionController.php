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

class ReactionController extends Controller
{
    public function togglePost(Request $request, Post $post): JsonResponse
    {
        $dto = TogglePostReactionDTO::fromRequest($request, $request->user()->id, $post->id);
        $result = (new ToggleReactionAction)->execute($dto);

        $message = $result['my_reaction'] === null ? 'Reaction removed' : 'Reaction toggled';

        return ApiResponse::success($result, $message);
    }

    public function toggleComment(Request $request, Comment $comment): JsonResponse
    {
        $dto = ToggleCommentReactionDTO::fromRequest($request, $request->user()->id, $comment->id);
        $result = (new ToggleCommentReactionAction)->execute($dto);

        $message = $result['my_reaction'] === null ? 'Reaction removed' : 'Reaction toggled';

        return ApiResponse::success($result, $message);
    }

    public function postReactions(Request $request, Post $post): JsonResponse
    {
        if ($post->visibility === 'private' && $post->user_id !== $request->user()->id) {
            return ApiResponse::notFound('Post not found');
        }

        $reactions = PostReaction::where('post_id', $post->id)
            ->selectRaw('reaction_id, count(*) as count')
            ->groupBy('reaction_id')
            ->with('reaction:id,name')
            ->get()
            ->map(fn ($reactive) => [
                'reaction_id' => $reactive->reaction_id,
                'type' => $reactive->reaction->name,
                'count' => (int) $reactive->count,
            ]);

        return ApiResponse::success($reactions);
    }

    public function commentReactions(Request $request, Comment $comment): JsonResponse
    {
        $reactions = CommentReaction::where('comment_id', $comment->id)
            ->selectRaw('reaction_id, count(*) as count')
            ->groupBy('reaction_id')
            ->with('reaction:id,name')
            ->get()
            ->map(fn ($reactive) => [
                'reaction_id' => $reactive->reaction_id,
                'type' => $reactive->reaction->name,
                'count' => (int) $reactive->count,
            ]);

        return ApiResponse::success($reactions);
    }
}
