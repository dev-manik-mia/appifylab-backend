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
 * @property int $comment_id
 * @property int $reaction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 * @mixin Model
 *
 * @property Reaction|null $reaction
 * @property Comment|null $comment
 * @property User|null $user
 */
#[Fillable(['user_id', 'comment_id', 'reaction_id'])]
class CommentReaction extends Model
{
    protected $table = 'comment_reactions';

    public function scopeLikes(Builder $query): Builder
    {
        return $query->where('reaction_id', Reaction::LIKE_ID);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    public function reaction(): BelongsTo
    {
        return $this->belongsTo(Reaction::class);
    }
}
