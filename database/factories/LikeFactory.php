<?php

namespace Database\Factories;

use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
        ];
    }

    public function forPost(mixed $post): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $post,
            'likeable_type' => 'App\Models\Post',
        ]);
    }

    public function forComment(mixed $comment): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_id' => $comment,
            'likeable_type' => 'App\Models\Comment',
        ]);
    }
}
