<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

abstract class Observer
{
    /**
     * Get the key value pair of the fields to be checked and
     * the events to be triggered if they have been changed.
     *
     * e.g. [
     *  'user_id' => TaskAssigneeChanged::class,
     * ]
     *
     * @return array<string,mixed>
     */
    protected function onSavedEvents(): array
    {
        return [];
    }

    /**
     * Get the key value pair of the fields to be checked and
     * the events to be triggered if they have been changed.
     *
     * e.g. [
     *  'user_id' => TaskAssigneeChanged::class,
     * ]
     *
     * @return array<string,mixed>
     */
    protected function onUpdatedEvents(): array
    {
        return [];
    }

    /**
     * Get the key value pair of the fields to be checked and
     * the events to be triggered if they are being changed.
     *
     * e.g. [
     *  'user_id' => TaskAssigneeChanged::class,
     * ]
     *
     * @return array<string,mixed>
     */
    protected function onUpdatingEvents(): array
    {
        return [];
    }

    /**
     * Get the key value pair of the fields to be checked and
     * the events to be triggered if they are being changed.
     *
     * e.g. [
     *  'user_id' => TaskAssigneeChanged::class,
     * ]
     *
     * @return array<string,mixed>
     */
    protected function onSavingEvents(): array
    {
        return [];
    }

    /**
     * Check if the keys of the fields is dirty and fire the event if true.
     *
     * @param Model               $model
     * @param array<string,mixed> $fields
     *
     * @return void
     */
    protected function checkAndFire(Model $model, array $fields): void
    {
        foreach ($fields as $field => $event) {
            if ($model->isDirty($field)) {
                $event::dispatch($model);
            }
        }
    }

    /**
     * Listen to the saved event.
     *
     * @param Model $model
     *
     * @return void
     */
    public function saved(Model $model): void
    {
        $this->checkAndFire($model, $this->onSavedEvents());
    }

    /**
     * Listen to the updated event.
     *
     * @param Model $model
     *
     * @return void
     */
    public function updated(Model $model): void
    {
        $this->checkAndFire($model, $this->onUpdatedEvents());
    }

    /**
     * Listen to the updated event.
     *
     * @param Model $model
     *
     * @return void
     */
    public function updating(Model $model): void
    {
        $this->checkAndFire($model, $this->onUpdatingEvents());
    }

    /**
     * Listen to the updated event.
     *
     * @param Model $model
     *
     * @return void
     */
    public function saving(Model $model): void
    {
        $this->checkAndFire($model, $this->onSavingEvents());
    }
}
