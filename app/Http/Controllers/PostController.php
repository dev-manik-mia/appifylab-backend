<?php

namespace App\Http\Controllers;

use App\Actions\Post\DeleteAction;
use App\Actions\Post\IndexAction;
use App\Actions\Post\ShowAction;
use App\Actions\Post\StoreAction;
use App\Actions\Post\UserPostsAction;
use App\DTOs\Post\CreatePostDTO;
use App\Models\Post;
use App\Models\User;
use App\Supports\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $posts = (new IndexAction)->execute($request->user(), $request->input('cursor'));

            return ApiResponse::success($posts);
        } catch (QueryException $e) {
            report($e);

            return ApiResponse::error('Failed to fetch posts', 500);
        }
    }

    public function userPosts(Request $request, User $user): JsonResponse
    {
        try {
            $posts = (new UserPostsAction)->execute($request->user(), $user);

            return ApiResponse::success($posts);
        } catch (QueryException $e) {
            report($e);

            return ApiResponse::error('Failed to fetch user posts', 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $dto = CreatePostDTO::fromRequest($request, $request->user()->id);
        $post = (new StoreAction)->execute($dto);

        return ApiResponse::created($post, 'Post created successfully');
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        if ($post->visibility === 'private' && $post->user_id !== $request->user()->id) {
            return ApiResponse::notFound('Post not found');
        }

        $result = (new ShowAction)->execute($post, $request->user());

        return ApiResponse::success($result);
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        (new DeleteAction)->execute($post, $request->user());

        return ApiResponse::success(null, 'Post deleted successfully');
    }
}
