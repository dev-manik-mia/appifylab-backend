<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int $post_id
 * @property int $reaction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 * @mixin Model
 *
 * @property User $user
 * @property Post $post
 * @property Reaction $reaction
 */
#[Fillable(['user_id', 'post_id', 'reaction_id'])]
class PostReaction extends Model
{
    protected $table = 'post_reactions';

    public function scopeLikes(Builder $query): Builder
    {
        return $query->where('reaction_id', Reaction::LIKE_ID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function reaction(): BelongsTo
    {
        return $this->belongsTo(Reaction::class);
    }
}
