<?php

namespace App\Http\Controllers;

use App\Actions\Post\DeleteAction;
use App\Actions\Post\IndexAction;
use App\Actions\Post\ShowAction;
use App\Actions\Post\StoreAction;
use App\DTOs\Post\CreatePostDTO;
use App\Models\Post;
use App\Supports\ApiResponse;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    /**
     * @throws JWTException
     */
    public function index(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $posts = (new IndexAction)->execute($user);

            return ApiResponse::success($posts);
        } catch (QueryException $e) {
            return ApiResponse::error('Failed to fetch posts', 500);
        }
    }

    /**
     * @throws JWTException
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            $dto = CreatePostDTO::fromRequest($request, $user->id);
            $post = (new StoreAction)->execute($dto);

            return ApiResponse::created($post, 'Post created successfully');
        } catch (QueryException $e) {
            return ApiResponse::error('Failed to create post', 500);
        }
    }

    /**
     * @throws JWTException
     */
    public function show(Post $post): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if ($post->visibility === 'private' && $post->user_id !== $user->id) {
                return ApiResponse::notFound('Post not found');
            }

            $result = (new ShowAction)->execute($post, $user);

            return ApiResponse::success($result);
        } catch (QueryException $e) {
            return ApiResponse::error('Failed to fetch post', 500);
        }
    }

    /**
     * @throws JWTException
     */
    public function destroy(Post $post): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            (new DeleteAction)->execute($post, $user);

            return ApiResponse::success(null, 'Post deleted successfully');
        } catch (QueryException $e) {
            return ApiResponse::error('Failed to delete post', 500);
        }
    }
}
