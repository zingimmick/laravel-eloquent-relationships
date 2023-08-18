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
        $url = 'test-url';
        $product->images()
            ->attach(Image::query()->create([
                'url' => $url,
            ]), []);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->with(['cover'])->findOrFail($product->getKey());
        $this->assertInstanceOf(Image::class, $product->cover);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        $this->assertSame($url, $cover->url);
    }

    public function testLazyLoading(): void
    {
        $product = Product::query()->create([]);
        $url = 'test-url';
        $product->images()
            ->attach(Image::query()->create([
                'url' => $url,
            ]), []);
        $this->assertInstanceOf(Image::class, $product->cover);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        $this->assertSame($url, $cover->url);
    }

    public function testWithDefault(): void
    {
        $product = Product::query()->create([
            'name' => 'test',
        ]);
        $this->assertInstanceOf(Image::class, $product->cover);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = $product->cover;
        $this->assertSame('https://example.com/default.png', $cover->url);
    }

    public function testWithoutDefault(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->create([
            'name' => 'test',
        ]);
        $this->assertNull($product->thumbnail);
    }

    public function testOfMany(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $image */
        $image = Image::query()->create([
            'url' => 'test-url',
        ]);
        $product = Product::query()->create([]);
        $image->bestProduct()
            ->attach($product, []);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Product $product */
        $product = Product::query()->findOrFail($image->getKey());
        $this->assertSame('cover', $product->cover()->getRelationName());
        $this->assertTrue($product->cover()->exists());
        $this->assertCount(1, $product->cover()->get());
        $this->assertSame(1, $product->cover()->count());
        $this->assertTrue($product->cover()->is($image));
        $image2 = Image::query()->create([
            'url' => 'test-url',
        ]);
        $image2->bestProduct()
            ->attach($product, []);
        $this->assertTrue($product->cover()->exists());
        $this->assertCount(1, $product->cover()->get());
        $this->assertTrue($product->cover()->is($image2));
        $this->assertSame(1, $product->cover()->count());
        $this->assertTrue($product->cover()->isNot($image));
    }

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Image::getEventDispatcher()->listen('eloquent.retrieved:*', static function (
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

        $this->assertSame(2, $retrievedLogins);
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
        $this->assertNotNull($cover);
        $this->assertSame('test', $cover->url);
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
        $this->assertNotNull($cover);
        $this->assertSame('test', $cover->url);
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

        $exists = Product::query()->whereHas('cover', static function ($q) use ($previousImage): void {
            $q->whereKey($previousImage->getKey());
        })->exists();
        $this->assertFalse($exists);

        $exists = Product::query()->whereHas('cover', static function ($q) use ($currentImage): void {
            $q->whereKey($currentImage->getKey());
        })->exists();
        $this->assertTrue($exists);
    }

    public function testGetResults(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Image $cover */
        $cover = (new Product())->cover;
        $this->assertSame('https://example.com/default.png', $cover->url);
    }
}
