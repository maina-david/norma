<?php

namespace App\Http\Controllers\Lookups\Collaborate;

use App\Enums\Lookups\CannedResponseField;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Lookups\CannedResponseRequest;
use App\Models\Lookups\CannedResponse;
use Illuminate\View\ComponentAttributeBag;

class CannedResponseController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'response';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return CannedResponse::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.canned-responses';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return CannedResponseRequest::class;
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
     * @return array<string|int, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'response',
            'for_field' => fn ($row) => CannedResponseField::lang()[$row->for_field] ?? '-',
            'created_at',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceFilters(): array
    {
        return [
            'for_field' => [
                'label' => __('lookups.canned_response.for_field'),
                'render' => fn () => view('components.lookups.canned-response-field-selector', [
                    'attributes' => new ComponentAttributeBag(),
                    'name' => 'for_field',
                ]),
                'value' => fn ($value) => str_replace('_', ' ', $value),
            ],
        ];
    }
}
