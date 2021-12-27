<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Zing\LaravelEloquentRelationships\Tests\Models\Image;
use Zing\LaravelEloquentRelationships\Tests\Models\Product;

/**
 * @internal
 */
final class MorphedByOneTest extends TestCase
{
    use WithFaker;

    public function testEagerLoading(): void
    {
        $product = Product::query()->create([]);
        $url = $this->faker->imageUrl();
        $product->images()
            ->attach(Image::query()->create([
                'url' => $url,
            ]), []);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->with(['bestProduct'])->findOrFail($product->getKey());
        self::assertInstanceOf(Product::class, $image->bestProduct);
        self::assertTrue($product->is($image->bestProduct));
    }

    public function testLazyLoading(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->create([]);
        $url = $this->faker->imageUrl();
        $product->images()
            ->attach(Image::query()->create([
                'url' => $url,
            ]), []);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->findOrFail($product->getKey());
        self::assertInstanceOf(Product::class, $image->bestProduct);
        self::assertTrue($product->is($image->bestProduct));
    }

    public function testWithDefault(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $product */
        $product = Image::query()->create([
            'url' => $this->faker->imageUrl(),
        ]);
        self::assertInstanceOf(Product::class, $product->defaultProduct);
        self::assertSame('default name', $product->defaultProduct->name);
    }

    public function testWithoutDefault(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->create([
            'url' => $this->faker->imageUrl(),
        ]);
        self::assertNull($image->bestProduct);
    }
}
