<?php

namespace App\Actions\Post;

use App\DTOs\Post\TogglePostReactionDTO;
use App\Models\Post;
use App\Models\PostReactive;
use App\Models\Reaction;

final class ToggleReactionAction
{
    public function execute(TogglePostReactionDTO $dto): array
    {
        $existing = PostReactive::where('user_id', $dto->userId)
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

            return [
                'my_reaction' => $existing->reaction->name,
                'reactions' => $this->getPostReactions($dto->postId),
            ];
        }

        PostReactive::create([
            'user_id' => $dto->userId,
            'post_id' => $dto->postId,
            'reaction_id' => $dto->reactionId,
        ]);

        $reaction = Reaction::find($dto->reactionId);

        return [
            'my_reaction' => $reaction->name,
            'reactions' => $this->getPostReactions($dto->postId),
        ];
    }

    private function getPostReactions(int $postId): array
    {
        return PostReactive::where('post_id', $postId)
            ->with(['user', 'reaction'])
            ->get()
            ->map(fn ($reactive) => [
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

