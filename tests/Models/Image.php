<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentRelationships\HasMoreRelationships;
use Zing\LaravelEloquentRelationships\Relations\MorphToOne;

/**
 * @property string $url
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\Product|null $bestProduct
 * @property-read \Zing\LaravelEloquentRelationships\Tests\Models\Product $defaultProduct
 */
class Image extends Model
{
    use HasMoreRelationships;

    /**
     * @var string[]
     */
    protected $fillable = ['url'];

    public function bestProduct(): MorphToOne
    {
        return $this->morphedByOne(Product::class, 'imageable', 'model_has_images');
    }

    public function defaultProduct(): MorphToOne
    {
        return $this->morphedByOne(Product::class, 'imageable', 'model_has_images')->withDefault([
            'name' => 'default name',
        ]);
    }
}
