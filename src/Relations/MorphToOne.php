<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Relations;

use Illuminate\Contracts\Database\Eloquent\SupportsPartialRelations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\CanBeOneOfMany;
use Illuminate\Database\Eloquent\Relations\Concerns\ComparesRelatedModels;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Query\JoinClause;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @extends \Illuminate\Database\Eloquent\Relations\MorphToMany<TRelatedModel>
 */
class MorphToOne extends MorphToMany implements SupportsPartialRelations
{
    use SupportsDefaultModels;
    use CanBeOneOfMany;
    use ComparesRelatedModels;

    /**
     * Initialize the relation on a set of models.
     *
     * @param \Illuminate\Database\Eloquent\Model[] $models
     * @param string $relation
     *
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function initRelation(array $models, $relation): array
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param \Illuminate\Database\Eloquent\Model[] $models
     * @param string $relation
     *
     * @return \Illuminate\Database\Eloquent\Model[]
     */
    public function match(array $models, Collection $results, $relation): array
    {
        $dictionary = $this->buildDictionary($results);

        // Once we have an array dictionary of child objects we can easily match the
        // children back to their parent using the dictionary and the keys on the
        // parent models. Then we should return these hydrated models back out.
        foreach ($models as $model) {
            $key = $this->getDictionaryKey($model->getAttribute($this->parentKey));
            if (isset($dictionary[$key])) {
                $value = $dictionary[$key];
                $model->setRelation($relation, reset($value));
            }
        }

        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Model|mixed|null
     */
    public function getResults()
    {
        if ($this->parent->getAttribute($this->parentKey) === null) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->first() ?: $this->getDefaultFor($this->parent);
    }

    /**
     * Make a new related instance for the given model.
     */
    protected function newRelatedInstanceFor(Model $parent): Model
    {
        return $this->related->newInstance();
    }

    public function addConstraints(): void
    {
        if (! $this->isOneOfMany()) {
            parent::addConstraints();
        }

        if (static::$constraints) {
            $this->addWhereConstraints();
        }
    }

    public function addOneOfManySubQueryConstraints(Builder $query, $column = null, $aggregate = null): void
    {
        $query->join($this->table, $this->getQualifiedRelatedKeyName(), '=', $this->getQualifiedRelatedPivotKeyName())
            ->addSelect([$this->foreignPivotKey, $this->morphType]);
    }

    /**
     * @return string[]
     */
    public function getOneOfManySubQuerySelectColumns(): array
    {
        return [$this->getQualifiedForeignPivotKeyName(), $this->qualifyPivotColumn($this->morphType)];
    }

    public function addOneOfManyJoinSubQueryConstraints(JoinClause $join): void
    {
        $join
            ->on($this->qualifySubSelectColumn($this->morphType), '=', $this->qualifyPivotColumn($this->morphType))
            ->on(
                $this->qualifySubSelectColumn($this->foreignPivotKey),
                '=',
                $this->qualifyPivotColumn($this->foreignPivotKey)
            );
    }

    public function getParentKey(): void
    {
        
    }

    protected function getRelatedKeyFrom(Model $model): void
    {
        
    }

    protected function compareKeys($parentKey, $relatedKey): bool
    {
        return true;
    }
}