<?php

namespace App\Actions\Post;

use App\DTOs\Post\TogglePostReactionDTO;
use App\Models\PostReaction;
use App\Models\Reaction;
use Illuminate\Support\Facades\Cache;

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

                Cache::forget("post:{$dto->postId}");

                return [
                    'my_reaction' => null,
                    'reactions' => $this->getGroupedReactions($dto->postId),
                ];
            }

            $existing->update(['reaction_id' => $dto->reactionId]);

            /** @var Reaction $reaction */
            $reaction = Reaction::find($dto->reactionId);

            Cache::forget("post:{$dto->postId}");

            return [
                'my_reaction' => $reaction->name,
                'reactions' => $this->getGroupedReactions($dto->postId),
            ];
        }

        PostReaction::query()->create([
            'user_id' => $dto->userId,
            'post_id' => $dto->postId,
            'reaction_id' => $dto->reactionId,
        ]);

        /** @var Reaction $reaction */
        $reaction = Reaction::find($dto->reactionId);

        Cache::forget("post:{$dto->postId}");

        return [
            'my_reaction' => $reaction->name,
            'reactions' => $this->getGroupedReactions($dto->postId),
        ];
    }

    private function getGroupedReactions(int $postId): array
    {
        return PostReaction::query()->where('post_id', $postId)
            ->selectRaw('reaction_id, count(*) as count')
            ->groupBy('reaction_id')
            ->with('reaction:id,name')
            ->get()
            ->map(fn (PostReaction $reactive) => [
                'reaction_id' => $reactive->reaction_id,
                'type' => $reactive->reaction->name,
                'count' => (int) $reactive->count,
            ])
            ->toArray();
    }
}
