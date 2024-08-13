<?php

namespace App\Http\Controllers\Collaborators\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Collaborators\GroupRequest;
use App\Models\Collaborators\Group;

class GroupController extends CollaborateController
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
        return Group::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.groups';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return GroupRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string>
     */
    protected static function indexColumns(): array
    {
        return ['title', 'description', 'created_at'];
    }
}
