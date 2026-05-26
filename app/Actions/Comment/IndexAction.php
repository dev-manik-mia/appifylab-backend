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
            ->with($this->repliesWith($user, 3))
            ->withCount(['replies', 'likes'])
            ->withExists(['likes as is_liked' => fn ($q) => $q->where('user_id', $user->id)])
            ->latest()
            ->get();
    }

    private function repliesWith(User $user, int $depth): array
    {
        if ($depth <= 0) {
            return ['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')];
        }

        return [
            'user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image'),
            'replies' => fn ($q) => $q
                ->with($this->repliesWith($user, $depth - 1))
                ->withCount(['replies', 'likes'])
                ->withExists(['likes as is_liked' => fn ($lq) => $lq->where('user_id', $user->id)]),
        ];
    }
}
