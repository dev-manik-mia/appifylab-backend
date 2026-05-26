<?php

namespace App\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Comment $comment */
        $comment = $this->resource;

        return [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'post_id' => $comment->post_id,
            'parent_id' => $comment->parent_id,
            'content' => $comment->content,

            'user' => new UserResource(
                $this->whenLoaded('user')
            ),

            'replies' => CommentResource::collection(
                $this->whenLoaded('replies')
            ),

            'replies_count' => $this->whenCounted('replies'),
            'likes_count' => $this->whenCounted('likes'),

            'is_liked' => $comment->is_liked ?? false,

            'created_at' => $comment->created_at,
            'updated_at' => $comment->updated_at,
        ];
    }
}
