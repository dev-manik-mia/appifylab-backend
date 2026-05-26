<?php

namespace App\DTOs\Comment;

use Illuminate\Http\Request;

readonly class CreateCommentDTO
{
    public function __construct(
        public int $userId,
        public int $postId,
        public ?int $parentId,
        public string $content,
    ) {}

    public static function fromRequest(Request $request, int $userId, int $postId): self
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        return new self(
            userId: $userId,
            postId: $postId,
            parentId: $validated['parent_id'] ?? null,
            content: $validated['content'],
        );
    }
}
