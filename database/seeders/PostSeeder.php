<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (Post::count() > 0) {
            return;
        }

        $users = User::all();

        if ($users->isEmpty()) {
            $users = User::factory(50)->create();
        }

        $posts = [];
        $now = now();

        foreach ($users as $user) {
            for ($i = 0; $i < 16; $i++) {
                $posts[] = [
                    'user_id' => $user->id,
                    'content' => fake()->paragraphs(2, true),
                    'image' => null,
                    'visibility' => 'public',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            for ($i = 0; $i < 4; $i++) {
                $posts[] = [
                    'user_id' => $user->id,
                    'content' => fake()->paragraphs(2, true),
                    'image' => null,
                    'visibility' => 'private',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($posts, 100) as $chunk) {
            Post::insert($chunk);
        }
    }
}
