<?php

namespace App\DTOs\Comment;

use Illuminate\Http\Request;

final readonly class ToggleCommentReactionDTO
{
    public function __construct(
        public int $userId,
        public int $commentId,
        public int $reactionId,
    ) {}

    public static function fromRequest(Request $request, int $userId, int $commentId): self
    {
        $request->validate([
            'reaction_id' => ['required', 'exists:reactions,id'],
        ]);

        return new self(
            userId: $userId,
            commentId: $commentId,
            reactionId: $request->integer('reaction_id'),
        );
    }
}
