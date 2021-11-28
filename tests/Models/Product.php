<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Zing\LaravelEloquentRelationships\HasMoreRelationships;
use Zing\LaravelEloquentRelationships\Relations\BelongsToOne;
use Zing\LaravelEloquentRelationships\Relations\MorphToOne;

/**
 * @property string $name
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\Image|null $cover
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\Image|null $thumbnail
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\User|null $leader
 */
class Product extends Model
{
    use HasMoreRelationships;

    /**
     * @var string[]
     */
    protected $fillable = ['name'];

    public function leader(): BelongsToOne
    {
        return $this->belongsToOne(User::class, 'group_user', 'group_id', null, 'group_id')
            ->where('status', 1)
            ->withPivot('status');
    }

    public function images(): MorphToMany
    {
        return $this->morphToMany(Image::class, 'imageable', 'model_has_images');
    }

    public function cover(): MorphToOne
    {
        return $this->morphToOne(Image::class, 'imageable', 'model_has_images')->withDefault([
            'url' => 'https://example.com/default.png',
        ]);
    }

    public function thumbnail(): MorphToOne
    {
        return $this->morphToOne(Image::class, 'imageable', 'model_has_images');
    }
}
