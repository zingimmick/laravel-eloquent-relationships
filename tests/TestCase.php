<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create(
            'groups',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name')
                    ->default('');
                $table->timestamps();
            }
        );

        Schema::create(
            'users',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name')
                    ->default('');
                $table->timestamps();
            }
        );
        Schema::create(
            'group_user',
            function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('group_id')
                    ->index();
                $table->unsignedBigInteger('user_id')
                    ->index();
                $table->tinyInteger('status')
                    ->default(0);
                $table->index(['group_id', 'user_id']);
            }
        );
        Schema::create('products', function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id')
                ->nullable()
                ->index();
            $table->string('name')
                ->default('');
            $table->timestamps();
        });
    }

    protected function getEnvironmentSetUp($app): void
    {
        config([
            'database.default' => 'testing',
        ]);
    }
}
