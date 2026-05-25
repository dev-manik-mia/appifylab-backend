<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\CommentReaction;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostReactionSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (PostReaction::count() > 0 || CommentReaction::count() > 0) {
            return;
        }

        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();
        $userIds = $users->pluck('id');
        $likeId = Reaction::where('name', Reaction::LIKE)->value('id');
        $reactionIds = Reaction::pluck('id');

        if ($posts->isEmpty()) {
            $this->command->warn('No posts found. Run PostSeeder first.');

            return;
        }

        $postReactions = [];
        foreach ($posts as $post) {
            $likingUsers = $users->random(fake()->numberBetween(0, min(3, $users->count())));

            foreach ($likingUsers as $user) {
                $postReactions[] = [
                    'user_id' => $user->id,
                    'post_id' => $post->id,
                    'reaction_id' => $reactionIds->random(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($postReactions, 100) as $chunk) {
            PostReaction::insert($chunk);
        }

        if ($comments->isNotEmpty()) {
            $commentReactions = [];
            foreach ($comments as $comment) {
                $likingUsers = $users->random(fake()->numberBetween(0, min(2, $users->count())));

                foreach ($likingUsers as $user) {
                    $commentReactions[] = [
                        'user_id' => $user->id,
                        'comment_id' => $comment->id,
                        'reaction_id' => $likeId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            foreach (array_chunk($commentReactions, 100) as $chunk) {
                CommentReaction::insert($chunk);
            }
        }
    }
}
