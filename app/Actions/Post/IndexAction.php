<?php

namespace App\Actions\Post;

use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class IndexAction
{
    public function execute(User $user): LengthAwarePaginator
    {
        $posts = Post::with(['user', 'reactions.user', 'reactions.reaction'])
            ->withCount([
                'comments as comments_count' => fn ($q) => $q->whereNull('parent_id'),
                'likes',
            ])
            ->where(fn (Builder $q) => $q
                ->where('visibility', PostVisibility::PUBLIC->value)
                ->orWhere('user_id', $user->id)
            )
            ->latest()
            ->paginate(20);

        /** @var Collection<int,PostReaction> $userReactions */
        $userReactions = PostReaction::query()->whereIn('post_id', $posts->pluck('id'))
            ->where('user_id', $user->id)
            ->with('reaction')
            ->get()
            ->keyBy('post_id');

        return $posts->through(function ($post) use ($userReactions) {
            $reaction = $userReactions->get($post->id);
            $post->is_liked = $reaction?->reaction_id === Reaction::LIKE_ID;
            $post->my_reaction = $reaction?->reaction?->name;

            $post->reactions = $post->relationLoaded('reactions')
                ? $post->reactions->map(fn ($r) => [
                    'id' => $r->id,
                    'post_id' => $r->post_id,
                    'user_id' => $r->user_id,
                    'type' => $r->reaction->name,
                    'user' => $r->user,
                    'created_at' => $r->created_at,
                ])
                : [];

            return $post;
        });
    }
}
