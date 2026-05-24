<?php

namespace App\Actions\Comment;

use App\Data\DTOS\Comment\CreateCommentDTO;
use App\Models\Comment;
use App\Models\Post;
use App\Supports\ApiResponse;
use Illuminate\Http\JsonResponse;

class CreateAction
{
    public function execute(CreateCommentDTO $dto): JsonResponse
    {
        /** @var Post|null $post */
        $post = Post::findOrFail($dto->postId);

        if ($dto->parentId) {
            /** @var Comment $parent */
            $parent = Comment::findOrFail($dto->parentId);

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

        $comment->load(['user']);

        return ApiResponse::created($comment, 'Comment created successfully');
    }
}
