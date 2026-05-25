<?php

namespace App\DTOs\Comment;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

readonly class CreateCommentDTO
{
    public function __construct(
        public int $userId,
        public int $postId,
        public ?int $parentId,
        public string $content,
    ) {}

    /**
     * @throws ValidationException
     */
    public static function fromRequest(array $data, int $userId, int $postId): self
    {
        $validator = Validator::make($data, [
            'content' => ['required', 'string', 'max:1000'],
            'parent_id' => ['nullable', 'integer', 'exists:comments,id'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();

        return new self(
            userId: $userId,
            postId: $postId,
            parentId: $validated['parent_id'] ?? null,
            content: $validated['content'],
        );
    }
}
