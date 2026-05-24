<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

final class TogglePostReactionDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $postId,
        public readonly int $reactionId,
    ) {}

    public static function fromRequest(Request $request, int $userId, int $postId): self
    {
        $request->validate([
            'reaction_id' => ['required', 'exists:reactions,id'],
        ]);

        return new self(
            userId: $userId,
            postId: $postId,
            reactionId: $request->integer('reaction_id'),
        );
    }
}
