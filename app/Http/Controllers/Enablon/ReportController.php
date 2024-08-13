<?php

namespace App\Http\Controllers\Enablon;

use App\Enums\Enablon\ExportType;
use App\Exports\Requirements\Enablon\Mappers\CargillRequirementContentMapper;
use App\Exports\Requirements\Enablon\Mappers\CargillRequirementsMapper;
use App\Exports\Requirements\Enablon\Mappers\CargillWorksMapper;
use App\Exports\Tasks\Enablon\Mappers\CargillTasksMapper;
use App\Jobs\Exports\GenerateGenericLibryoOrgCSVExport;
use App\Models\Customer\Organisation;
use App\Services\Customer\ActiveLibryosManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportController
{
    /** @var array<int, string> */
    protected array $variants = [
        'cargill',
    ];

    /**
     * Load the base view.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        return view('pages.enablon.my.reports.index', [
            'variants' => $this->variants,
            'downloadTypes' => ExportType::cases(),
        ]);
    }

    /**
     * @param \Illuminate\Http\Request $request
     * @param string                   $type
     *
     * @throws Exception
     *
     * @return Response
     */
    public function generate(Request $request, string $type): Response
    {
        $type = ExportType::tryFrom($type);

        abort_unless((bool) $type, 404);
        /** @var ExportType $type */

        /** @var class-string|null $mapper */
        $mapper = $this->getMapper($request);
        $filename = $mapper ? app($mapper)->addFileExtension(Str::random(15)) : Str::random(15) . '.csv';

        /** @var \App\Models\Auth\User $user */
        $user = $request->user();
        $manager = app(ActiveLibryosManager::class);
        /** @var Organisation $organisation */
        $organisation = $manager->getActiveOrganisation();
        $libryo = $manager->getActive();

        if ($libryo && $manager->isSingleMode()) {
            $filename = "{$libryo->title}{$filename}";
        }

        $job = new GenerateGenericLibryoOrgCSVExport(
            $filename,
            $user,
            $libryo,
            $organisation,
            $type->export(),
            [],
            $mapper,
        );

        dispatch($job);

        $targetId = $request->get('mapper', $type->value);

        /** @var \Illuminate\Contracts\View\View $view */
        $view = view('streams.single-partial', [
            'partialView' => 'partials.system.file-download-progress',
            'target' => "{$targetId}-download-progress",
            'jobId' => $job->getJobStatusId(),
            'percentage' => 0,
            'redirect' => route('my.downloads.download.enablon.excel', ['type' => $type->value, 'filename' => $filename], false),
        ]);

        return turboStreamResponse($view);
    }

    /**
     * Get the spreadsheet mapper to use.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @codeCoverageIgnore
     *
     * @return string|null
     */
    protected function getMapper(Request $request): ?string
    {
        return match ($request->get('mapper')) {
            ExportType::REQUIREMENTS->mapFor('cargill') => CargillRequirementsMapper::class,
            ExportType::REQUIREMENT_CONTENT->mapFor('cargill') => CargillRequirementContentMapper::class,
            ExportType::REGULATION->mapFor('cargill') => CargillWorksMapper::class,
            ExportType::TASKS->mapFor('cargill') => CargillTasksMapper::class,
            default => null,
        };
    }
}
