# Laravel Backend Coding Style Guide (AI Skill Prompt)

Use this coding style when generating Laravel backend code for this project.

---

## Architecture Pattern

Follow **Controller → DTO → Action → Model** architecture.

### Flow

Controller
→ Validate/Create DTO
→ Call Action
→ Return ApiResponse

---

## Project Structure

```txt
app/
├── Actions/
│   └── {Domain}/{Resource}/
│       ├── IndexAction.php
│       ├── StoreAction.php
│       ├── UpdateAction.php
│       ├── DeleteAction.php
│       └── ...
│
├── DTOs/
│   └── {Domain}/{Resource}/
│       ├── {Resource}DTO.php
│
├── Http/Controllers/Api/
│   └── {Domain}/
│       └── {Resource}Controller.php
│
└── Supports/
    └── ApiResponse.php
```

---

## DTO Structure

Use DTOs as clean templates. Replace resource-specific examples with a generic structure developers can copy for any module.

```php
<?php

namespace App\DTOs\{Domain}\{Resource};

use Illuminate\Http\Request;

final class Create{Resource}DTO
{
    public function __construct(
        public readonly int $tenantId,
        public readonly int $createdById,
        // Resource fields...
    ) {}

    public static function fromRequest(Request $request, int $tenantId, int $createdById): self
    {
        $request->validate([
            // Validation rules...
        ]);

        return new self(
            tenantId: $tenantId,
            createdById: $createdById,
            // Map request fields...
        );
    }
}
```

---

## Model Structure

Use models as clean templates. Replace resource-specific examples with a reusable structure for any entity.

```php
<?php

namespace App\Models;

use Database\Factories\{Resource}Factory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @mixin Builder
 */
#[Fillable([
    // Fillable attributes...
])]
class {Resource} extends Model
{
    use HasFactory, SoftDeletes;

    /** @use HasFactory<{Resource}Factory> */

    protected function casts(): array
    {
        return [
            // Attribute casts...
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query;
    }
}
```
