<?php

namespace App\Actions\Comment;

use App\DTOs\Comment\CreateCommentDTO;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CreateAction
{
    public function execute(CreateCommentDTO $dto): JsonResponse
    {
        /** @var Post|null $post */
        $post = Post::query()->findOrFail($dto->postId);

        if ($dto->parentId) {
            /** @var Comment $parent */
            $parent = Comment::query()->findOrFail($dto->parentId);

            if ($parent->post_id !== $post->id) {
                return ApiResponse::error('Parent comment does not belong to this post', 422);
            }
        }

        $comment = Comment::query()->create([
            'user_id' => $dto->userId,
            'post_id' => $dto->postId,
            'parent_id' => $dto->parentId,
            'content' => $dto->content,
        ]);

        $comment->load(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')]);
        $comment->loadCount('likes');

        Cache::forget("post:{$dto->postId}:comments");

        return ApiResponse::created(
            new CommentResource($comment),
            'Comment created successfully'
        );
    }
}
