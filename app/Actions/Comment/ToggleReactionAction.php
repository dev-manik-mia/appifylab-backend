<?php

namespace App\Actions\Comment;

use App\DTOs\Comment\ToggleCommentReactionDTO;
use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Reaction;
use Illuminate\Support\Facades\Cache;

final class ToggleReactionAction
{
    public function execute(ToggleCommentReactionDTO $dto): array
    {
        $existing = CommentReaction::query()->where('user_id', $dto->userId)
            ->where('comment_id', $dto->commentId)
            ->first();

        if ($existing) {
            if ($existing->reaction_id === $dto->reactionId) {
                $existing->delete();

                $this->invalidateCache($dto->commentId);

                return [
                    'my_reaction' => null,
                    'reactions' => $this->getGroupedReactions($dto->commentId),
                ];
            }

            $existing->update(['reaction_id' => $dto->reactionId]);

            /** @var Reaction $reaction */
            $reaction = Reaction::find($dto->reactionId);

            $this->invalidateCache($dto->commentId);

            return [
                'my_reaction' => $reaction->name,
                'reactions' => $this->getGroupedReactions($dto->commentId),
            ];
        }

        CommentReaction::query()->create([
            'user_id' => $dto->userId,
            'comment_id' => $dto->commentId,
            'reaction_id' => $dto->reactionId,
        ]);

        /** @var Reaction $reaction */
        $reaction = Reaction::find($dto->reactionId);

        $this->invalidateCache($dto->commentId);

        return [
            'my_reaction' => $reaction->name,
            'reactions' => $this->getGroupedReactions($dto->commentId),
        ];
    }

    private function getGroupedReactions(int $commentId): array
    {
        return CommentReaction::query()->where('comment_id', $commentId)
            ->selectRaw('reaction_id, count(*) as count')
            ->groupBy('reaction_id')
            ->with('reaction:id,name')
            ->get()
            ->map(fn (CommentReaction $reactive) => [
                'reaction_id' => $reactive->reaction_id,
                'type' => $reactive->reaction->name,
                'count' => (int) $reactive->count,
            ])
            ->toArray();
    }

    private function invalidateCache(int $commentId): void
    {
        $comment = Comment::select('post_id')->find($commentId);

        if ($comment) {
            Cache::forget("post:{$comment->post_id}:comments");
        }
    }
}
