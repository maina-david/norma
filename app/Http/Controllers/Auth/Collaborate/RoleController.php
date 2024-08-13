<?php

namespace App\Http\Controllers\Auth\Collaborate;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Auth\RoleRequest;
use App\Models\Auth\Role;
use App\Models\Workflows\TaskType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use ReflectionException;

class RoleController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'title';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Role::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.roles';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return RoleRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title',
            'created_at',
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)->collaborate();
    }

    /**
     * Get extra data to be sent back to the create view.
     *
     * @param Request $request
     *
     * @throws ReflectionException
     *
     * @return array<string, mixed>
     */
    protected function createViewData(Request $request): array
    {
        return $this->getExtraViewData();
    }

    /**
     * Get extra data to be sent back to the edit view.
     *
     * @param Request $request
     *
     * @throws ReflectionException
     *
     * @return array<string, mixed>
     */
    protected function editViewData(Request $request): array
    {
        return $this->getExtraViewData();
    }

    /**
     * Get extra data to be sent back to the show view.
     *
     * @param Request $request
     *
     * @throws ReflectionException
     *
     * @return array<string, mixed>
     */
    protected function showViewData(Request $request): array
    {
        return $this->getExtraViewData();
    }

    /**
     * Get all the permissions for the collaborate app.
     *
     * @throws ReflectionException
     *
     * @return array<string, mixed>
     */
    private function getExtraViewData(): array
    {
        return [
            'formWidth' => 'max-w-5xl',
            'permissions' => Role::collaboratePermissions(false)
                ->map(function ($permission) {
                    $label = str_replace('-', '_', $permission);

                    $permissionLabel = str_contains($label, '.computed_')
                        ? $this->computeLabel($permission)
                        : __('permissions.' . $label);

                    return (object) [
                        'group' => __(substr($label, 0, strripos($label, '.') ?: null) . '.index_title'),
                        'label' => $permissionLabel,
                        'value' => $permission,
                        'form' => "permissions[{$permission}]",
                    ];
                })
                ->groupBy('group')
                ->sortBy(fn ($value, $key) => $key)
                ->toArray(),
        ];
    }

    /**
     * Compute the permission name.
     *
     * @param string $permission
     *
     * @return string
     */
    protected function computeLabel(string $permission): string
    {
        if (str_starts_with($permission, 'workflows.access.computed_task_type_')) {
            $matches = [];
            preg_match('/\d+$/', $permission, $matches);
            $match = TaskType::cached($matches[0]);

            if ($match) {
                return $match->name;
            }
        }

        // @codeCoverageIgnoreStart
        /** @var string */
        return __('permissions.' . $permission);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Pre-Store hook.
     *
     * @param Role    $model
     * @param Request $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        $model->permissions = array_keys($request->get('permissions'));
        $model->app = ApplicationType::collaborate();
    }

    /**
     * Pre-Update hook.
     *
     * @param Role    $model
     * @param Request $request
     *
     * @return void
     */
    protected function preUpdate(Model $model, Request $request): void
    {
        $model->permissions = array_keys($request->get('permissions'));
    }
}
