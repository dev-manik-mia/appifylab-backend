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
            $users = User::factory(5)->create();
        }

        $users->each(function (User $user) {
            Post::factory()
                ->count(fake()->numberBetween(2, 5))
                ->public()
                ->create(['user_id' => $user->id]);
        });

        $users->each(function (User $user) {
            Post::factory()
                ->count(fake()->numberBetween(0, 2))
                ->private()
                ->create(['user_id' => $user->id]);
        });
    }
}
