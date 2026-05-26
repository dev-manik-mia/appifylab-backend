<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class DeleteAction
{
    public function execute(Post $post, User $user): void
    {
        Gate::authorize('delete', $post);

        $image = $post->getRawOriginal('image');

        $post->delete();

        if ($image) {
            Storage::disk('public')->delete(Str::after($image, '/storage/'));
        }

        Cache::forget("post:{$post->id}");
        Cache::tags(['posts:feed', 'user:'.$user->id])->flush();
    }
}
