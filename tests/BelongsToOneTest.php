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
        $this->assertInstanceOf(User::class, $group->leader);
        $this->assertSame(1, (int) $group->leader->pivot->status);
    }

    public function testLazyLoading(): void
    {
        $group = Group::query()->create([]);
        $group->members()
            ->attach(User::query()->create([]), [
                'status' => 1,
            ]);
        $this->assertInstanceOf(User::class, $group->leader);
        $this->assertSame(1, (int) $group->leader->pivot->status);
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
        $this->assertInstanceOf(User::class, $group->candidate);
        $this->assertSame('candidate leader for test', $group->candidate->name);
    }

    public function testWithoutDefault(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->create([
            'name' => 'test',
        ]);
        $this->assertNull($group->leader);
    }

    public function testGetResults(): void
    {
        $product = Product::query()->create([]);
        $this->assertNull($product->leader);
    }

    public function testOfMany(): void
    {
        $group = Group::query()->create([]);
        $user = User::query()->create([]);
        $group->leader()
            ->attach($group, [
                'status' => 1,
            ]);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->findOrFail($group->getKey());
        $this->assertSame('leader', $group->leader()->getRelationName());
        $this->assertTrue($group->leader()->exists());
        $this->assertCount(1, $group->leader()->get());
        $this->assertSame(1, $group->leader()->count());
        $this->assertTrue($group->leader()->is($user));
        $user2 = User::query()->create([]);
        $group->leader()
            ->attach($user2, [
                'status' => 1,
            ]);
        $this->assertTrue($group->leader()->exists());
        $this->assertCount(1, $group->leader()->get());
        $this->assertTrue($group->leader()->is($user2));
        $this->assertSame(1, $group->leader()->count());
        $this->assertTrue($group->leader()->isNot($user));
    }

    public function testRetrievedTimes(): void
    {
        $retrievedLogins = 0;
        Group::getEventDispatcher()->listen('eloquent.retrieved:*', static function (
            $event,
            $models
        ) use (&$retrievedLogins): void {
            foreach ($models as $model) {
                if ($model instanceof \Zing\LaravelEloquentRelationships\Tests\Models\User) {
                    ++$retrievedLogins;
                }
            }
        });

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->create([
            'name' => $this->faker->name(),
        ]);
        $group->leader()
            ->create([], [
                'status' => 1,
            ]);
        $group->leader()
            ->create([], [
                'status' => 1,
            ]);
        $group = Group::query()->create([
            'name' => $this->faker->name(),
        ]);
        $group->leader()
            ->create([], [
                'status' => 1,
            ]);
        $group->leader()
            ->create([], [
                'status' => 1,
            ]);

        Group::query()->with('leader')->get();

        $this->assertSame(2, $retrievedLogins);
    }

    public function testReceivingModel(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->create([
            'url' => $this->faker->url(),
        ]);
        $group->leader()
            ->create([], [
                'status' => 1,
            ]);
        $user = $group->leader()
            ->create([], [
                'status' => 1,
            ]);

        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\User $leader */
        $leader = $group->leader;
        $this->assertNotNull($leader);
        $this->assertSame($user->getKey(), $leader->getKey());
    }

    public function testExists(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->create([
            'url' => $this->faker->url(),
        ]);
        $previousUser = $group->leader()
            ->create([], [
                'status' => 1,
            ]);
        $currentUser = $group->leader()
            ->create([], [
                'status' => 1,
            ]);

        $exists = Group::query()->whereHas('leader', static function ($q) use ($previousUser): void {
            $q->whereKey($previousUser->getKey());
        })->exists();
        $this->assertFalse($exists);

        $exists = Group::query()->whereHas('leader', static function ($q) use ($currentUser): void {
            $q->whereKey($currentUser->getKey());
        })->exists();
        $this->assertTrue($exists);
    }

    public function testIs(): void
    {
        /** @var \Zing\LaravelEloquentRelationships\Tests\Models\Group $group */
        $group = Group::query()->create([
            'url' => $this->faker->url(),
        ]);
        $previousImage = $group->leader()
            ->create([], [
                'status' => 1,
            ]);
        $currentImage = $group->leader()
            ->create([], [
                'status' => 1,
            ]);

        $this->assertFalse($group->leader()->is($previousImage));

        $this->assertTrue($group->leader()->is($currentImage));
    }
}
