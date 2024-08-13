<?php

namespace App\Support;

use App\Managers\AppManager;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionException;

class ResourceNames
{
    /**
     * @param string $modelClass
     *
     * @return string
     */
    public static function getLongName(string $modelClass): string
    {
        /** @var class-string|object */
        $clssName = (string) $modelClass;
        $name = (new ReflectionClass($clssName))->getName();

        return Str::of($name)->after('App\\Models\\');
    }

    /**
     * @param string $modelClass
     *
     * @throws ReflectionException
     *
     * @return string
     */
    public static function getName(string $modelClass): string
    {
        /** @var class-string|object */
        $clssName = (string) $modelClass;

        return (new ReflectionClass($clssName))->getShortName();
    }

    // /**
    //  * @param string $modelClass
    //  *
    //  * @throws ReflectionException
    //  *
    //  * @return string
    //  */
    // public static function getNameKebab(string $modelClass): string
    // {
    //     $name = static::getName($modelClass);

    //     return Str::kebab($name);
    // }

    /**
     * @param string $modelClass
     *
     * @return string
     */
    public static function getViewPath(string $modelClass): string
    {
        $path = Str::of(static::getLongName($modelClass))
            ->explode('\\')
            ->map(fn ($part) => Str::kebab($part));

        $path->splice($path->count() - 1, 0, [AppManager::getApp()]);

        return $path->implode('.');
    }

    /**
     * @param string $modelClass
     *
     * @return string
     */
    public static function getPermissionPath(string $modelClass): string
    {
        return Str::of(static::getLongName($modelClass))
            ->explode('\\')
            ->map(fn ($part) => Str::kebab($part))
            ->implode('.');
    }

    /**
     * @param string $modelClass
     *
     * @return string
     */
    public static function getLangPath(string $modelClass): string
    {
        $prefix = Str::of(static::getLongName($modelClass))
            ->beforeLast('\\')
            ->explode('\\')
            ->map(fn ($part) => Str::kebab($part))
            ->implode('.');

        return Str::of(static::getName($modelClass))
            ->snake()
            ->when($prefix !== '', fn ($s) => $s->prepend($prefix . '.'));
    }
}
