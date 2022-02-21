<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Zing\LaravelEloquentRelationships\Tests\Models\Image;
use Zing\LaravelEloquentRelationships\Tests\Models\Product;

/**
 * @internal
 */
final class MorphToOneTest extends TestCase
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

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->with(['cover'])->findOrFail($product->getKey());
        self::assertInstanceOf(Image::class, $product->cover);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        self::assertSame($url, $cover->url);
    }

    public function testLazyLoading(): void
    {
        $product = Product::query()->create([]);
        $url = $this->faker->imageUrl();
        $product->images()
            ->attach(Image::query()->create([
                'url' => $url,
            ]), []);
        self::assertInstanceOf(Image::class, $product->cover);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        self::assertSame($url, $cover->url);
    }

    public function testWithDefault(): void
    {
        $product = Product::query()->create([
            'name' => 'test',
        ]);
        self::assertInstanceOf(Image::class, $product->cover);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        self::assertSame('https://example.com/default.png', $cover->url);
    }

    public function testWithoutDefault(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->create([
            'name' => 'test',
        ]);
        self::assertNull($product->thumbnail);
    }

    public function testOfMany(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->create([
            'url' => $this->faker->imageUrl(),
        ]);
        $product = Product::query()->create([]);
        $image->bestProduct()
            ->attach($product, []);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->findOrFail($image->getKey());
        self::assertSame('cover', $product->cover()->getRelationName());
        self::assertTrue($product->cover()->exists());
        self::assertCount(1, $product->cover()->get());
        self::assertSame(1, $product->cover()->count());
        self::assertTrue($product->cover()->is($image));
        $image2 = Image::query()->create([
            'url' => $this->faker->imageUrl(),
        ]);
        $image2->bestProduct()
            ->attach($product, []);
        self::assertTrue($product->cover()->exists());
        self::assertCount(1, $product->cover()->get());
        self::assertTrue($product->cover()->is($image2));
        self::assertSame(1, $product->cover()->count());
        self::assertTrue($product->cover()->isNot($image));
    }

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Image::getEventDispatcher()->listen('eloquent.retrieved:*', function (
            $event,
            $models
        ) use (&$retrievedLogins): void {
            foreach ($models as $model) {
                if ($model instanceof \Zing\LaravelEloquentRelationships\Tests\Models\Image) {
                    ++$retrievedLogins;
                }
            }
        });

        $product = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $product->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $product->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $product = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $product->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $product->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);

        Product::query()->with('cover')->get();

        self::assertSame(2, $retrievedLogins);
    }

    public function testReceivingModel(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $product->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $product->cover()
            ->create([
                'url' => 'test',
            ]);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        self::assertNotNull($cover);
        self::assertSame('test', $cover->url);
    }

    public function testMorphType(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $product->images()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $product->images()
            ->create([
                'url' => 'test',
            ]);
        $image = $product->images()
            ->make([
                'url' => $this->faker->url(),
            ]);
        $product->images()
            ->updateExistingPivot($image->getKey(), [
                'imageable_type' => 'bar',
            ]);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        self::assertNotNull($cover);
        self::assertSame('test', $cover->url);
    }

    public function testExists(): void
    {
        $product = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $previousImage = $product->images()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $currentImage = $product->images()
            ->create([
                'url' => $this->faker->url(),
            ]);

        $exists = Product::query()->whereHas('cover', function ($q) use ($previousImage): void {
            $q->whereKey($previousImage->getKey());
        })->exists();
        self::assertFalse($exists);

        $exists = Product::query()->whereHas('cover', function ($q) use ($currentImage): void {
            $q->whereKey($currentImage->getKey());
        })->exists();
        self::assertTrue($exists);
    }

    public function testGetResults(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = (new Product())->cover;
        self::assertSame('https://example.com/default.png', $cover->url);
    }
}
