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
                    'reactions' => $this->getReactions($dto->postId),
                ];
            }

            $existing->update(['reaction_id' => $dto->reactionId]);

            /** @var Reaction $reaction */
            $reaction = Reaction::find($dto->reactionId);

            Cache::forget("post:{$dto->postId}");

            return [
                'my_reaction' => $reaction->name,
                'reactions' => $this->getReactions($dto->postId),
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
            'reactions' => $this->getReactions($dto->postId),
        ];
    }

    private function getReactions(int $postId): array
    {
        return PostReaction::query()->where('post_id', $postId)
            ->with(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')])
            ->with('reaction:id,name')
            ->latest()
            ->get()
            ->map(fn (PostReaction $r) => [
                'id' => $r->id,
                'post_id' => $r->post_id,
                'user_id' => $r->user_id,
                'type' => $r->reaction->name,
                'user' => $r->user ? [
                    'id' => $r->user->id,
                    'first_name' => $r->user->first_name,
                    'last_name' => $r->user->last_name,
                    'profile_image' => $r->user->profile_image,
                ] : null,
                'created_at' => $r->created_at,
            ])
            ->toArray();
    }
}
