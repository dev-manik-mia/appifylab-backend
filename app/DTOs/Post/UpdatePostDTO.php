<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

final class UpdatePostDTO
{
    public function __construct(
        public readonly int $postId,
        public readonly int $userId,
        public readonly string $content,
        public readonly string $visibility,
        public readonly ?string $imagePath = null,
        public readonly bool $removeImage = false,
    ) {}

    public static function fromRequest(Request $request, int $postId, int $userId): self
    {
        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', 'in:public,private'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['nullable', 'boolean'],
        ]);

        $imagePath = null;
        $removeImage = $request->boolean('remove_image', false);

        if ($request->hasFile('image')) {
            $storedPath = $request->file('image')->store('posts', 'public');
            $imagePath = '/storage/'.$storedPath;
        }

        return new self(
            postId: $postId,
            userId: $userId,
            content: $request->string('content')->value(),
            visibility: $request->string('visibility')->value(),
            imagePath: $imagePath,
            removeImage: $removeImage,
        );
    }
}
