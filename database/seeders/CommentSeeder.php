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
        $userIds = $users->pluck('id');

        if ($posts->isEmpty()) {
            $this->command->warn('No posts found. Run PostSeeder first.');

            return;
        }

        $comments = [];
        $now = now();

        foreach ($posts as $post) {
            $commentCount = fake()->numberBetween(0, 4);
            $topLevelIds = [];

            for ($i = 0; $i < $commentCount; $i++) {
                $comments[] = [
                    'user_id' => $userIds->random(),
                    'post_id' => $post->id,
                    'parent_id' => null,
                    'content' => fake()->paragraph(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($comments, 100) as $chunk) {
            Comment::insert($chunk);
        }

        $topLevel = Comment::whereNull('parent_id')->get();

        $replies = [];
        foreach ($topLevel as $comment) {
            $replyCount = fake()->numberBetween(0, 3);

            for ($i = 0; $i < $replyCount; $i++) {
                $replies[] = [
                    'user_id' => $userIds->random(),
                    'post_id' => $comment->post_id,
                    'parent_id' => $comment->id,
                    'content' => fake()->paragraph(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($replies, 100) as $chunk) {
            Comment::insert($chunk);
        }
    }
}
