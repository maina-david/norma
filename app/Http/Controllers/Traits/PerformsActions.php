<?php

namespace App\Http\Controllers\Traits;

use App\Models\Auth\User;
use App\Models\Customer\Organisation;
use App\Models\Customer\Team;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

/**
 * Helper method for controllers that perform actions for a data table.
 */
trait PerformsActions
{
    /**
     * @param Request       $request
     * @param string        $modelClass
     * @param callable|null $beforeFetch
     *
     * @return Collection<\Illuminate\Database\Eloquent\Model>
     */
    private function filterActionInput(Request $request, string $modelClass, ?callable $beforeFetch = null): Collection
    {
        $ids = [];
        foreach ($request->all() as $key => $input) {
            if (Str::startsWith($key, 'actions-checkbox-')) {
                $ids[] = (int) Str::afterLast($key, 'actions-checkbox-');
            }
        }
        if (count($ids) === 0) {
            Session::flash('flash.type', 'danger');
            Session::flash('flash.message', __('actions.errors.no_items_selected'));
            abort(422);
        }

        return tap($modelClass::whereKey($ids), $beforeFetch ?? fn ($q) => $q)->get();
    }

    /**
     * @param Request           $request
     * @param string            $modelClass
     * @param Organisation|null $organisation
     *
     * @return Collection<\Illuminate\Database\Eloquent\Model>
     */
    private function filterActionInputForOrg(Request $request, string $modelClass, ?Organisation $organisation): Collection
    {
        $collection = $this->filterActionInput($request, $modelClass);

        if (!is_null($organisation)) {
            if (method_exists($modelClass, 'organisation')) {
                $collection->load('organisation');
                /** @var Team $model */
                foreach ($collection as $model) {
                    if ($organisation->id !== $model->organisation?->id) {
                        abort(403);
                    }
                }
            }
            if (method_exists($modelClass, 'organisations')) {
                $collection->load('organisations');
                /** @var User $model */
                foreach ($collection as $model) {
                    if (!$model->organisations->contains($organisation)) {
                        abort(403);
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * @param Request $request
     *
     * @return string
     */
    private function validateActionName(Request $request): string
    {
        if (!$actionName = $request->input('action')) {
            abort(422);
        }

        return $actionName;
    }
}
