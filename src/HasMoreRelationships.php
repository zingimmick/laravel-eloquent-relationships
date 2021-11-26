<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Zing\LaravelEloquentRelationships\Relations\BelongsToOne;
use Zing\LaravelEloquentRelationships\Relations\MorphToOne;

trait HasMoreRelationships
{
    /**
     * The one to one relationship methods.
     *
     * @var string[]
     */
    public static $oneMethods = ['belongsToOne', 'morphToOne', 'morphedByOne'];

    /**
     * Define a one-to-one relationship.
     *
     * @phpstan-param string $related
     *
     * @param mixed $related
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @param string|null $relation
     *
     * @return \Zing\LaravelEloquentRelationships\Relations\BelongsToOne
     */
    public function belongsToOne(
        $related,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    ) {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if ($relation === null) {
            $relation = $this->guessBelongsToOneRelation();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if ($table === null) {
            $table = $this->joiningTable($related, $instance);
        }

        return $this->newBelongsToOne(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $relation
        );
    }

    /**
     * Instantiate a new BelongsToOne relationship.
     *
     * @phpstan-param string $table
     * @phpstan-param string $foreignPivotKey
     * @phpstan-param string $relatedPivotKey
     * @phpstan-param string $parentKey
     * @phpstan-param string $relatedKey
     *
     * @param mixed $table
     * @param mixed $foreignPivotKey
     * @param mixed $relatedPivotKey
     * @param mixed $parentKey
     * @param mixed $relatedKey
     * @param string|null $relationName
     */
    protected function newBelongsToOne(
        Builder $query,
        Model $parent,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null
    ): BelongsToOne {
        return new BelongsToOne(
            $query,
            $parent,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName
        );
    }

    /**
     * Define a polymorphic one-to-one relationship.
     *
     * @phpstan-param string $related
     * @phpstan-param string $name
     * @phpstan-param bool $inverse
     *
     * @param mixed $related
     * @param mixed $name
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     * @param mixed $inverse
     *
     * @return \Zing\LaravelEloquentRelationships\Relations\MorphToOne
     */
    public function morphToOne(
        $related,
        $name,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $inverse = false
    ) {
        $caller = $this->guessBelongsToOneRelation();

        // First, we will need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we will make the query
        // instances, as well as the relationship instances we need for these.
        $instance = $this->newRelatedInstance($related);

        $foreignPivotKey = $foreignPivotKey ?: $name . '_id';

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // Now we're ready to create a new query builder for this related model and
        // the relationship instances for this relation. This relations will set
        // appropriate query constraints then entirely manages the hydrations.
        if (! $table) {
            $words = preg_split('#(_)#u', $name, -1, PREG_SPLIT_DELIM_CAPTURE);

            $lastWord = array_pop($words);

            $table = implode('', $words) . Str::plural($lastWord);
        }

        return $this->newMorphToOne(
            $instance->newQuery(),
            $this,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey ?: $this->getKeyName(),
            $relatedKey ?: $instance->getKeyName(),
            $caller,
            $inverse
        );
    }

    /**
     * Instantiate a new MorphToOne relationship.
     *
     * @phpstan-param string $name
     * @phpstan-param string $table
     * @phpstan-param string $foreignPivotKey
     * @phpstan-param string $relatedPivotKey
     * @phpstan-param string $parentKey
     * @phpstan-param string $relatedKey
     *
     * @param mixed $name
     * @param mixed $table
     * @param mixed $foreignPivotKey
     * @param mixed $relatedPivotKey
     * @param mixed $parentKey
     * @param mixed $relatedKey
     * @param string|null $relationName
     * @param mixed $inverse
     * @phpstan-param bool $inverse
     */
    protected function newMorphToOne(
        Builder $query,
        Model $parent,
        $name,
        $table,
        $foreignPivotKey,
        $relatedPivotKey,
        $parentKey,
        $relatedKey,
        $relationName = null,
        $inverse = false
    ): MorphToOne {
        return new MorphToOne(
            $query,
            $parent,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            $relationName,
            $inverse
        );
    }

    /**
     * Define a polymorphic, inverse one-to-one relationship.
     *
     * @phpstan-param string $related
     * @phpstan-param string $name
     *
     * @param mixed $related
     * @param mixed $name
     * @param string|null $table
     * @param string|null $foreignPivotKey
     * @param string|null $relatedPivotKey
     * @param string|null $parentKey
     * @param string|null $relatedKey
     *
     * @return \Zing\LaravelEloquentRelationships\Relations\MorphToOne
     */
    public function morphedByOne(
        $related,
        $name,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null
    ) {
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        // For the inverse of the polymorphic one-to-one relations, we will change
        // the way we determine the foreign and other keys, as it is the opposite
        // of the morph-to-one method since we're figuring out these inverses.
        $relatedPivotKey = $relatedPivotKey ?: $name . '_id';

        return $this->morphToOne(
            $related,
            $name,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            $parentKey,
            $relatedKey,
            true
        );
    }

    /**
     * Get the relationship name of the belongsToOne relationship.
     */
    protected function guessBelongsToOneRelation(): ?string
    {
        $caller = Arr::first(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), function ($trace): bool {
            return ! in_array(
                $trace['function'],
                array_merge(static::$oneMethods, ['guessBelongsToOneRelation']),
                true
            );
        });

        return $caller !== null ? $caller['function'] : null;
    }
}
