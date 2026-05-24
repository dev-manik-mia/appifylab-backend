<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Comment::count() > 0) {
            return;
        }

        $users = User::all();
        $posts = Post::all();

        if ($posts->isEmpty()) {
            $this->command->warn('No posts found. Run PostSeeder first.');

            return;
        }

        $posts->each(function (Post $post) use ($users) {
            $topLevelComments = Comment::factory()
                ->count(fake()->numberBetween(0, 4))
                ->create([
                    'post_id' => $post->id,
                    'user_id' => $users->random()->id,
                ]);

            $topLevelComments->each(function (Comment $comment) use ($users) {
                Comment::factory()
                    ->count(fake()->numberBetween(0, 3))
                    ->reply($comment)
                    ->create([
                        'post_id' => $comment->post_id,
                        'user_id' => $users->random()->id,
                    ]);
            });
        });
    }
}
