<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Enums\Application\ApplicationType;
use App\Http\Controllers\Abstracts\CrudController;
use App\Http\Requests\Compilation\LibraryRequest;
use App\Models\Compilation\Library;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LibraryController extends CrudController
{
    /**
     * @param ActiveOrganisationManager $activeOrganisationManager
     */
    public function __construct(
        protected ActiveOrganisationManager $activeOrganisationManager,
    ) {
    }

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Library::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'my.settings.libraries';
    }

    protected function appLayout(): string
    {
        return 'layouts.settings';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return LibraryRequest::class;
    }

    /**
     * Get the application to be used when authorising the requests.
     *
     * @codeCoverageIgnore
     *
     * @return ApplicationType
     */
    protected static function application(): ApplicationType
    {
        return ApplicationType::my();
    }

    /**
     * @codeCoverageIgnore
     *
     * @return array<mixed>
     */
    protected static function indexColumns(): array
    {
        return [];
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        $organisation = $this->activeOrganisationManager->getActive();

        if ($organisation) {
            $baseQuery = Library::forOrganisation($organisation->id);
        } else {
            $baseQuery = userCanManageAllOrgs() ? (new Library())->newQuery() : null;
        }

        if ($request->wantsJson()) {
            // @phpstan-ignore-next-line
            $json = $baseQuery->filter($request->all())
                ->get(['id', 'title', 'description'])
                ->map(function ($item) {
                    /** @var Library $item */
                    return ['id' => $item->id, 'title' => $item->title, 'details' => $item->description];
                })
                ->toArray();

            return response()->json($json);
        }

        /** @var View $view */
        $view = view('pages.compilation.my.library.settings.index', [
            'baseQuery' => $baseQuery,
            'organisation' => $organisation,
        ]);

        return $view;
    }

    /**
     * {@inheritDoc}
     */
    public function show(Request $request, int $library): View|Response
    {
        /** @var Library */
        $library = Library::findOrFail($library);
        /** @var View $view */
        $view = view('pages.compilation.my.library.settings.show', ['library' => $library]);

        return $view;
    }
}
