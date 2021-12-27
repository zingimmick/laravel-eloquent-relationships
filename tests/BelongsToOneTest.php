<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests;

use Zing\LaravelEloquentRelationships\Tests\Models\Group;
use Zing\LaravelEloquentRelationships\Tests\Models\Product;
use Zing\LaravelEloquentRelationships\Tests\Models\User;

/**
 * @internal
 */
final class BelongsToOneTest extends TestCase
{
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
}
