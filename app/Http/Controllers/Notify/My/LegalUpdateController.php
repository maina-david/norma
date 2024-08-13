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
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Models\Notify\Pivots\LegalUpdateUser;
use App\Services\Customer\ActiveLibryosManager;
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

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        $libryo = $organisation = null;
        if ($manager->isSingleMode()) {
            /** @var Libryo */
            $libryo = $manager->getActive();
            $query = LegalUpdate::forLibryo($libryo);
        } else {
            /** @var Organisation */
            $organisation = $manager->getActiveOrganisation();
            $query = LegalUpdate::forOrganisationUserAccess($organisation, $user)
                ->withCount([
                    'libryos' => fn ($q) => $q->forOrganisation($organisation->id)->userHasAccess($user),
                ]);
        }

        if ($request->has('search')) {
            event(new LegalUpdatesSearched($user, $libryo, $organisation));
        }

        $filters = $request->only(['domains', 'status', 'from', 'to', 'bookmarked']);

        if (!empty($filters)) {
            event(new LegalUpdatesFiltered($filters, $user, $libryo, $organisation));
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

        return [$libryo, $organisation, $filters, $query];
    }

    /**
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        [$libryo, $organisation, $filters, $query] = $this->prepare($request);

        /** @var User $user */
        $user = $request->user();

        $query->with(['bookmarks' => fn ($q) => $q->forUser($user)]);

        /** @var View */
        return view('pages.notify.legal-update.my.index', [
            'updates' => $query->paginate(15)->appends($request->query() ?? []),
            'libryo' => $libryo ?? null,
            'organisation' => $organisation ?? null,
            'query' => $query,
            'filters' => $filters,
            'subTitle' => !is_null($libryo) ? $libryo->title : $organisation->title,
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
        [$libryo, $organisation, $filters, $query] = $this->prepare($request);

        $next = $query->whereHas('users', function ($builder) use ($user) {
            $builder->whereKey($user->id)->where((new LegalUpdateUser())->qualifyColumn('read_status'), false);
        })->first();

        /** @var View */
        return view('pages.notify.legal-update.my.show', [
            'update' => $update,
            'next' => $next,
            'organisation' => app(ActiveLibryosManager::class)->getActiveOrganisation(),
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

        $libryo = $organisation = null;
        $manager = app(ActiveLibryosManager::class);
        $organisation = $manager->getActiveOrganisation();

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $job = new GenerateLegalUpdateExportExcel($filename, $user, $libryo, $organisation, $filters);
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

        event(new LegalUpdatesExported('excel', $user, $libryo, $organisation));

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

        /** @var ActiveLibryosManager */
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation */
        $organisation = $manager->getActiveOrganisation();
        /** @var \App\Models\Auth\User $user */
        $user = $request->user();

        $libryo = null;

        if ($manager->isSingleMode()) {
            /** @var Libryo $libryo */
            $libryo = $manager->getActive();
            $filename = "l---{$libryo->id}---{$filename}";
            $job = new GenerateLegalUpdateExportPDF($filename, $user, $libryo, $organisation, $filters);
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

        event(new LegalUpdatesExported('pdf', $user, $libryo, $organisation));

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
