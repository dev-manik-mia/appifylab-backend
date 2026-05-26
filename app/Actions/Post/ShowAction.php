<?php

namespace App\Actions\Post;

use App\Actions\Comment\IndexAction as CommentIndexAction;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final class ShowAction
{
    public function execute(Post $post, User $user): array
    {
        return Cache::remember("post:{$post->id}", 300, function () use ($post, $user) {
            $post->load(['user' => fn ($q) => $q->select('id', 'first_name', 'last_name', 'profile_image')])
                ->loadCount([
                    'comments as comments_count' => fn ($q) => $q->whereNull('parent_id'),
                    'likes',
                ]);

            $userReaction = $post->reactions()
                ->where('user_id', $user->id)
                ->with('reaction')
                ->first();

            $post->is_liked = $userReaction?->reaction_id === Reaction::LIKE_ID;
            $post->my_reaction = $userReaction?->reaction?->name;

            $post->reactions_count = $post->reactions()
                ->selectRaw('reaction_id, count(*) as count')
                ->groupBy('reaction_id')
                ->with('reaction')
                ->get()
                ->map(fn ($r) => [
                    'reaction_id' => $r->reaction_id,
                    'type' => $r->reaction->name,
                    'count' => (int) $r->count,
                ]);

            $comments = (new CommentIndexAction)->execute($post, $user);

            return [
                'post' => $post,
                'comments' => CommentResource::collection($comments),
            ];
        });
    }
}
