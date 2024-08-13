<?php

namespace App\Http\Controllers\Traits;

use App\Http\Controllers\Controller;
use App\Support\ResourceNames;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;
use ReflectionException;

/**
 * @mixin Controller
 */
trait AuthorizesCrudActions
{
    /**
     * Determine if the controller should authorise the actions.
     *
     * @return bool
     */
    public static function shouldAuthorise(): bool
    {
        return true;
    }

    /**
     * Authorize a given action for the current user.
     *
     * @param string $action
     * @param mixed  $arguments
     *
     * @throws AuthorizationException
     * @throws ReflectionException
     *
     * @return void
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        if (!static::shouldAuthorise()) {
            // @codeCoverageIgnoreStart
            return;
            // @codeCoverageIgnoreEnd
        }

        $permission = match ($action) {
            'create' => 'create',
            'delete' => 'delete',
            'edit', 'update' => 'update',
            // @codeCoverageIgnoreStart
            'restore' => 'restore',
            // @codeCoverageIgnoreEnd
            'show' => 'view',
            default => 'viewAny',
        };

        // if the model has a Policy, and it has a corresponding method, use that
        $resource = static::resource();

        /** @var array<class-string, class-string> */
        // @phpstan-ignore-next-line
        $policies = Gate::policies();
        $modelsWithPolicies = array_keys($policies);
        if (in_array($resource, $modelsWithPolicies) && method_exists($policies[$resource], $permission)) {
            $this->authorize($permission, $arguments);

            return;
        }

        $prefix = $this->permissionPrefix();

        $this->authorize("{$prefix}.{$permission}", $arguments);
    }

    /**
     * Get the premission prefix.
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function permissionPrefix(): string
    {
        $resource = ResourceNames::getPermissionPath(static::resource());

        $app = static::application()->value;

        return "{$app}.{$resource}";
    }
}
