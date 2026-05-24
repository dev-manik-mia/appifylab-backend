<?php

namespace App\Actions\Like;

use App\DTOs\Like\ToggleLikeDTO;
use App\Models\Comment;
use App\Models\CommentReactive;
use App\Models\Post;
use App\Models\PostReactive;
use App\Models\Reaction;

final class ToggleLikeAction
{
    private const LIKE_REACTION_ID = 1; // Like reaction ID

    public function execute(ToggleLikeDTO $dto): array
    {
        if ($dto->likeableType === 'post') {
            return $this->togglePostLike($dto);
        }

        return $this->toggleCommentLike($dto);
    }

    private function togglePostLike(ToggleLikeDTO $dto): array
    {
        $existing = PostReactive::where('user_id', $dto->userId)
            ->where('post_id', $dto->likeableId)
            ->where('reaction_id', self::LIKE_REACTION_ID)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['liked' => false];
        }

        PostReactive::create([
            'user_id' => $dto->userId,
            'post_id' => $dto->likeableId,
            'reaction_id' => self::LIKE_REACTION_ID,
        ]);

        return ['liked' => true];
    }

    private function toggleCommentLike(ToggleLikeDTO $dto): array
    {
        $existing = CommentReactive::where('user_id', $dto->userId)
            ->where('comment_id', $dto->likeableId)
            ->where('reaction_id', self::LIKE_REACTION_ID)
            ->first();

        if ($existing) {
            $existing->delete();
            return ['liked' => false];
        }

        CommentReactive::create([
            'user_id' => $dto->userId,
            'comment_id' => $dto->likeableId,
            'reaction_id' => self::LIKE_REACTION_ID,
        ]);

        return ['liked' => true];
    }
}
