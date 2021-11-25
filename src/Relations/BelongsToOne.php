<?php

declare(strict_types=1);

namespace Zing\LaravelEloquentRelationships\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsToMany<TRelatedModel>
 */
class BelongsToOne extends BelongsToMany
{
    use SupportsDefaultModels;

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
            $key = $model->{$this->parentKey};
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
        if ($this->parent->{$this->parentKey} === null) {
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
}
