<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;

final class DeleteAction
{
    public function execute(Post $post, User $user): void
    {
        Gate::authorize('delete', $post);

        $post->delete();

        Cache::forget("post:{$post->id}");
        Cache::tags(['posts:feed', 'user:'.$user->id])->flush();
    }
}
