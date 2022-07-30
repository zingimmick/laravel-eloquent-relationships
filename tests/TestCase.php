<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create(
            'groups',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name')
                    ->default('');
                $table->timestamps();
            }
        );

        Schema::create(
            'users',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('name')
                    ->default('');
                $table->timestamps();
            }
        );
        Schema::create(
            'group_user',
            static function (Blueprint $table): void {
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
        Schema::create('products', static function (Blueprint $table): void {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('group_id')
                ->nullable()
                ->index();
            $table->string('name')
                ->default('');
            $table->timestamps();
        });
        Schema::create(
            'images',
            static function (Blueprint $table): void {
                $table->bigIncrements('id');
                $table->string('url');
                $table->timestamps();
            }
        );

        Schema::create(
            'model_has_images',
            static function (Blueprint $table): void {
                $table->unsignedBigInteger('image_id');
                $table->morphs('imageable');
                $table->tinyInteger('priority')
                    ->default(0);
                $table->primary(['image_id', 'imageable_id', 'imageable_type']);
                $table->foreign('image_id')
                    ->references('id')
                    ->on('images')
                    ->onDelete('cascade');
            }
        );
    }

    protected function getEnvironmentSetUp($app): void
    {
        config([
            'database.default' => 'testing',
        ]);
    }
}
