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

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Image::getEventDispatcher()->listen('eloquent.retrieved:*', function ($event, $models) use (
            &$retrievedLogins
        ): void {
            foreach ($models as $model) {
                if (get_class($model) === Image::class) {
                    $retrievedLogins++;
                }
            }
        });

        $image = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $image->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $image->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $image = Product::query()->create([
            'name' => $this->faker->name(),
        ]);
        $image->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);
        $image->cover()
            ->create([
                'url' => $this->faker->url(),
            ]);

        Product::query()->with('cover')->get();

        $this->assertSame(2, $retrievedLogins);
    }
}