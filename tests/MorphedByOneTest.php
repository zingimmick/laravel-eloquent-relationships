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
        self::assertTrue($image->bestProduct()->is($product2));
        self::assertSame(1, $image->bestProduct()->count());
        self::assertTrue($image->bestProduct()->isNot($product));
    }

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Image::getEventDispatcher()->listen('eloquent.retrieved:*', function (
            $event,
            $models
        ) use (&$retrievedLogins): void {
            foreach ($models as $model) {
                if ($model instanceof \Zing\LaravelEloquentRelationships\Tests\Models\Product) {
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

    public function testReceivingModel(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->create([
            'url' => $this->faker->url(),
        ]);
        $image->bestProduct()
            ->create([
                'name' => $this->faker->name(),
            ]);
        $image->bestProduct()
            ->create([
                'name' => 'test',
            ]);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = $image->bestProduct;
        $this->assertNotNull($product);
        $this->assertSame('test', $product->name);
    }

    public function testMorphType(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->create([
            'url' => $this->faker->url(),
        ]);
        $image->bestProduct()
            ->create([
                'name' => $this->faker->name(),
            ]);
        $image->bestProduct()
            ->create([
                'name' => 'test',
            ]);
        $product = $image->bestProduct()
            ->make([
                'name' => $this->faker->name(),
            ]);
        $image->bestProduct()
            ->updateExistingPivot($product->getKey(), [
                'imageable_type' => 'bar',
            ]);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = $image->bestProduct;
        $this->assertNotNull($product);
        $this->assertSame('test', $product->name);
    }

    public function testExists(): void
    {
        $image = Image::query()->create([
            'url' => $this->faker->url(),
        ]);
        $previousProduct = $image->bestProduct()
            ->create([
                'name' => $this->faker->name(),
            ]);
        $currentProduct = $image->bestProduct()
            ->create([
                'name' => $this->faker->name(),
            ]);

        $exists = Image::query()->whereHas('bestProduct', function ($q) use ($previousProduct): void {
            $q->whereKey($previousProduct->getKey());
        })->exists();
        $this->assertFalse($exists);

        $exists = Image::query()->whereHas('bestProduct', function ($q) use ($currentProduct): void {
            $q->whereKey($currentProduct->getKey());
        })->exists();
        $this->assertTrue($exists);
    }
}
