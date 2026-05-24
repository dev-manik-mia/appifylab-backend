<?php

namespace App\DTOs\Like;

use Illuminate\Http\Request;

final class ToggleLikeDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly int $likeableId,
        public readonly string $likeableType,
    ) {}

    public static function fromRequest(Request $request, int $userId): self
    {
        $request->validate([
            'likeable_id' => ['required', 'integer'],
            'likeable_type' => ['required', 'string', 'in:post,comment'],
        ]);

        return new self(
            userId: $userId,
            likeableId: $request->integer('likeable_id'),
            likeableType: $request->string('likeable_type')->value(),
        );
    }
}
