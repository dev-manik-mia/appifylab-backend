<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'content' => $this->content,
            'image' => $this->image,
            'visibility' => $this->visibility,
            'user' => new UserResource($this->whenLoaded('user')),
            'comments_count' => $this->whenCounted('comments'),
            'likes_count' => $this->whenCounted('likes'),
            'is_liked' => $this->is_liked ?? false,
            'my_reaction' => $this->my_reaction ?? null,
            'reactions' => $this->when($this->relationLoaded('reactions'), function () {
                return $this->reactions->map(fn ($reaction) => [
                    'id' => $reaction->id,
                    'post_id' => $reaction->post_id,
                    'user_id' => $reaction->user_id,
                    'type' => $reaction->reaction->name,
                    'user' => new UserResource($reaction->whenLoaded('user')),
                    'created_at' => $reaction->created_at,
                ]);
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
