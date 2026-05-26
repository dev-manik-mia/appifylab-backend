<?php

namespace App\DTOs\Post;

use Illuminate\Http\Request;

final class CreatePostDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly string $content,
        public readonly string $visibility,
        public readonly ?string $imagePath = null,
    ) {}

    public static function fromRequest(Request $request, int $userId): self
    {
        $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', 'in:public,private'],
            'image' => ['nullable', 'image', 'max:10240'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $storedPath = $request->file('image')->store('posts', 'public');
            $imagePath = '/storage/'.$storedPath;
        }

        return new self(
            userId: $userId,
            content: $request->string('content')->value(),
            visibility: $request->string('visibility')->value(),
            imagePath: $imagePath,
        );
    }
}
