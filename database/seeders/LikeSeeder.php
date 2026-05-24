<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Like::count() > 0) {
            return;
        }

        $users = User::all();
        $posts = Post::all();
        $comments = Comment::all();

        if ($posts->isEmpty()) {
            $this->command->warn('No posts found. Run PostSeeder first.');

            return;
        }

        $posts->each(function (Post $post) use ($users) {
            $likingUsers = $users->random(fake()->numberBetween(0, min(3, $users->count())));

            $likingUsers->each(function (User $user) use ($post) {
                Like::factory()
                    ->forPost($post->id)
                    ->create(['user_id' => $user->id]);
            });
        });

        if ($comments->isNotEmpty()) {
            $comments->each(function (Comment $comment) use ($users) {
                $likingUsers = $users->random(fake()->numberBetween(0, min(2, $users->count())));

                $likingUsers->each(function (User $user) use ($comment) {
                    Like::factory()
                        ->forComment($comment->id)
                        ->create(['user_id' => $user->id]);
                });
            });
        }
    }
}
