<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Builder
 */
#[Fillable(['name'])]
class Reaction extends Model
{
    public const LIKE = 'like';
    public const LOVE = 'love';
    public const HAHA = 'haha';
    public const WOW = 'wow';
    public const SAD = 'sad';
    public const CARE = 'care';
    public const ANGRY = 'angry';
}
