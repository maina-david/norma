<?php

namespace App\Http\Controllers\Compilation\My\Settings;

use App\Actions\Compilation\Library\AddChildren;
use App\Actions\Compilation\Library\AddParents;
use App\Actions\Compilation\Library\RemoveChildren;
use App\Actions\Compilation\Library\RemoveParents;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Compilation\Library;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForLibraryLibraryController extends Controller
{
    use PerformsActions;

    /**
     * @param Library $library
     * @param string  $relation
     *
     * @return Response
     */
    public function index(Library $library, string $relation): Response
    {
        $relation = in_array($relation, ['children', 'parents']) ? $relation : 'parents';

        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        if ($organisation = $manager->getActive()) {
            $baseQuery = $library->{$relation}()
                ->forOrganisation($organisation->id)
                ->getQuery();
        } else {
            $baseQuery = userCanManageAllOrgs()
                ? $baseQuery = $library->{$relation}()
                    ->getQuery()
                // @codeCoverageIgnoreStart
                : Library::whereKey(0);
            // @codeCoverageIgnoreEnd
        }

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.compilation.my.library.' . $relation . '-for-library',
            'target' => 'settings-' . $relation . '-for-library-' . $library->id,
            'baseQuery' => $baseQuery,
            'library' => $library,
            'organisation' => $organisation,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request                $request
     * @param Library                $library
     * @param string                 $relation
     * @param Organisation|null|null $organisation
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, Library $library, string $relation, ?Organisation $organisation = null): RedirectResponse
    {
        $relation = in_array($relation, ['children', 'parents']) ? $relation : 'parents';

        $actionName = $this->validateActionName($request);
        /** @var \Illuminate\Database\Eloquent\Collection<Library> */
        $libraries = $this->filterActionInput($request, Library::class);

        $flashMessage = $this->performAction($actionName, $library, $libraries);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.children-parents.for.library.index', ['library' => $library->id, 'relation' => $relation]));

        return $response;
    }

    /**
     * @param string              $action
     * @param Library             $library
     * @param Collection<Library> $libraries
     *
     * @return string
     */
    private function performAction(string $action, Library $library, Collection $libraries): string
    {
        switch ($action) {
            case 'remove_children_from_library':
                RemoveChildren::run($library, $libraries);
                break;
            case 'remove_parents_from_library':
                RemoveParents::run($library, $libraries);
                break;

                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }
        /** @var string $flashMessage */
        $flashMessage = __('actions.success');

        return $flashMessage;
    }

    /**
     * @param Request $request
     * @param Library $library
     * @param string  $relation
     *
     * @return RedirectResponse
     */
    public function addLibraries(Request $request, Library $library, string $relation): RedirectResponse
    {
        $relation = in_array($relation, ['children', 'parents']) ? $relation : 'parents';

        $libraryIds = $request->input('libraries', []);

        /** @var ActiveOrganisationManager */
        $manager = app(ActiveOrganisationManager::class);
        $organisation = $manager->getActive();

        if ($relation === 'children') {
            AddChildren::run($library, $libraryIds, $organisation);
        } else {
            AddParents::run($library, $libraryIds, $organisation);
        }

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse $response */
        $response = redirect(route('my.settings.children-parents.for.library.index', ['library' => $library->id, 'relation' => $relation]));

        return $response;
    }
}
