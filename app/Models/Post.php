<?php

namespace App\Models;

use App\Enums\PostVisibility;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $content
 * @property string|null $image
 * @property string $visibility
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property bool $is_liked
 * @property string|null $my_reaction
 *
 * @mixin Builder
 */
#[Fillable(['user_id', 'content', 'image', 'visibility'])]
class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'visibility' => 'string',
        ];
    }

    protected function image(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value && ! str_starts_with($value, 'http')
                ? url($value)
                : $value,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(PostReaction::class)->where('reaction_id', Reaction::LIKE_ID);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('visibility', PostVisibility::PUBLIC->value);
    }

    public function scopeFeedForUser(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('visibility', PostVisibility::PUBLIC->value)
            ->orWhere('user_id', $user->id)
        );
    }

    public function scopeVisibleForUser(Builder $query, User $user): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->where('visibility', PostVisibility::PUBLIC->value)
            ->orWhere('user_id', $user->id)
        );
    }
}
