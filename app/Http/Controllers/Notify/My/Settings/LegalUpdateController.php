<?php

namespace App\Http\Controllers\Notify\My\Settings;

use App\Http\Controllers\Compilation\My\Settings\SessionLibraryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Compilation\Library;
use App\Models\Customer\Norma;
use App\Models\Notify\LegalUpdate;
use App\Stores\Notify\LegalUpdateStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LegalUpdateController extends Controller
{
    use PerformsActions;

    protected string $sessionKey = 'notify_updates';

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $baseQuery = (new LegalUpdate())->newQuery()
            ->withCount(['libraries', 'normas'])
            ->orderBy('id', 'desc');

        /** @var View */
        return view('pages.notify.legal-update.my.settings.index', ['baseQuery' => $baseQuery]);
    }

    /**
     * Display the details of the resource.
     *
     * @param LegalUpdate $update
     *
     * @return View
     */
    public function show(LegalUpdate $update): View
    {
        app(SessionLibraryController::class)->clearLibraries($this->sessionKey);

        $update->load(['work', 'notifyReference.work', 'notifiable']);

        /** @var View */
        return view('pages.notify.legal-update.my.settings.show', [
            'update' => $update,
            'sessionKey' => $this->sessionKey,
        ]);
    }

    /**
     * Add the libraries to the update and redirect.
     *
     * @param LegalUpdate         $update
     * @param Collection<Library> $libraries
     *
     * @return RedirectResponse
     */
    protected function addLibrariesAndRedirect(LegalUpdate $update, Collection $libraries): RedirectResponse
    {
        if ($libraries->isNotEmpty()) {
            app(LegalUpdateStore::class)->attachLibraries($update, $libraries);
        }

        return to_route('my.settings.legal-updates.show', ['update' => $update]);
    }

    /**
     * Add libraries to the legal update.
     *
     * @param LegalUpdate $update
     *
     * @return RedirectResponse
     */
    public function addByLibraries(LegalUpdate $update): RedirectResponse
    {
        $libraries = app(SessionLibraryController::class)->getLibraries($this->sessionKey);

        /** @var Collection<Library> $libraries */
        $libraries = Library::whereKey($libraries)->get(['id']);

        return $this->addLibrariesAndRedirect($update, $libraries);
    }

    /**
     * Add libraries to the legal update using references.
     *
     * @param Request     $request
     * @param LegalUpdate $update
     *
     * @return RedirectResponse
     */
    public function addByReferences(Request $request, LegalUpdate $update): RedirectResponse
    {
        $ids = $request->get('references', []);

        /** @var Collection<Library> $libraries */
        $libraries = (new Library())->newCollection();

        if (!empty($ids)) {
            /** @var Collection<Library> $libraries */
            $libraries = Library::whereRelation('references', fn ($query) => $query->whereKey($ids))->get(['id']);
        }

        return $this->addLibrariesAndRedirect($update, $libraries);
    }

    /**
     * Add libraries to the legal update using normas.
     *
     * @param Request     $request
     * @param LegalUpdate $update
     *
     * @return RedirectResponse
     */
    public function addByNormas(Request $request, LegalUpdate $update): RedirectResponse
    {
        $request->validate(['normas' => ['required', 'array']]);

        $ids = $request->get('normas', []);

        if (!empty($ids)) {
            /** @var Collection<Norma> $normas */
            $normas = Norma::whereKey($ids)->get(['id']);
            app(LegalUpdateStore::class)->attachNormas($update, $normas);
        }

        return to_route('my.settings.legal-updates.show', ['update' => $update]);
    }

    /**
     * Perform a given action on a given list of libraries.
     *
     * @param Request     $request
     * @param LegalUpdate $update
     *
     * @return RedirectResponse
     */
    public function actions(Request $request, LegalUpdate $update): RedirectResponse
    {
        $actionName = $this->validateActionName($request);

        /** @var Collection<Library> $libraries */
        $libraries = $this->filterActionInput($request, Library::class);

        $flashMessage = $this->performAction($actionName, $update, $libraries);

        Session::flash('flash.message', $flashMessage);

        return to_route('my.settings.legal-updates.show', ['update' => $update]);
    }

    /**
     * @param string              $action
     * @param LegalUpdate         $update
     * @param Collection<Library> $libraries
     *
     * @return string
     */
    private function performAction(string $action, LegalUpdate $update, Collection $libraries): string
    {
        $flashMessage = '';
        switch ($action) {
            case 'remove_from_update':
                app(LegalUpdateStore::class)->detachLibraries($update, $libraries);
                /** @var string $flashMessage */
                $flashMessage = __('actions.success');
                break;

                // @codeCoverageIgnoreStart
            default:
                abort(422);
                // @codeCoverageIgnoreEnd
        }

        return $flashMessage;
    }
}
