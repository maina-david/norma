<?php

namespace App\Models\Traits;

use App\Enums\Storage\My\FolderType;
use Illuminate\Database\Eloquent\Builder;

trait HasFolderType
{
    /**
     * Checks if a folder is of type organisation.
     *
     * @return bool
     **/
    public function isOrganisation(): bool
    {
        return FolderType::organisation()->is($this->folder_type);
    }

    /**
     * Checks if a folder is of type norma.
     *
     * @return bool
     **/
    public function isNorma(): bool
    {
        return FolderType::norma()->is($this->folder_type);
    }

    /**
     * Checks if a folder is of type global.
     *
     * @return bool
     **/
    public function isGlobal(): bool
    {
        return FolderType::global()->is($this->folder_type);
    }

    /**
     * Checks if a folder is of type system.
     *
     * @return bool
     **/
    public function isSystem(): bool
    {
        return FolderType::system()->is($this->folder_type) || $this->folder_type === 0;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include folders/files of type global.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeOrganisation(Builder $query): Builder
    {
        return $query->where('folder_type', FolderType::organisation()->value);
    }

    /**
     * Scope a query to only include folders/files of type system.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeNorma(Builder $query): Builder
    {
        return $query->where('folder_type', FolderType::norma()->value);
    }

    /**
     * Scope a query to only include folders/files of type global.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeTypeGlobal(Builder $query): Builder
    {
        return $query->where('folder_type', FolderType::global()->value);
    }

    /**
     * Scope a query to only include folders/files of type system.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    // public function scopeTypeSystem(Builder $query): Builder
    // {
    // return $query->where('folder_type', FolderType::system()->value);
    // }
}
