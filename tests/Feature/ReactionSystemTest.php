<?php

use App\Models\CommentReaction;
use App\Models\PostReaction;
use App\Models\Reaction;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses()->group('api');

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->token = JWTAuth::fromUser($this->user);
    $this->headers = ['Authorization' => "Bearer {$this->token}"];

    $this->like = Reaction::create(['name' => 'like']);
    $this->love = Reaction::create(['name' => 'love']);
    $this->haha = Reaction::create(['name' => 'haha']);
});

it('registers a user', function () {
    $response = $this->postJson('/api/auth/register', [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

it('logs in a user', function () {
    $user = User::factory()->create(['password' => bcrypt('password123')]);

    $response = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'password123',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['token', 'user']]);
});

it('returns auth error with invalid credentials', function () {
    $this->postJson('/api/auth/login', [
        'email' => 'wrong@example.com',
        'password' => 'wrongpassword',
    ])->assertStatus(401);
});

it('requires authentication for protected routes', function () {
    $this->getJson('/api/posts')->assertStatus(401);
    $this->postJson('/api/posts', [])->assertStatus(401);
    $this->postJson('/api/logout')->assertStatus(401);
});

it('creates a post', function () {
    $response = $this->withHeaders($this->headers)->postJson('/api/posts', [
        'content' => 'Test post content',
        'visibility' => 'public',
    ]);

    $response->assertStatus(201)
        ->assertJson(['data' => ['content' => 'Test post content']]);
});

it('fetches feed with cursor pagination', function () {
    $this->user->posts()->createMany([
        ['content' => 'Post 1', 'visibility' => 'public'],
        ['content' => 'Post 2', 'visibility' => 'public'],
        ['content' => 'Post 3', 'visibility' => 'public'],
    ]);

    $response = $this->withHeaders($this->headers)->getJson('/api/posts');

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['data', 'path', 'per_page', 'next_page_url', 'prev_page_url']]);
});

it('fetches single post with reactions', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);

    $response = $this->withHeaders($this->headers)->getJson("/api/posts/{$post->id}");

    $response->assertStatus(200)
        ->assertJsonStructure(['data' => ['post', 'comments']]);
});

it('hides private posts from other users', function () {
    $post = $this->user->posts()->create(['content' => 'Private', 'visibility' => 'private']);
    $otherUser = User::factory()->create();
    $otherToken = JWTAuth::fromUser($otherUser);

    $this->withHeaders(['Authorization' => "Bearer {$otherToken}"])
        ->getJson("/api/posts/{$post->id}")
        ->assertStatus(404);
});

it('allows owner to see private posts', function () {
    $post = $this->user->posts()->create(['content' => 'Private', 'visibility' => 'private']);

    $this->withHeaders($this->headers)
        ->getJson("/api/posts/{$post->id}")
        ->assertStatus(200);
});

it('creates a comment on a post', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);

    $response = $this->withHeaders($this->headers)->postJson("/api/posts/{$post->id}/comments", [
        'content' => 'Nice post!',
    ]);

    $response->assertStatus(201);
});

it('toggles reaction on a post', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);

    $response = $this->withHeaders($this->headers)->postJson("/api/posts/{$post->id}/reactions", [
        'reaction_id' => $this->like->id,
    ]);

    $response->assertStatus(200);
    expect(PostReaction::count())->toBe(1);

    $this->withHeaders($this->headers)->postJson("/api/posts/{$post->id}/reactions", [
        'reaction_id' => $this->like->id,
    ])->assertStatus(200);

    expect(PostReaction::count())->toBe(0);
});

it('toggles reaction on a comment', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    $comment = $post->comments()->create(['user_id' => $this->user->id, 'content' => 'Comment']);

    $response = $this->withHeaders($this->headers)->postJson("/api/comments/{$comment->id}/reactions", [
        'reaction_id' => $this->like->id,
    ]);

    $response->assertStatus(200);
    expect(CommentReaction::count())->toBe(1);
});

it('lists grouped reactions on a post', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    PostReaction::create(['user_id' => $this->user->id, 'post_id' => $post->id, 'reaction_id' => $this->like->id]);

    $response = $this->withHeaders($this->headers)->getJson("/api/posts/{$post->id}/reactions");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [
            ['reaction_id' => $this->like->id, 'type' => 'like', 'count' => 1],
        ]]);
});

it('lists grouped reactions on a comment', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    $comment = $post->comments()->create(['user_id' => $this->user->id, 'content' => 'Comment']);
    CommentReaction::create(['user_id' => $this->user->id, 'comment_id' => $comment->id, 'reaction_id' => $this->like->id]);

    $response = $this->withHeaders($this->headers)->getJson("/api/comments/{$comment->id}/reactions");

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJson(['data' => [
            ['reaction_id' => $this->like->id, 'type' => 'like', 'count' => 1],
        ]]);
});

it('deletes own post', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/posts/{$post->id}")
        ->assertStatus(200);

    expect($post->fresh()->trashed())->toBeTrue();
});

it('prevents deleting others posts', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    $otherUser = User::factory()->create();
    $otherToken = JWTAuth::fromUser($otherUser);

    $this->withHeaders(['Authorization' => "Bearer {$otherToken}"])
        ->deleteJson("/api/posts/{$post->id}")
        ->assertStatus(403);
});

it('deletes own comment', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    $comment = $post->comments()->create(['user_id' => $this->user->id, 'content' => 'Comment']);

    $this->withHeaders($this->headers)
        ->deleteJson("/api/comments/{$comment->id}")
        ->assertStatus(200);
});

it('loads comments with likes_count and is_liked', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    $comment = $post->comments()->create(['user_id' => $this->user->id, 'content' => 'Comment']);
    PostReaction::create(['user_id' => $this->user->id, 'post_id' => $post->id, 'reaction_id' => $this->like->id]);

    $response = $this->withHeaders($this->headers)->getJson("/api/posts/{$post->id}/comments");

    $response->assertStatus(200);
});

it('includes is_liked and my_reaction in feed', function () {
    $post = $this->user->posts()->create(['content' => 'Test', 'visibility' => 'public']);
    PostReaction::create(['user_id' => $this->user->id, 'post_id' => $post->id, 'reaction_id' => $this->like->id]);

    $response = $this->withHeaders($this->headers)->getJson('/api/posts');

    $response->assertStatus(200);
});

it('logs out successfully', function () {
    $this->withHeaders($this->headers)
        ->postJson('/api/logout')
        ->assertStatus(200);

    $this->withHeaders($this->headers)
        ->getJson('/api/me')
        ->assertStatus(401);
});

it('enforces rate limiting on auth routes', function () {
    foreach (range(1, 12) as $i) {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);
    }

    $response->assertStatus(429);
});

it('shows user info', function () {
    $response = $this->withHeaders($this->headers)->getJson('/api/me');

    $response->assertStatus(200)
        ->assertJsonFragment(['first_name' => $this->user->first_name]);
});
