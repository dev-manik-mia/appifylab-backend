<?php

namespace App\Actions\Post;

use App\Enums\PostVisibility;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

final class IndexAction
{
    public function execute(User $user, ?string $cursor = null): CursorPaginator
    {
        $cacheKey = 'feed:user:'.$user->id.':cursor:'.($cursor ?: 'first');

        return Cache::tags(['posts:feed', 'user:'.$user->id])->flexible($cacheKey, [300, 3600], function () use ($user) {
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

            $postIds = $posts->pluck('id');

            /** @var Collection<int,PostReaction> $userReactions */
            $userReactions = PostReaction::query()->whereIn('post_id', $postIds)
                ->where('user_id', $user->id)
                ->with('reaction')
                ->get()
                ->keyBy('post_id');

            $reactions = PostReaction::query()->whereIn('post_id', $postIds)
                ->with(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')])
                ->with('reaction:id,name')
                ->latest()
                ->get()
                ->groupBy('post_id');

            return $posts->through(function ($post) use ($userReactions, $reactions) {
                $reaction = $userReactions->get($post->id);
                $post->is_liked = $reaction?->reaction_id === Reaction::LIKE_ID;
                $post->my_reaction = $reaction?->reaction?->name;

                $post->setRelation('reactions', $reactions->get($post->id, collect()));

                return $post;
            });
        });
    }
}
