<?php

namespace Database\Seeders;

use App\Models\Reaction;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['like', 'love', 'haha', 'wow', 'sad', 'care', 'angry'] as $name) {
            Reaction::create(compact('name'));
        }
    }
}
