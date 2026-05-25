<?php

namespace App\Actions\Post;

use App\DTOs\Post\CreatePostDTO;
use App\Models\Post;

final class StoreAction
{
    public function execute(CreatePostDTO $dto): Post
    {
        $post = Post::query()->create([
            'user_id' => $dto->userId,
            'content' => $dto->content,
            'visibility' => $dto->visibility,
            'image' => $dto->imagePath,
        ]);

        return $post->load(['user']);
    }
}
