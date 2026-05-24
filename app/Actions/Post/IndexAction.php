<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\PostReactive;
use App\Models\User;

final class IndexAction
{
    public function execute(User $user): array
    {
        $posts = Post::with(['user'])
            ->withCount(['comments'])
            ->feedForUser($user)
            ->latest()
            ->get();

        return $posts->map(function ($post) use ($user) {
            // Check if user liked this post (like is reaction_id = 1)
            $post->is_liked = PostReactive::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('reaction_id', 1)
                ->exists();
            
            // Get user's current reaction on this post
            $reactive = PostReactive::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->with('reaction')
                ->first();
            $post->my_reaction = $reactive?->reaction?->name;
            
            // Get all reactions on this post
            $post->reactions = PostReactive::where('post_id', $post->id)
                ->with(['user', 'reaction'])
                ->get()
                ->map(fn ($reactive) => [
                    'id' => $reactive->id,
                    'post_id' => $reactive->post_id,
                    'user_id' => $reactive->user_id,
                    'type' => $reactive->reaction->name,
                    'user' => $reactive->user,
                    'created_at' => $reactive->created_at,
                ])
                ->toArray();

            return $post;
        })
        ->toArray();
    }
}

