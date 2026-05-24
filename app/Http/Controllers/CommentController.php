<?php

namespace App\Http\Controllers;

use App\Actions\Comment\CreateAction;
use App\Data\DTOS\Comment\CreateCommentDTO;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class CommentController extends Controller
{
    public function __construct(
        private readonly CreateAction $createCommentAction,
    ) {}

    public function index(Post $post): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $comments = $post->comments()
            ->parentOnly()
            ->with([
                'user',
                'replies.user',
                'replies.replies.user',
                'replies.replies.replies.user',
            ])
            ->withCount(['likes', 'replies'])
            ->latest()
            ->get();

        return ApiResponse::success(
            CommentResource::collection($comments)
        );
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $dto = CreateCommentDTO::fromRequest(
                $request->all(),
                $user->id,
                $post->id
            );

            return $this->createCommentAction->execute($dto);
        } catch (ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        }
    }

    public function destroy(Comment $comment): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        if ($comment->user_id !== $user->id) {
            return ApiResponse::forbidden('You can only delete your own comments');
        }

        $comment->delete();

        return ApiResponse::success(null, 'Comment deleted successfully');
    }
}
