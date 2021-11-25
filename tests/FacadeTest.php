<?php

declare(strict_types=1);

namespace Zing\Skeleton\Tests;

use Zing\Skeleton\Facades\Skeleton;
use Zing\Skeleton\SkeletonServiceProvider;

class FacadeTest extends TestCase
{
    /**
     * @param mixed $app
     *
     * @return array<class-string<\Zing\Skeleton\SkeletonServiceProvider>>
     */
    protected function getPackageProviders($app): array
    {
        return [SkeletonServiceProvider::class];
    }

    /**
     * @param mixed $app
     *
     * @return array<string, class-string<\Zing\Skeleton\Facades\Skeleton>>
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Skeleton' => Skeleton::class,
        ];
    }

    public function testStaticCall(): void
    {
        self::assertTrue(Skeleton::foo());
    }

    public function testAlias(): void
    {
        self::assertSame(forward_static_call([\Skeleton::class, 'foo']), forward_static_call([Skeleton::class, 'foo']));
    }
}
