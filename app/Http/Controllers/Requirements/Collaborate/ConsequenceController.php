<?php

namespace App\Http\Controllers\Requirements\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Requirements\ConsequenceRequest;
use App\Models\Requirements\Consequence;
use Illuminate\Http\Request;

class ConsequenceController extends CollaborateController
{
    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Consequence::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.consequences';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return ConsequenceRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table.
     *
     * Use the dot notation for relations and for custom results use functions that
     * will receive the current row as an arg. The field name will be used as the
     * translation key but when you use functions, make sure the array is keyed with the
     * translation name.
     *
     * e.g. ['profile' => 'profile.id', 'name'].
     *
     * @return array<int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'description',
        ];
    }

    /**
     * Get extra data to be sent back to the form views.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function formViewsData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
        ];
    }

    /**
     * Get extra data to be sent back to the create view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function createViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }

    /**
     * Get extra data to be sent back to the edit view.
     *
     * @param Request $request
     *
     * @return array<string, mixed>
     */
    protected function editViewData(Request $request): array
    {
        return $this->formViewsData($request);
    }
}
