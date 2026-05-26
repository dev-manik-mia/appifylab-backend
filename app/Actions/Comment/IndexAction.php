<?php

namespace App\Actions\Comment;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

final class IndexAction
{
    public function execute(Post $post, User $user): Collection
    {
        return $post->comments()
            ->whereNull('parent_id')
            ->with([
                'user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image'),
                'replies' => fn ($q) => $q
                    ->with([
                        'user' => fn ($uq) => $uq->select('id', 'first_name', 'last_name', 'profile_image'),
                    ])
                    ->withCount('likes')
                    ->withExists(['likes as is_liked' => fn ($lq) => $lq->where('user_id', $user->id)])
                    ->latest(),
            ])
            ->withCount(['replies', 'likes'])
            ->withExists(['likes as is_liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->get();
    }
}
