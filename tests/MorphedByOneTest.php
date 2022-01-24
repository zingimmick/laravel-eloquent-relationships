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

    /**
     * @var int|mixed
     */
    public $retrievedLogins;

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

    public function testOfMany(): void
    {
        $product = Product::query()->create([]);
        $url = $this->faker->imageUrl();
        $image = Image::query()->create([
            'url' => $url,
        ]);
        $product->images()
            ->attach($image, []);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->findOrFail($product->getKey());
        self::assertSame('bestProduct', $image->bestProduct()->getRelationName());
        self::assertTrue($image->bestProduct()->exists());
        self::assertCount(1, $image->bestProduct()->get());
        self::assertSame(1, $image->bestProduct()->count());
        self::assertTrue($image->bestProduct()->is($product));
        $product2 = Product::query()->create([]);
        $product2->images()
            ->attach($image, []);
        self::assertTrue($image->bestProduct()->exists());
        self::assertCount(1, $image->bestProduct()->get());
        self::assertTrue($image->bestProduct()->is($product));
        self::assertSame(1, $image->bestProduct()->count());
        self::assertTrue($image->bestProduct()->isNot($product2));
        self::assertTrue($image->bestProduct()->whereKey($product->getKey())->exists());
        self::assertFalse($image->bestProduct()->whereKey($product2->getKey())->exists());
        $this->retrievedLogins = 0;
        Image::getEventDispatcher()->listen('eloquent.retrieved:*', function ($event, $models): void {
            foreach ($models as $model) {
                if (get_class($model) === Product::class) {
                    $this->retrievedLogins++;
                }
            }
        });
        $image = Image::query()->with(['bestProduct'])->findOrFail($product->getKey());
        $this->assertSame(1, $this->retrievedLogins);
    }

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Image::getEventDispatcher()->listen('eloquent.retrieved:*', function ($event, $models) use (
            &$retrievedLogins
        ): void {
            foreach ($models as $model) {
                if (get_class($model) === Product::class) {
                    $retrievedLogins++;
                }
            }
        });

        $image = Image::query()->create([
            'url' => $this->faker->url(),
        ]);
        $image->bestProduct()
            ->create();
        $image->bestProduct()
            ->create();
        $image = Image::query()->create([
            'url' => $this->faker->url(),
        ]);
        $image->bestProduct()
            ->create();
        $image->bestProduct()
            ->create();

        Image::query()->with('bestProduct')->get();

        $this->assertSame(2, $retrievedLogins);
    }
}
