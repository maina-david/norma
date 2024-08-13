<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Notify\LegalUpdateExcelExport;
use App\Mail\Notify\DailyNotification;
use App\Models\Notify\LegalUpdate;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Support\Facades\Storage;
use ReflectionException;

class GenerateLegalUpdateExportPDF extends LibryoAndOrganisationExport
{
    use UsesBookmarksTableFilter;

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     */
    protected function getExporter(): LibryoOrganisationExport
    {
        return app(LegalUpdateExcelExport::class);
    }

    /**
     * @throws ReflectionException
     *
     * @return void
     */
    public function handle(): void
    {
        $this->filters = $this->updateBookmarkFilters($this->filters, $this->user);

        $query = is_null($this->libryo)
            ? LegalUpdate::forOrganisationUserAccess($this->organisation, $this->user)
            : LegalUpdate::forLibryo($this->libryo);

        if (isset($this->filters['status'])) {
            // @codeCoverageIgnoreStart
            $query->userStatusFromString($this->user, $this->filters['status']);
            unset($this->filters['status']);
            // @codeCoverageIgnoreEnd
        }

        $notifications = $query->filter($this->filters)->orderByRaw('COALESCE(release_at,created_at) DESC')
            ->pluck((new LegalUpdate())->qualifyColumn('id'))
            ->toArray();

        $this->setProgressNow(1);
        $content = (new DailyNotification($this->user, $notifications))->render();

        $pdf = SnappyPdf::loadHTML($content);

        $this->setProgressNow(90);

        $pdf->setPaper('a4');

        $path = config('filesystems.paths.temp') . DIRECTORY_SEPARATOR . $this->tempFileName;

        Storage::put($path, $pdf->output());

        $this->setProgressNow(100);
    }
}
