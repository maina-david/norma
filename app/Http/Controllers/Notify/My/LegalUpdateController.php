<?php

namespace App\Http\Controllers\Notify\My;

use App\Actions\Notify\LegalUpdate\UpdateReadUnderstoodStatus;
use App\Enums\Auth\UserActivityType;
use App\Enums\Notify\LegalUpdatePublishedStatus;
use App\Enums\Notify\LegalUpdateStatus;
use App\Events\Auth\UserActivity\GenericActivityUsingAuth;
use App\Events\Auth\UserActivity\LegalUpdates\LegalUpdatesExported;
use App\Events\Auth\UserActivity\LegalUpdates\LegalUpdatesFiltered;
use App\Events\Auth\UserActivity\LegalUpdates\LegalUpdatesSearched;
use App\Http\Controllers\Controller;
use App\Jobs\Exports\GenerateLegalUpdateExportExcel;
use App\Jobs\Exports\GenerateLegalUpdateExportPDF;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateUser;
use App\Services\Customer\ActiveNormasManager;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LegalUpdateController extends Controller
{
    /**
     * Get the common items.
     *
     * @param Request $request
     *
     * @return array<int, mixed>
     */
    protected function prepare(Request $request): array
    {
        /** @var User */
        $user = Auth::user();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        $norma = $organisation = null;
        if ($manager->isSingleMode()) {
            /** @var Norma */
            $norma = $manager->getActive();
            $query = LegalUpdate::forNorma($norma);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $query = LegalUpdate::forOrganisationUserAccess($organisation, $user)
                ->withCount([
                    'normas' => fn ($q) => $q->forOrganisation($organisation->id)->userHasAccess($user),
                ]);
        }

        if ($request->has('search')) {
            event(new LegalUpdatesSearched($user, $norma, $organisation));
        }

        $filters = $request->only(['domains', 'status', 'from', 'to', 'bookmarked']);

        if (!empty($filters)) {
            event(new LegalUpdatesFiltered($filters, $user, $norma, $organisation));
        }

        /** @var array<mixed> */
        $filters = $request->all();
        $query->filter($filters)
            ->where('status', LegalUpdatePublishedStatus::PUBLISHED->value)
            ->orderByRaw('COALESCE(release_at,created_at) DESC')
            ->with([
                'work',
                'primaryLocation.ancestorsWithSelf:title,id',
                'legalDomains',
                'users' => function ($q) use ($user) {
                    $q->whereKey($user->id);
                },
            ]);

        return [$norma, $organisation, $filters, $query];
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        [$norma, $organisation, $filters, $query] = $this->prepare($request);

        /** @var User $user */
        $user = $request->user();

        $query->with(['bookmarks' => fn ($q) => $q->forUser($user)]);

        /** @var View */
        return view('pages.notify.legal-update.my.index', [
            'updates' => $query->paginate(15)->appends($request->query() ?? []),
            'norma' => $norma ?? null,
            'organisation' => $organisation ?? null,
            'query' => $query,
            'filters' => $filters,
            'subTitle' => !is_null($norma) ? $norma->title : $organisation->title,
        ]);
    }

    /**
     * @param Request     $request
     * @param LegalUpdate $update
     *
     * @return View
     */
    public function show(Request $request, LegalUpdate $update): View
    {
        $update->load(['bookmarks']);

        /** @var User */
        $user = Auth::user();
        UpdateReadUnderstoodStatus::run($update, $user, LegalUpdateStatus::read());
        [$norma, $organisation, $filters, $query] = $this->prepare($request);

        $next = $query->whereHas('users', function ($builder) use ($user) {
            $builder->whereKey($user->id)->where((new LegalUpdateUser())->qualifyColumn('read_status'), false);
        })->first();

        /** @var View */
        return view('pages.notify.legal-update.my.show', [
            'update' => $update,
            'next' => $next,
            'organisation' => app(ActiveNormasManager::class)->getActiveOrganisation(),
            'canMarkAsUnderstood' => $update->users()->whereKey($user->id)->wherePivot('understood_status', false)->exists(),
        ]);
    }

    /**
     * @param LegalUpdate $update
     *
     * @return RedirectResponse
     */
    public function markAsUnderstood(LegalUpdate $update): RedirectResponse
    {
        /** @var User */
        $user = Auth::user();
        UpdateReadUnderstoodStatus::run($update, $user, LegalUpdateStatus::readUnderstood());

        return back();
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAsExcel(Request $request): Response
    {
        $filename = Str::random(15) . '.xlsx';
        $filters = $request->all();
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $norma = $organisation = null;
        $manager = app(ActiveNormasManager::class);
        $organisation = $manager->getActiveOrganisation();

        if ($manager->isSingleMode()) {
            /** @var Norma $norma */
            $norma = $manager->getActive();
            $job = new GenerateLegalUpdateExportExcel($filename, $user, $norma, $organisation, $filters);
        } else {
            $job = new GenerateLegalUpdateExportExcel($filename, $user, null, $organisation, $filters);
        }

        $this->dispatch($job);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.legal-updates.excel', ['filename' => $filename], false),
        ]);

        event(new LegalUpdatesExported('excel', $user, $norma, $organisation));

        return turboStreamResponse($view);
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function exportAsPDF(Request $request): Response
    {
        $filename = Str::random(15) . '.pdf';
        $filters = $request->all();

        /** @var ActiveNormasManager */
        $manager = app(ActiveNormasManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $norma = null;

        if ($manager->isSingleMode()) {
            /** @var Norma $norma */
            $norma = $manager->getActive();
            $filename = "l---{$norma->id}---{$filename}";
            $job = new GenerateLegalUpdateExportPDF($filename, $user, $norma, $organisation, $filters);
        } else {
            $filename = "o---{$organisation->id}---{$filename}";
            $job = new GenerateLegalUpdateExportPDF($filename, $user, null, $organisation, $filters);
        }

        $this->dispatch($job);

        /** @var View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => 'download-progress',
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.legal-updates.pdf', ['filename' => $filename], false),
        ]);

        event(new LegalUpdatesExported('pdf', $user, $norma, $organisation));

        return turboStreamResponse($view);
    }

    public function preview(LegalUpdate $update): View
    {
        $update->load(['source', 'createdFromDoc.docMeta']);

        event(new GenericActivityUsingAuth(UserActivityType::viewedUpdateDocument(), ['id' => $update->id]));

        /** @var View */
        return view('pages.notify.legal-update.my.preview', ['update' => $update]);
    }
}
