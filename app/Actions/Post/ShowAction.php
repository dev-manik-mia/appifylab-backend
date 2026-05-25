<?php

namespace App\Actions\Post;

use App\Actions\Comment\IndexAction as CommentIndexAction;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Reaction;
use App\Models\User;

final class ShowAction
{
    public function execute(Post $post, User $user): array
    {
        $post->load(['user'])
            ->loadCount([
                'comments as comments_count' => fn ($q) => $q->whereNull('parent_id'),
                'likes',
            ]);

        /** @var PostReaction|null $userReaction */
        $userReaction = $post->reactions()
            ->where('user_id', $user->id)
            ->with('reaction')
            ->first();

        $post->is_liked = $userReaction?->reaction_id === Reaction::LIKE_ID;
        $post->my_reaction = $userReaction?->reaction?->name;

        $comments = (new CommentIndexAction)->execute($post, $user);

        return [
            'post' => $post,
            'comments' => CommentResource::collection($comments),
        ];
    }
}
