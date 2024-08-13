<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Contracts\Http\ResourceAction;
use App\Http\Requests\Corpus\WorkWorkAttachmentRequest;
use App\Http\ResourceActions\Corpus\DetachWorkFromWork;
use App\Models\Auth\User;
use App\Models\Corpus\Pivots\WorkWork;
use App\Models\Corpus\Work;
use App\Stores\Corpus\WorkStore;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class WorkWorkRelationController extends WorkController
{
    /** @var bool */
    protected bool $withCreate = true;

    /** @var bool */
    protected bool $withUpdate = false;

    /** @var bool */
    protected bool $withDelete = false;

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        /** @var Route $route */
        $route = $request->route();

        /** @var string $name */
        $name = $route->getName();

        $relation = str_contains($name, 'children') ? 'parents' : 'children';

        return parent::baseQuery($request)
            ->with(['primaryLocation'])
            ->whereRelation($relation, 'id', '=', $route->parameter('work'));
    }

    /**
     * The current route relation.
     *
     * @return string
     */
    protected static function relation(): string
    {
        /** @var Request $request */
        $request = request();
        /** @var Route $route */
        $route = $request->route();

        return str_contains($route->getName() ?? '', 'children') ? 'children' : 'parents';
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @phpstan-return class-string
     *
     * @return string
     */
    protected static function resource(): string
    {
        /** @var Request $request */
        $request = request();

        return $request->routeIs('collaborate.works.children.create')
            ? WorkWork::class
            : Work::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.works.' . self::relation();
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
     * Authorize a given action for the current user.
     *
     * @param string $action
     * @param mixed  $arguments
     *
     * @throws AuthorizationException
     *
     * @return void
     */
    protected function authoriseAction(string $action, mixed $arguments = []): void
    {
        /** @var User $user */
        $user = Auth::user();

        $type = self::relation();

        if ($action === 'index') {
            abort_unless($user->canAny([
                "collaborate.corpus.work.view-{$type}",
                'collaborate.corpus.work.detach',
                'collaborate.corpus.work.attach',
            ]), 403);

            return;
        }

        abort_unless($user->canAny(['collaborate.corpus.work.detach', 'collaborate.corpus.work.attach']), 403);
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
        return WorkWorkAttachmentRequest::class;
    }

    /**
     * Get the turbo frame target to be updated when crud actions are called requesting a frame update.
     *
     * @param Request $request
     *
     * @return string|null
     */
    protected static function turboTarget(Request $request): ?string
    {
        $prefix = self::relation();
        /** @var int $work */
        $work = $request->route('work');

        return "{$prefix}_corpus_work_{$work}";
    }

    /**
     * Get the action route for the resource.
     *
     * @return string
     */
    protected static function resourceActionRoute(): string
    {
        $relation = self::relation();

        /** @var Request $request */
        $request = request();

        return route("collaborate.works.{$relation}.actions", $request->route('work'));
    }

    /**
     * Get the resource actions.
     *
     * @return array<int, ResourceAction>
     */
    protected static function resourceActions(): array
    {
        /** @var Request $request */
        $request = request();

        /** @var int $work */
        $work = $request->route('work');

        return [
            new DetachWorkFromWork($work, self::relation()),
        ];
    }

    /**
     * Get extra attributest to append to the anchor tag that links to the show page.
     *
     * @param Model $row
     *
     * @return array<string, string>
     */
    protected function showAnchorAttributes(Model $row): array
    {
        return [
            'target' => '_top',
        ];
    }

    /**
     * Get the path to the common form that is to be used.
     *
     * @return string
     */
    protected function getFormView(): string
    {
        return 'partials.corpus.collaborate.work-work-relation.form';
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
        return [
            'fluid' => true,
            'formWidth' => 'max-w-full',
            'selected' => Work::findOrFail($request->route('work')),
            'relation' => self::relation(),
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @throws AuthorizationException
     *
     * @return View|Response
     */
    public function index(Request $request): View|Response
    {
        if ($request->isForTurboFrame()) {
            return parent::index($request);
        }

        /** @var Request $request */
        $request = request();

        /** @var int $work */
        $work = $request->route('work');

        return redirect()->route('collaborate.works.show', ['work' => $work, 'tab' => self::relation()]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @param int     $resourceId
     *
     * @return View|Response
     */
    public function show(Request $request, int $resourceId): View|Response
    {
        /** @var string $resource */
        $resource = $request->route(Str::singular(self::relation()));

        return redirect()->route('collaborate.works.show', (int) $resource);
    }

    /**
     * Create the specified resource in storage.
     *
     * @throws AuthorizationException
     *
     * @return RedirectResponse
     */
    public function store(): RedirectResponse
    {
        $this->authorize('collaborate.corpus.work.attach');

        /** @var Request $request */
        $request = app(self::resourceFormRequest());

        /** @var Work $work */
        $work = Work::findOrFail($request->route('work'));

        $relation = self::relation();
        $related = Work::whereKey($request->get('works', []))->pluck('id');
        $method = $relation === 'children' ? 'attachChildren' : 'attachParents';

        app(WorkStore::class)->{$method}($work, $related);

        Session::flash('flash.message', __('notifications.successfully_attached'));

        return redirect()->route('collaborate.works.show', ['work' => $work->id, 'tab' => $relation]);
    }
}
