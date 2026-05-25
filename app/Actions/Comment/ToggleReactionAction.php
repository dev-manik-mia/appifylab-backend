<?php

namespace App\Actions\Comment;

use App\DTOs\Comment\ToggleCommentReactionDTO;
use App\Models\CommentReaction;
use App\Models\Reaction;

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

                return [
                    'my_reaction' => null,
                    'reactions' => $this->getCommentReactions($dto->commentId),
                ];
            }

            $existing->update(['reaction_id' => $dto->reactionId]);

            /** @var Reaction $reaction */
            $reaction = Reaction::find($dto->reactionId);

            return [
                'my_reaction' => $reaction->name,
                'reactions' => $this->getCommentReactions($dto->commentId),
            ];
        }

        CommentReaction::query()->create([
            'user_id' => $dto->userId,
            'comment_id' => $dto->commentId,
            'reaction_id' => $dto->reactionId,
        ]);

        /** @var Reaction $reaction */
        $reaction = Reaction::find($dto->reactionId);

        return [
            'my_reaction' => $reaction->name,
            'reactions' => $this->getCommentReactions($dto->commentId),
        ];
    }

    private function getCommentReactions(int $commentId): array
    {
        return CommentReaction::query()->where('comment_id', $commentId)
            ->with(['user', 'reaction'])
            ->get()
            ->map(fn (CommentReaction $reactive) => [
                'id' => $reactive->id,
                'comment_id' => $reactive->comment_id,
                'user_id' => $reactive->user_id,
                'type' => $reactive->reaction->name,
                'user' => $reactive->user,
                'created_at' => $reactive->created_at,
            ])
            ->toArray();
    }
}
