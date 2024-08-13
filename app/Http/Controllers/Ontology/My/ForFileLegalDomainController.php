<?php

namespace App\Http\Controllers\Ontology\My;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PerformsActions;
use App\Models\Ontology\LegalDomain;
use App\Models\Storage\My\File;
use App\Stores\Storage\FileLegalDomainStore;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class ForFileLegalDomainController extends Controller
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
            'partialView' => 'partials.ontology.my.legal-domain.for-file',
            'target' => 'legal-domain-for-file-' . $file->id,
            'baseQuery' => LegalDomain::whereRelation('files', 'id', $file->id),
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

        /** @var Collection<int, LegalDomain> $domains */
        $domains = $this->filterActionInput($request, LegalDomain::class);

        $flashMessage = $this->performAction($actionName, $file, $domains);

        Session::flash('flash.message', $flashMessage);

        /** @var RedirectResponse */
        return redirect(route('my.legal-domains.for.file.index', ['file' => $file->id]));
    }

    /**
     * Trigger the action.
     *
     * @param string                       $action
     * @param File                         $file
     * @param Collection<int, LegalDomain> $domains
     *
     * @return string
     */
    private function performAction(string $action, File $file, Collection $domains): string
    {
        $store = app(FileLegalDomainStore::class);

        switch ($action) {
            case 'remove_from_file':
                $store->detachLegalDomains($file, $domains);
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
     * Attach the legal domains to the libryo.
     *
     * @param Request $request
     * @param File    $file
     *
     * @return RedirectResponse
     */
    public function add(Request $request, File $file): RedirectResponse
    {
        $domains = LegalDomain::whereKey($request->input('legal-domains', []))->get();

        app(FileLegalDomainStore::class)->attachLegalDomains($file, $domains);

        Session::flash('flash.message', __('actions.success'));

        /** @var RedirectResponse */
        return redirect(route('my.legal-domains.for.file.index', ['file' => $file->id]));
    }
}
