<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentRelationships\Relations\BelongsToOne;
use Zing\LaravelEloquentRelationships\Relations\HasMoreRelationships;

/**
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User|null $leader
 */
class Product extends Model
{
    use HasMoreRelationships;

    public function leader(): BelongsToOne
    {
        return $this->belongsToOne(User::class, 'group_user', 'group_id', null, 'group_id')
            ->where('status', 1)
            ->withPivot('status');
    }
}
