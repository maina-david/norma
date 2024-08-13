<?php

namespace App\Http\Controllers\Assess\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Assess\AssessmentItemDescriptionRequest;
use App\Models\Assess\AssessmentItem;
use App\Models\Assess\AssessmentItemDescription;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AssessmentItemDescriptionController extends CollaborateController
{
    protected bool $searchable = false;
    protected bool $withShow = false;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return AssessmentItemDescription::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return AssessmentItemDescriptionRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): ?string
    {
        return AssessmentItem::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.assessment-items.descriptions';
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function index(Request $request): View|Response
    {
        return redirect()->route('collaborate.assessment-items.show', [
            'assessment_item' => $request->route('item'),
            'tab' => 'explanations',
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return [
            'item' => $this->getForResource(),
            'formWidth' => 'w-full',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'item' => $this->getForResource(),
            'formWidth' => 'w-full',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'item' => $request->route('item'),
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
        $item = $this->getForResource();

        return $item->descriptions()->with(['location'])->getQuery();
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param AssessmentItemDescription $model
     *
     * @return string
     */
    protected function redirectAfterStore(Model $model): string
    {
        return route('collaborate.assessment-items.show', [
            'assessment_item' => $model->assessment_item_id,
            'tab' => 'explanations',
        ]);
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param AssessmentItemDescription $model
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }

    /**
     * Which route to redirect to after destroy action.
     *
     * @param AssessmentItemDescription $model
     *
     * @return string
     */
    protected function redirectAfterDestroy(Model $model): string
    {
        return $this->redirectAfterStore($model);
    }
}
