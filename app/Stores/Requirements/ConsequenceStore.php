<?php

namespace App\Stores\Requirements;

use App\Enums\Requirements\ConsequenceAmountType;
use App\Enums\Requirements\ConsequenceType;
use App\Exceptions\ValidationHttpException;
use App\Models\Requirements\Consequence;
use Illuminate\Database\Eloquent\Builder;

class ConsequenceStore
{
    /**
     * Add query modifiers to validate if the model exists.
     * This method should be overridden if incorrect.
     *
     * @param Builder             $builder
     * @param array<string,mixed> $payload
     *
     * @return Builder
     */
    protected function addExistModifiers(Builder $builder, array $payload): Builder
    {
        $builder->whereNull('deleted_at');

        if (isset($payload['id'])) {
            // @codeCoverageIgnoreStart
            $builder->whereKey($payload['id']);
            // @codeCoverageIgnoreEnd
        }

        $fields = [
            'consequence_type',
            'consequence_other_detail',
            'amount_prefix',
            'amount',
            'amount_type',
            'currency',
            'sentence_period',
            'sentence_period_type',
            'per_day',
            'severity',
        ];

        foreach ($fields as $field) {
            if (($payload[$field] ?? 'not-set') === 'not-set') {
                continue;
            }

            if (is_null($payload[$field])) {
                // @codeCoverageIgnoreStart
                $builder->whereNull($field);
                continue;
                // @codeCoverageIgnoreEnd
            }

            $builder->where($field, $payload[$field]);
        }

        return $builder;
    }

    /**
     * Update the model before saving it.
     *
     * @param Consequence $model
     *
     * @return Consequence
     */
    public function preSave(Consequence $model): Consequence
    {
        $model->consequence_type = (int) $model->consequence_type;

        if (!ConsequenceType::fine()->is($model->consequence_type)) {
            $model->amount = null;
            $model->amount_type = null;
            $model->currency = null;
        }

        if (!ConsequenceType::prison()->is($model->consequence_type)) {
            $model->sentence_period = null;
            $model->sentence_period_type = null;
        }
        if (ConsequenceType::other()->is($model->consequence_type)) {
            $model->amount_prefix = null;
        } else {
            $model->consequence_other_detail = null;
        }

        if (!ConsequenceAmountType::currency()->is((int) $model->amount_type)) {
            $model->currency = null;
        }

        return $model;
    }

    /**
     * Validate if current consequence is a duplicate.
     *
     * @param Consequence $consequence
     *
     * @return void
     */
    public function validateDuplicate(Consequence $consequence): void
    {
        /** @var Builder $builder */
        $builder = (new Consequence())->newQueryWithoutScopes();

        $payload = $consequence->toArray();

        if (isset($consequence->id)) {
            $builder->whereKeyNot($consequence->id);
            unset($payload['id']);
        }

        $builder = $this->addExistModifiers($builder, $payload);

        /** @var Consequence|null $exists */
        $exists = $builder->first();

        if ($exists) {
            // @codeCoverageIgnoreStart
            /** @var string $message */
            $message = __('exceptions.requirements.consequence.duplicate_consequence');

            throw new ValidationHttpException("{$message}:{$exists->description}");
            // @codeCoverageIgnoreEnd
        }
    }
}
