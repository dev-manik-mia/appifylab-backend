<?php

namespace App\Actions\Post;

use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Models\User;

final class ShowAction
{
    public function execute(Post $post, User $user): array
    {
        $post->load(['user'])
            ->loadCount(['comments', 'likes']);
        $post->is_liked = $post->likes()->where('user_id', $user->id)->exists();

        $comments = $post->comments()
            ->parentOnly()
            ->with([
                'user',
                'replies.user',
                'replies.replies.user',
                'replies.replies.replies.user',
            ])
            ->withCount(['likes', 'replies'])
            ->latest()
            ->get();

        return [
            'post' => $post,
            'comments' => CommentResource::collection($comments),
        ];
    }
}
