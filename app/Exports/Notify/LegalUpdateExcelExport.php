<?php

namespace App\Exports\Notify;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Traits\CleansHtmlForExcel;
use App\Exports\Traits\SetsUpLibryoOrgExport;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Notify\LegalUpdate;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\Traits\Comments\ExportsComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class LegalUpdateExcelExport implements LibryoOrganisationExport
{
    use CleansHtmlForExcel;
    use UsesBookmarksTableFilter;
    use ExportsComments;
    use SetsUpLibryoOrgExport;

    public const TITLE_COL = 'A';

    /**
     * {@inheritDoc}
     */
    protected function getExportName(): string
    {
        /** @var string */
        return __('notify.legal_update.legal_update_history');
    }

    /**
     * @param Libryo                            $libryo
     * @param \App\Models\Customer\Organisation $organisation
     * @param User                              $user
     * @param array<string, mixed>              $filters
     * @param callable|null                     $progressCallback
     *
     * @return Spreadsheet
     */
    public function forLibryo(Libryo $libryo, Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $this->setupExport($user, $organisation, $libryo, $progressCallback);
        $filters = $this->updateBookmarkFilters($filters, $user);
        $libryo->setRelation('organisation', $organisation);

        $query = LegalUpdate::forLibryo($libryo);
        if (isset($filters['status'])) {
            $query->userStatusFromString($user, $filters['status']);
            unset($filters['status']);
        }
        $query->filter($filters)
            ->with($this->eagerCommentsForLibryo($libryo))
            ->orderByRaw('COALESCE(release_at,created_at) DESC');

        return $this->build($query);
    }

    /**
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $filters
     * @param callable|null        $progressCallback
     *
     * @return Spreadsheet
     */
    public function forOrganisation(Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $this->setupExport($user, $organisation, null, $progressCallback);
        $filters = $this->updateBookmarkFilters($filters, $user);

        /** @var Builder $query */
        $query = LegalUpdate::forOrganisationUserAccess($organisation, $user);
        if (isset($filters['status'])) {
            $query->userStatusFromString($user, $filters['status']);
            unset($filters['status']);
        }
        $query->filter($filters)
            ->with($this->eagerCommentsForOrganisation($organisation, $user))
            ->orderByRaw('COALESCE(release_at,created_at) DESC');

        return $this->build($query);
    }

    /**
     * @param Builder $query
     *
     * @return Spreadsheet
     */
    public function build(Builder $query): Spreadsheet
    {
        $this->progress = 0;
        $this->progressTotal = $query->count();

        $query->with([
            'work',
            'primaryLocation',
            'primaryLocation.country',
            'primaryLocation.ancestors',
            'legalDomains',
        ])->chunk(25, function ($updates) {
            /** @var LegalUpdate $update */
            foreach ($updates as $row => $update) {
                $update = $this->addJurisdictions($update);

                $this->writeLegalUpdateItem($update, $this->progress);
                $this->incrementProgress();
            }
        });

        $ws = $this->excel->getActiveSheet();

        $ws->setCellValue('A1', __('interface.title'));
        $ws->setCellValue('B1', __('notify.legal_update.jurisdiction'));
        $ws->setCellValue('C1', __('notify.legal_update.highlights'));
        $ws->setCellValue('D1', __('notify.legal_update.effective_date'));
        $ws->setCellValue('E1', __('notify.legal_update.date_notified'));
        $ws->setCellValue('F1', __('comments.comments_added'));
        $ws->getStyle('A1:G1')->getFont()->setBold(true);

        $ws->getStyle(static::TITLE_COL . '1:I500')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $ws->getDefaultColumnDimension()->setWidth(16);
        $ws->getColumnDimension(static::TITLE_COL)->setWidth(40);
        $ws->getColumnDimension('B')->setWidth(40);
        $ws->getColumnDimension('D')->setWidth(80);
        $ws->getColumnDimension('E')->setWidth(80);

        return $this->excel;
    }

    /**
     * Writes an update to the next row in the spreadsheet.
     *
     * @param LegalUpdate $update
     * @param int         $row
     *
     * @return void
     */
    protected function writeLegalUpdateItem(LegalUpdate $update, int $row): void
    {
        $row = $row + 2; // Row to insert content at.
        $ws = $this->excel->getActiveSheet();

        $highlights = !empty($update->highlights) ? $this->parseHTML($update->highlights) : '';

        $ws->setCellValue(static::TITLE_COL . $row, $update->title);
        $ws->setCellValue('B' . $row, $update->primaryLocation->jurisdictions ?? '');
        $ws->setCellValue('C' . $row, $highlights);
        $ws->setCellValue('D' . $row, $update->effective_date ? Carbon::parse($update->effective_date)->format('d M Y') : '');
        $ws->setCellValue('E' . $row, $update->release_at ? Carbon::parse($update->release_at)->format('d M Y') : '');

        $this->writeComments($update->comments, $ws, static::TITLE_COL . $row, "F{$row}");
    }

    /**
     * Add Jurisdictions to the legal update.
     *
     * @param LegalUpdate $update
     *
     * @return LegalUpdate
     */
    protected function addJurisdictions(LegalUpdate $update): LegalUpdate
    {
        $jurisdictions = collect();
        $location = $update->primaryLocation;
        if (!$location) {
            // @codeCoverageIgnoreStart
            return $update;
            // @codeCoverageIgnoreEnd
        }

        if ($location->level == 1) {
            $jurisdictions->push($location->title);
            $location['jurisdictions'] = $jurisdictions->join("\n");
        }
        $ancestors = $location->ancestors
            ->reverse()
            ->push($location)
            ->map(fn ($ancestor) => $ancestor->title)
            ->join(' > ');

        $jurisdictions->push($ancestors);
        $location['jurisdictions'] = $jurisdictions->unique('id')->join("\n");

        return $update;
    }
}
