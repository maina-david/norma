<?php

namespace App\Http\Controllers\Geonames\My;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Geonames\Location;
use App\Models\Storage\My\File;
use App\Stores\Storage\FileLocationStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForFileLocationController extends Controller
{
    use PerformsActions;

    /**
     * @param File $file
     *
     * @return Response
     */
    public function index(File $file): Response
    {
        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.geonames.my.location.for-file',
            'target' => 'location-for-file-' . $file->id,
            'baseQuery' => Location::whereRelation('files', 'id', $file->id),
            'file' => $file,
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Perform a given action on a given list of users.
     *
     * @param Request $request
     * @param File    $file
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, File $file): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        /** @var Collection<int, Location> $locations */
        $locations = $this->filterActionInput($request, Location::class);

        $flashMessage = $this->performAction($actionName, $file, $locations);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse */
        return redirect(route('my.locations.for.file.index', ['file' => $file->id]));
    }

    /**
     * Trigger the action.
     *
     * @param string                    $action
     * @param File                      $file
     * @param Collection<int, Location> $locations
     *
     * @return string
     */
    private function performAction(string $action, File $file, Collection $locations): string
    {
        $store = app(FileLocationStore::class);

        switch ($action) {
            case 'remove_from_file':
                $store->detachLocations($file, $locations);
                break;

                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        /** @var string */
        return __('actions.success');
    }

    /**
     * Attach the locations to the libryo.
     *
     * @param Request $request
     * @param File    $file
     *
     * @return RedirectResponse
     */
    public function add(Request $request, File $file): RedirectResponse
    {
        $locations = Location::whereKey($request->input('locations', []))->get();

        app(FileLocationStore::class)->attachLocations($file, $locations);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse */
        return redirect(route('my.locations.for.file.index', ['file' => $file->id]));
    }
}
