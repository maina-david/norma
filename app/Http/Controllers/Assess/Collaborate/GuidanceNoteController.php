<?php

namespace App\Http\Controllers\Assess\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Assess\GuidanceNoteRequest;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\GuidanceNote;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use ReflectionException;

class GuidanceNoteController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'description';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return GuidanceNote::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.guidance-notes';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return GuidanceNoteRequest::class;
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
     * @codeCoverageIgnore
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [];
    }

    /**
     * Pre-Store hook.
     *
     * @param GuidanceNote $model
     * @param Request      $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        /** @var AssessmentItem $item */
        $item = AssessmentItem::findOrFail($request->route('item'));

        $model->assessment_item_id = $item->id;
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param GuidanceNote $model
     *
     * @return string
     */
    protected function redirectAfterStore(Model $model): string
    {
        return route('collaborate.assessment-items.show', [
            'assessment_item' => $model->assessment_item_id,
            'tab' => 'guidance',
        ]);
    }

    /**
     * Which route to redirect to after destroy action.
     *
     * @param GuidanceNote $model
     *
     * @return string
     */
    protected function redirectAfterDestroy(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }

    /**
     * Which route to redirect to after update action.
     *
     * @codeCoverageIgnore
     *
     * @param GuidanceNote $model
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }
}
