# Laravel Eloquent Relationships

<p align="center">
<a href="https://github.com/zingimmick/laravel-eloquent-relationships/actions"><img src="https://github.com/zingimmick/laravel-eloquent-relationships/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://codecov.io/gh/zingimmick/laravel-eloquent-relationships"><img src="https://codecov.io/gh/zingimmick/laravel-eloquent-relationships/branch/master/graph/badge.svg" alt="Code Coverage" /></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-relationships"><img src="https://poser.pugx.org/zing/laravel-eloquent-relationships/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-relationships"><img src="https://poser.pugx.org/zing/laravel-eloquent-relationships/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-relationships"><img src="https://poser.pugx.org/zing/laravel-eloquent-relationships/v/unstable.svg" alt="Latest Unstable Version"></a>
<a href="https://packagist.org/packages/zing/laravel-eloquent-relationships"><img src="https://poser.pugx.org/zing/laravel-eloquent-relationships/license" alt="License"></a>
</p>

## Requirements

- [PHP 8.0+](https://php.net/releases/)
- [Composer](https://getcomposer.org)
- [Laravel 8.69+](https://laravel.com/docs/releases)

Require Laravel Eloquent Relationships using [Composer](https://getcomposer.org):

```bash
composer require zing/laravel-eloquent-relationships
```

## Usage

### BelongsToOne

`BelongsToOne` is based on `BelongsToMany`

#### Difference:

- returns related model instead of collection of models
- returns `null` instead of empty collection of models if the relationship does not exist
- supports return default related model in case the relationship does not exist

#### Example:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentRelationships\HasMoreRelationships;
use Zing\LaravelEloquentRelationships\Relations\BelongsToOne;
use Zing\LaravelEloquentRelationships\Tests\Models\User;

class Group extends Model
{
    use HasMoreRelationships;

    public function leader(): BelongsToOne
    {
        return $this->belongsToOne(User::class)
            ->wherePivot('status', 1)
            ->withDefault(function (User $user, self $group): void {
                $user->name = 'leader for ' . $group->name;
            });
    }
}
```

### MorphToOne


`MorphToOne` is based on `MorphToMany`

#### Difference:

- returns related model instead of collection of models
- returns `null` instead of empty collection of models if the relationship does not exist
- supports return default related model in case the relationship does not exist

#### Example:

```php
<?php

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentRelationships\HasMoreRelationships;
use Zing\LaravelEloquentRelationships\Relations\MorphToOne;
use Zing\LaravelEloquentRelationships\Tests\Models\Product;

class Image extends Model
{
    use HasMoreRelationships;

    public function bestProduct(): MorphToOne
    {
        return $this->morphedByOne(Product::class, 'imageable', 'model_has_images');
    }
}
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Zing\LaravelEloquentRelationships\HasMoreRelationships;
use Zing\LaravelEloquentRelationships\Relations\MorphToOne;
use Zing\LaravelEloquentRelationships\Tests\Models\Image;

class Product extends Model
{
    use HasMoreRelationships;

    public function cover(): MorphToOne
    {
        return $this->morphToOne(Image::class, 'imageable', 'model_has_images')->withDefault([
            'url' => 'https://example.com/default.png',
        ]);
    }
}
```

## License

Laravel Eloquent Relationships is an open-sourced software licensed under the [MIT license](LICENSE).
