<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests;

use Illuminate\Foundation\Testing\WithFaker;
use Zing\LaravelEloquentRelationships\Tests\Models\Group;
use Zing\LaravelEloquentRelationships\Tests\Models\Product;
use Zing\LaravelEloquentRelationships\Tests\Models\User;

/**
 * @internal
 */
final class BelongsToOneTest extends TestCase
{
    use WithFaker;

    public function testEagerLoading(): void
    {
        $group = Group::query()->create([]);
        $group->members()
            ->attach(User::query()->create([]), [
                'status' => 1,
            ]);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->with(['leader'])->findOrFail($group->getKey());
        self::assertInstanceOf(User::class, $group->leader);
        self::assertSame(1, (int) $group->leader->pivot->status);
    }

    public function testLazyLoading(): void
    {
        $group = Group::query()->create([]);
        $group->members()
            ->attach(User::query()->create([]), [
                'status' => 1,
            ]);
        self::assertInstanceOf(User::class, $group->leader);
        self::assertSame(1, (int) $group->leader->pivot->status);
    }

    public function testWithDefault(): void
    {
        $group = Group::query()->create([
            'name' => 'test',
        ]);
        $group->members()
            ->attach(User::query()->create([]), [
                'status' => 1,
            ]);
        self::assertInstanceOf(User::class, $group->candidate);
        self::assertSame('candidate leader for test', $group->candidate->name);
    }

    public function testWithoutDefault(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->create([
            'name' => 'test',
        ]);
        self::assertNull($group->leader);
    }

    public function testGetResults(): void
    {
        $product = Product::query()->create([]);
        self::assertNull($product->leader);
    }

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Group::getEventDispatcher()->listen('eloquent.retrieved:*', function ($event, $models) use (
            &$retrievedLogins
        ): void {
            foreach ($models as $model) {
                if (get_class($model) === User::class) {
                    $retrievedLogins++;
                }
            }
        });
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $image */
        $image = Group::query()->create([
            'name' => $this->faker->name(),
        ]);
        $image->leader()
            ->create([], [
                'status' => 1,
            ]);
        $image->leader()
            ->create([], [
                'status' => 1,
            ]);
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $image */
        $image = Group::query()->create([
            'name' => $this->faker->name(),
        ]);
        $image->leader()
            ->create([], [
                'status' => 1,
            ]);
        $image->leader()
            ->create([], [
                'status' => 1,
            ]);

        Group::query()->with('leader')->get();

        $this->assertSame(2, $retrievedLogins);
    }
}
