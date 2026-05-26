<?php

namespace App\Actions\Post;

use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final class IndexAction
{
    public function execute(User $user): CursorPaginator
    {
        $cursor = request('cursor', '__first__');
        $cacheKey = "feed:user:{$user->id}:cursor:{$cursor}";

        return Cache::remember($cacheKey, 120, function () use ($user) {
            $posts = Post::with(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')])
                ->withCount([
                    'comments as comments_count' => fn ($q) => $q->whereNull('parent_id'),
                    'likes',
                ])
                ->withExists(['reactions as is_liked' => fn ($q) => $q->where('user_id', $user->id)->where('reaction_id', Reaction::LIKE_ID)])
                ->where(fn (Builder $q) => $q
                    ->where('visibility', PostVisibility::PUBLIC->value)
                    ->orWhere('user_id', $user->id)
                )
                ->latest()
                ->cursorPaginate(20);

            /** @var Collection<int,PostReaction> $userReactions */
            $userReactions = PostReaction::query()->whereIn('post_id', $posts->pluck('id'))
                ->where('user_id', $user->id)
                ->with('reaction')
                ->get()
                ->keyBy('post_id');

            $postIds = $posts->pluck('id');
            $reactionsCounts = PostReaction::query()->whereIn('post_id', $postIds)
                ->selectRaw('post_id, reaction_id, count(*) as count')
                ->groupBy('post_id', 'reaction_id')
                ->with('reaction:id,name')
                ->get()
                ->groupBy('post_id');

            return $posts->through(function ($post) use ($userReactions, $reactionsCounts) {
                $reaction = $userReactions->get($post->id);
                $post->is_liked = $reaction?->reaction_id === Reaction::LIKE_ID;
                $post->my_reaction = $reaction?->reaction?->name;

                $post->unsetRelation('reactions');

                $post->reactions_count = ($reactionsCounts->get($post->id) ?? collect())
                    ->map(fn ($r) => [
                        'reaction_id' => $r->reaction_id,
                        'type' => $r->reaction->name,
                        'count' => (int) $r->count,
                    ])
                    ->values();

                return $post;
            });
        });
    }
}
