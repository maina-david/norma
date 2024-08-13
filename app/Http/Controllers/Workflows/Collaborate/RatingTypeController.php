<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\RatingTypeRequest;
use App\Models\Workflows\RatingType;

class RatingTypeController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'name';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return RatingType::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.rating-types';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return RatingTypeRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'name' => fn ($row) => "<span>{$row->name}</span>", // Example of how to return something custom
            'description',
            'course_link',
            'created_at',
        ];
    }
}
