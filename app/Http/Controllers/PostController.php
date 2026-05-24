<?php

namespace App\Http\Controllers;

use App\Actions\Post\DeleteAction;
use App\Actions\Post\IndexAction;
use App\Actions\Post\ShowAction;
use App\Actions\Post\StoreAction;
use App\DTOs\Post\CreatePostDTO;
use App\Models\Post;
use App\Supports\ApiResponse;
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
        $user = JWTAuth::parseToken()->authenticate();

        $posts = (new IndexAction())->execute($user);

        return ApiResponse::success($posts);
    }

    public function store(Request $request): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $dto = CreatePostDTO::fromRequest($request, $user->id);
        $post = (new StoreAction())->execute($dto);

        return ApiResponse::created($post, 'Post created successfully');
    }

    public function show(Post $post): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        $result = (new ShowAction())->execute($post, $user);

        return ApiResponse::success($result);
    }

    public function destroy(Post $post): JsonResponse
    {
        $user = JWTAuth::parseToken()->authenticate();

        (new DeleteAction())->execute($post, $user);

        return ApiResponse::success(null, 'Post deleted successfully');
    }
}
