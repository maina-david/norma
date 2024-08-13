<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Corpus\WorkExpressionRequest;
use App\Jobs\Corpus\CacheWorkContent;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Services\Storage\WorkStorageProcessor;
use App\Stores\Corpus\ContentResourceStore;
use App\Support\ResourceNames;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use ReflectionException;
use Symfony\Component\HttpFoundation\Response;

class WorkExpressionController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'start_date';

    /** @var bool */
    protected bool $searchable = false;

    /** @var bool */
    protected bool $sortable = false;

    protected bool $withDelete = false;

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        return WorkExpression::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function forResource(): string
    {
        return Work::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.work-expressions';
    }

    /**
     * Get base resource route parameters that are required to create the route.
     *
     * @return array<string, mixed>
     */
    protected function resourceRouteParams(): array
    {
        /** @var Request $request */
        $request = request();

        return [
            'work' => $request->route('work'),
        ];
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return WorkExpressionRequest::class;
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
        return parent::baseQuery($request)
            ->with(['work'])
            ->where('work_id', $request->route('work'));
    }

    /**
     * Get the turbo frame target to be updated when crud actions are called requesting a frame update..
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected static function turboTarget(Request $request): ?string
    {
        /** @var int $work */
        $work = $request->route('work');

        return "expressions_corpus_work_{$work}";
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
            'id',
            'created_at' => fn ($row) => self::renderPartial($row, 'title-column'),
            'doc_id' => fn ($row) => self::renderPartial($row, 'data-model-column'),
            'actions' => fn ($row) => self::renderPartial($row, 'actions-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'enctype' => 'multipart/form-data',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return [
            'enctype' => 'multipart/form-data',
        ];
    }

    /**
     * Pre-Store hook.
     *
     * @param WorkExpression $model
     * @param Request        $request
     *
     * @return void
     */
    protected function preStore(Model $model, Request $request): void
    {
        /** @var int $work */
        $work = $request->route('work');
        $model->work_id = $work;

        $this->postUpdate($model, $request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param int     $resourceId
     *
     * @throws AuthorizationException
     *
     * @return View|Response
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        /** @var int $expression */
        $expression = $request->route('work_expression');

        return parent::show($request, $expression);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int     $resourceId
     * @param Request $request
     *
     * @throws AuthorizationException
     *
     * @return View
     */
    public function edit(Request $request, int $resourceId): View
    {
        /** @var int $expression */
        $expression = $request->route('work_expression');

        return parent::edit($request, $expression);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $resourceId
     *
     * @throws AuthorizationException
     * @throws ReflectionException
     *
     * @return RedirectResponse
     */
    public function update(int $resourceId): RedirectResponse
    {
        /** @var Request $request */
        $request = request();

        /** @var int $expression */
        $expression = $request->route('work_expression');

        return parent::update($expression);
    }

    /**
     * {@inheritDoc}
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        /** @var WorkExpression $model */
        if ($request->hasFile('file')) {
            /** @var UploadedFile $file */
            $file = $request->file('file');
            $this->handleDocReplacement($model, $file);
        }
    }

    /**
     * Replace the Aug or July doc.
     *
     * @param \App\Models\Corpus\WorkExpression $expression
     * @param \Illuminate\Http\UploadedFile     $file
     *
     * @return void
     */
    protected function handleDocReplacement(WorkExpression $expression, UploadedFile $file): void
    {
        $expression->load(['doc']);

        if ($expression->doc) {
            $resource = app(ContentResourceStore::class)->storeResource($file->getContent(), $file->getClientMimeType());
            $expression->doc->first_content_resource_id = $resource->id;
            $expression->doc->save();

            return;
        }

        $processor = app(WorkStorageProcessor::class);
        $document = $processor->documentFromContent($expression, $file->getContent(), mime: $file->getMimeType());
        $expression->update(['source_document_id' => $document->id]);
    }

    /**
     * Which route to redirect to after update action.
     *
     * @param WorkExpression $model
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function redirectAfterUpdate(Model $model): string
    {
        $route = static::resourceRoute();

        $params = app(static::class)->resourceRouteParams();

        return route("{$route}.show", [
            Str::snake(ResourceNames::getName(static::resource())) => $model->id,
            ...$params,
        ]);
    }

    /**
     * Which route to redirect to after store action.
     *
     * @param WorkExpression $model
     *
     * @throws ReflectionException
     *
     * @return string
     */
    protected function redirectAfterStore(Model $model): string
    {
        return $this->redirectAfterUpdate($model);
    }

    /**
     * Activate the expression.
     *
     * @param WorkExpression $expression
     *
     * @return RedirectResponse
     */
    public function activate(WorkExpression $expression): RedirectResponse
    {
        $expression->load(['work']);

        $expression->work->update(['active_work_expression_id' => $expression->id]);

        CacheWorkContent::dispatch($expression->work);

        $this->notifySuccessfulUpdate();

        return redirect()->route('collaborate.work-expressions.index', ['work' => $expression->work_id]);
    }

    /**
     * Get the expressions.
     *
     * @param Work $work
     *
     * @return JsonResponse
     */
    public function indexForJson(Work $work): JsonResponse
    {
        $items = WorkExpression::where('work_id', $work->id)
            ->orderBy('start_date')
            ->get(['start_date', 'created_at', 'id'])
            ->map(fn (WorkExpression $exp) => ['id' => $exp->id, 'title' => $exp->start_date ?? $exp->created_at?->format('y-m-d')])
            ->toArray();

        return response()->json($items);
    }
}