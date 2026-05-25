<?php

namespace App\Actions\Post;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

final class DeleteAction
{
    public function execute(Post $post, User $user): void
    {
        if ($post->user_id !== $user->id) {
            throw new HttpResponseException(
                response()->json([
                    'success' => false,
                    'message' => 'You can only delete your own posts',
                ], ResponseAlias::HTTP_FORBIDDEN)
            );
        }

        $post->delete();
    }
}
