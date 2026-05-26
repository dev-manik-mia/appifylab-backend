<?php

namespace App\Actions\Post;

use App\DTOs\Post\UpdatePostDTO;
use App\Models\Post;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class UpdateAction
{
    public function execute(UpdatePostDTO $dto, Post $post): Post
    {
        try {
            Gate::authorize('update', $post);

            $oldImage = $post->getRawOriginal('image');

            if ($dto->removeImage && $oldImage) {
                Storage::disk('public')->delete(Str::after($oldImage, '/storage/'));
                $post->update([
                    'content' => $dto->content,
                    'visibility' => $dto->visibility,
                    'image' => null,
                ]);
            } else {
                $post->update([
                    'content' => $dto->content,
                    'visibility' => $dto->visibility,
                    'image' => $dto->imagePath ?? $oldImage,
                ]);

                if ($dto->imagePath && $oldImage) {
                    Storage::disk('public')->delete(Str::after($oldImage, '/storage/'));
                }
            }

            $updatedPost = $post->load(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')]);

            Cache::forget("post:{$post->id}");

            Cache::tags(['posts:feed', 'user:'.$dto->userId])->flush();

            Cache::put("post:{$post->id}", $updatedPost, now()->addHours(24));

            return $updatedPost;
        } catch (QueryException $e) {
            report($e);
            throw $e;
        }
    }
}
