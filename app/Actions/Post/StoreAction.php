<?php

namespace App\Actions\Post;

use App\DTOs\Post\CreatePostDTO;
use App\Models\Post;
use Illuminate\Support\Facades\Cache;

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

        Cache::tags(['posts:feed', 'user:'.$dto->userId])->flush();

        return $post->load(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')]);
    }
}
