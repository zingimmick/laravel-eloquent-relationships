<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Zing\LaravelEloquentRelationships\HasMoreRelationships;
use Zing\LaravelEloquentRelationships\Relations\BelongsToOne;

/**
 * @property string $name
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User[]|\Illuminate\Database\Eloquent\Collection $members
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User|null $leader
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User $candidate
 */
class Group extends Model
{
    use HasMoreRelationships;

    protected $fillable = ['name'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function leader(): BelongsToOne
    {
        return $this->belongsToOne(User::class)->wherePivot('status', 1)->ofMany()->withPivot('status');
    }

    public function candidate(): BelongsToOne
    {
        return $this->belongsToOne(User::class)
            ->withPivot('status')
            ->wherePivot('status', 0)
            ->withDefault(static function (User $user, self $group): void {
                $user->name = 'candidate leader for ' . $group->name;
            });
    }
}
