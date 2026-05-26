<?php

namespace App\Http\Controllers;

use App\Actions\Comment\StoreAction;
use App\Actions\Comment\IndexAction;
use App\DTOs\Comment\CreateCommentDTO;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CommentController extends Controller
{
    public function __construct(
        private readonly StoreAction $createCommentAction,
        private readonly IndexAction $indexCommentAction,
    ) {}

    public function index(Request $request, Post $post): JsonResponse
    {
        $comments = $this->indexCommentAction->execute($post, $request->user());

        return ApiResponse::success(
            CommentResource::collection($comments)
        );
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        try {
            $dto = CreateCommentDTO::fromRequest(
                $request,
                $request->user()->id,
                $post->id
            );

            return $this->createCommentAction->execute($dto);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function destroy(Request $request, Comment $comment): JsonResponse
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        Cache::forget("post:{$comment->post_id}:comments");

        return ApiResponse::success(null, 'Comment deleted successfully');
    }
}
