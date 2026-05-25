<?php

namespace App\Actions\Post;

use App\DTOs\Post\TogglePostReactionDTO;
use App\Models\PostReaction;
use App\Models\Reaction;

final class ToggleReactionAction
{
    public function execute(TogglePostReactionDTO $dto): array
    {
        $existing = PostReaction::query()->where('user_id', $dto->userId)
            ->where('post_id', $dto->postId)
            ->first();

        if ($existing) {
            if ($existing->reaction_id === $dto->reactionId) {
                $existing->delete();

                return [
                    'my_reaction' => null,
                    'reactions' => $this->getPostReactions($dto->postId),
                ];
            }

            $existing->update(['reaction_id' => $dto->reactionId]);

            /** @var Reaction $reaction */
            $reaction = Reaction::find($dto->reactionId);

            return [
                'my_reaction' => $reaction->name,
                'reactions' => $this->getPostReactions($dto->postId),
            ];
        }

        PostReaction::query()->create([
            'user_id' => $dto->userId,
            'post_id' => $dto->postId,
            'reaction_id' => $dto->reactionId,
        ]);

        /** @var Reaction $reaction */
        $reaction = Reaction::find($dto->reactionId);

        return [
            'my_reaction' => $reaction->name,
            'reactions' => $this->getPostReactions($dto->postId),
        ];
    }

    private function getPostReactions(int $postId): array
    {
        return PostReaction::query()->where('post_id', $postId)
            ->with(['user', 'reaction'])
            ->get()
            ->map(fn (PostReaction $reactive) => [
                'id' => $reactive->id,
                'post_id' => $reactive->post_id,
                'user_id' => $reactive->user_id,
                'type' => $reactive->reaction->name,
                'user' => $reactive->user,
                'created_at' => $reactive->created_at,
            ])
            ->toArray();
    }
}
