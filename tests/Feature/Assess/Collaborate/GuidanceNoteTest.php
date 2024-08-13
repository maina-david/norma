<?php

namespace Tests\Feature\Assess\Collaborate;

use App\Models\Assess\AssessmentItem;
use App\Models\Assess\GuidanceNote;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Tests\Feature\Abstracts\CrudTestCase;

class GuidanceNoteTest extends CrudTestCase
{
    /** @var string */
    protected string $sortBy = 'description';

    protected bool $collaborate = true;

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
     * The list of database columns that should be visible on the pages.
     *
     * @return string[]
     */
    protected static function visibleFields(): array
    {
        return ['description'];
    }

    /**
     * The list of database columns that should be visible on the pages with forms.
     *
     * @return string[]
     */
    protected static function visibleLabels(): array
    {
        return ['Notes'];
    }

    /**
     * Get the labels that are visible on the show page.
     *
     * @return array<int, string>
     */
    protected static function visibleShowLabels(): array
    {
        return [];
    }

    /**
     * A list of actions to exclude from testing.
     * Options: index, create, store, edit, update, destroy.
     *
     * @return array
     */
    protected static function excludeActions(): array
    {
        return [
            'index', 'show', 'create', 'update',
        ];
    }

    /**
     * Perform tasks before creation of the test model.
     *
     * @param Factory $factory
     * @param string  $route
     *
     * @return Factory
     */
    protected static function preCreate(Factory $factory, string $route): Factory
    {
        return $factory->state(['assessment_item_id' => AssessmentItem::factory()->create()->id]);
    }

    /**
     * Get the route to be used.
     *
     * @param string $action
     * @param array  $payload
     *
     * @return string
     */
    protected function getRoute(string $action, array $payload = []): string
    {
        $params = ['item' => $payload['assessment_item_id']];

        if (!in_array($action, ['store', 'index']) && isset($payload['id'])) {
            $params['guidance_note'] = $payload['id'];
        }

        return route(sprintf('%s.%s', static::resourceRoute(), $action), $params);
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param array $model
     *
     * @return string
     */
    protected function redirectAfterStore(array $model): string
    {
        return route('collaborate.assessment-items.show', [
            'assessment_item' => $model['assessment_item_id'],
            'tab' => 'guidance',
        ]);
    }

    /**
     * Which route to redirect to after destroy action.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function redirectAfterDestroy(Model $model): string
    {
        return $this->redirectAfterStore($model->toArray());
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param Model $model
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        return $this->redirectAfterStore($model->toArray());
    }
}
