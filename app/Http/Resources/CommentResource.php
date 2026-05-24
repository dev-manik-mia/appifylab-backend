<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    private int $depth;

    public function __construct($resource, int $depth = 4)
    {
        parent::__construct($resource);
        $this->depth = $depth;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'post_id' => $this->post_id,
            'parent_id' => $this->parent_id,
            'content' => $this->content,
            'user' => new UserResource($this->whenLoaded('user')),
            'replies' => $this->when($this->depth > 1 && $this->relationLoaded('replies'), function () {
                return $this->replies->map(fn ($reply) => new static($reply, $this->depth - 1));
            }),
            'likes_count' => $this->whenCounted('likes'),
            'is_liked' => $this->is_liked ?? false,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
