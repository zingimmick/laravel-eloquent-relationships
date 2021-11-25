<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Zing\LaravelEloquentRelationships\Relations\BelongsToOne;
use Zing\LaravelEloquentRelationships\Relations\HasMoreRelationships;

/**
 * @property string $name
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User[]|\Illuminate\Database\Eloquent\Collection $members
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User|null $leader
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User $candidate
 */
class Group extends Model
{
    use HasMoreRelationships;

    /**
     * @var string[]
     */
    protected $fillable = ['name'];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function leader(): BelongsToOne
    {
        return $this->belongsToOne(User::class)->where('status', 1)->withPivot('status');
    }

    public function candidate(): BelongsToOne
    {
        return $this->belongsToOne(User::class)
            ->withPivot('status')
            ->where('status', 0)
            ->withDefault(function (User $user, self $group): void {
                $user->name = 'candidate leader for ' . $group->name;
            });
    }
}
