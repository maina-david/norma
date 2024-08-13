<?php

namespace App\Models\Auth;

use App\Cache\Workflows\Collaborate\TaskTypeForRoleCache;
use App\Enums\Application\ApplicationType;
use App\Models\AbstractModel;
use App\Support\ResourceNames;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Contracts\Auditable;
use ReflectionException;

/**
 * @mixin IdeHelperRole
 */
class Role extends AbstractModel implements Auditable
{
    use \OwenIt\Auditing\Auditable;

    /** @var array<string, string> */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_role')->using(UserRole::class);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeAdmin(Builder $builder): Builder
    {
        return $builder->where('app', ApplicationType::admin()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeCollaborate(Builder $builder): Builder
    {
        return $builder->where('app', ApplicationType::collaborate()->value);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeMy(Builder $builder): Builder
    {
        return $builder->where('app', ApplicationType::my()->value);
    }

    /**
     * Extract the permissions for the given group.
     *
     * @param ApplicationType $app
     * @param bool            $prefix
     *
     * @throws ReflectionException
     *
     * @return Collection<int, mixed>
     */
    protected static function extractPermissions(ApplicationType $app, bool $prefix = false): Collection
    {
        $app = $app->value;
        $settings = config("permissions.{$app}");

        /** @var Collection<int, mixed> $permissions */
        $permissions = collect();

        collect($settings['crud'])->each(function (string $class) use ($app, $prefix, $permissions) {
            $common = ['create', 'delete', 'update', 'viewAny', 'view'];
            if (method_exists($class, 'restore')) {
                $common[] = 'restore';
            }

            $exclude = $class::excludeFromCrud()[$app] ?? [];
            $class = ResourceNames::getPermissionPath($class);

            if (!empty($exclude)) {
                $common = array_filter($common, fn ($item) => !in_array($item, $exclude));
            }

            $class = $prefix ? "{$app}.{$class}" : $class;

            collect($common)->each(fn (string $perm) => $permissions->push("{$class}.{$perm}"));
        });

        collect($settings['plain'])->each(function (string $perm) use ($app, $prefix, $permissions) {
            $perm = $prefix ? "{$app}.{$perm}" : $perm;
            $permissions->push($perm);
        });

        return $permissions;
    }

    /**
     * Get the permissions for the "admin" application.
     *
     * @param bool $prefix
     *
     * @throws ReflectionException
     *
     * @return Collection<int,mixed>
     */
    public static function adminPermissions(bool $prefix = false): Collection
    {
        return self::extractPermissions(ApplicationType::admin(), $prefix);
    }

    /**
     * Get the permissions for the "collaborate" application.
     *
     * @param bool $prefix
     *
     * @throws ReflectionException
     *
     * @return Collection<int,mixed>
     */
    public static function collaboratePermissions(bool $prefix = false): Collection
    {
        $permissions = self::extractPermissions(ApplicationType::collaborate(), $prefix);

        try {
            $types = (new TaskTypeForRoleCache())->get();
        } catch (Exception $ex) {
            // @codeCoverageIgnoreStart
            $types = [];
            // @codeCoverageIgnoreEnd
        }

        if ($prefix) {
            $types = collect($types)->map(fn ($type) => ApplicationType::collaborate()->value . ".{$type}");
        }

        return $permissions->merge($types);
    }

    /**
     * Get the permissions for the "my" application.
     *
     * @param bool $prefix
     *
     * @throws ReflectionException
     *
     * @return Collection<int, mixed>
     */
    public static function myPermissions(bool $prefix = false): Collection
    {
        return self::extractPermissions(ApplicationType::my(), $prefix);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
        ];
    }
}
