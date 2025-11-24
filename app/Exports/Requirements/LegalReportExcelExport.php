<?php

namespace App\Exports\Requirements;

use App\Cache\Ontology\Collaborate\CategorySelectorCache;
use App\Contracts\Exports\NormaOrganisationExport;
use App\Exports\Traits\CleansHtmlForExcel;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Pivots\CategoryReference;
use App\Models\Requirements\Summary;
use App\Traits\Bookmarks\UsesBookmarksTableFilter;
use App\Traits\CleansWorksheetTitle;
use App\Traits\Comments\ExportsComments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Protection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LegalReportExcelExport implements NormaOrganisationExport
{
    use CleansHtmlForExcel;
    use UsesBookmarksTableFilter;
    use ExportsComments;
    use CleansWorksheetTitle;

    public const TITLE_COL = 'A';
    public const CONTENT_COL = 'B';

    /** @var callable|null */
    protected $progressCallback;

    /** @var int */
    protected int $progress = 0;

    /** @var Spreadsheet */
    protected $excel;

    /** @var Norma|null */
    protected ?Norma $norma = null;

    /** @var Organisation|null */
    protected ?Organisation $organisation = null;

    /** @var User|null */
    protected ?User $user = null;
    /** @var string|null */
    protected ?string $domain = null;

    /**
     * Set the domain to be used for the routes.
     *
     * @param string|null $domain
     *
     * @return $this
     */
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    // /**
    //  * @param Norma               $norma
    //  * @param array<string, mixed> $filters
    //  * @param callable|null|null   $progressCallback
    //  *
    //  * @return Spreadsheet
    //  */
    // public function forNorma(Norma $norma, User $user, array $filters = [], callable|null $progressCallback = null): Spreadsheet
    // {
    //     /** @var Builder */
    //     $query = Work::primaryForNorma($norma, $filters);
    //     $query->withRelationsForNorma($norma, true, $filters);

    //     return $this->build($query, $progressCallback);
    // }

    // /**
    //  * @param Organisation         $organisation
    //  * @param User                 $user
    //  * @param array<string, mixed> $filters
    //  * @param callable|null|null   $progressCallback
    //  *
    //  * @return Spreadsheet
    //  */
    // public function forOrganisation(Organisation $organisation, User $user, array $filters = [], callable|null $progressCallback = null): Spreadsheet
    // {
    //     /** @var Builder */
    //     $query = Work::primaryForOrganisation($organisation, $user, $filters);
    //     $query->withRelationsForOrganisation($organisation, $user, true, $filters);

    //     return $this->build($query, $progressCallback);
    // }

    /**
     * NB: This will replace the forLibyo method. Remove the @codeCoverageIgnore when doing so.
     *
     * @codeCoverageIgnore
     *
     * @param Norma                            $norma
     * @param \App\Models\Customer\Organisation $organisation
     * @param \App\Models\Auth\User             $user
     * @param array<string, mixed>              $filters
     * @param callable|null                     $progressCallback
     *
     * @return Spreadsheet
     */
    public function forNorma(Norma $norma, Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $this->norma = $norma;
        $this->organisation = $organisation;
        $this->user = $user;
        $filters = $this->updateBookmarkFilters($filters, $user);
        $search = $filters['search'] ?? '';

        /** @var Builder $query */
        $query = Work::forRequirements(Arr::except($filters, ['search']), $search, $user, $norma, null, true);

        return $this->build($query, $progressCallback);
    }

    /**
     * NB: This will replace the forOrganisation method. Remove the @codeCoverageIgnore when doing so.
     *
     * @codeCoverageIgnore
     *
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $filters
     * @param callable|null|null   $progressCallback
     *
     * @return Spreadsheet
     */
    public function forOrganisation(Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $this->organisation = $organisation;
        $this->user = $user;
        $filters = $this->updateBookmarkFilters($filters, $user);
        $search = $filters['search'] ?? '';

        /** @var Builder */
        $query = Work::forRequirements(Arr::except($filters, ['search']), $search, $user, null, $organisation, true);

        return $this->build($query, $progressCallback);
    }

    /**
     * @param Builder  $query
     * @param callable $progressCallback
     *
     * @return Spreadsheet
     */
    public function build(
        Builder $query,
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->progressCallback = $progressCallback;
        $this->setup();

        $progressTotal = $query->count();
        $this->progress = 0;

        $query->chunk(2, function ($works) use ($progressTotal) {
            foreach ($works as $work) {
                /** @var Work $work */
                $this->writeWork($work);
                $this->progress++;
                $percentage = round(($this->progress / $progressTotal) * 100);
                $percentage = $percentage > 99 ? 99 : $percentage;
                if (!is_null($this->progressCallback)) {
                    call_user_func_array($this->progressCallback, [$percentage]);
                }
            }
        });

        $toc = $this->excel->getSheet(0);
        $toc->setTitle('Table of Contents');
        $toc->getColumnDimension('A')->setAutoSize(true);
        $toc->getStyle('A')->getFont()
            ->setUnderline(true)
            ->setColor(new Color(Color::COLOR_BLUE));

        $toc->setProtection((new Protection())->setSheet(true));

        $this->excel->setActiveSheetIndex(0);

        return $this->excel;
    }

    /**
     * Set up the pages for the Excel file.
     *
     * @return void
     */
    protected function setup(): void
    {
        $this->excel = new Spreadsheet();

        /** @var string */
        $requirementsTxt = __('requirements.requirements');
        $this->excel->getProperties()
            ->setCreator(config('app.name'))
            ->setLastModifiedBy(config('app.name'))
            ->setTitle($requirementsTxt)
            ->setSubject($requirementsTxt)
            ->setKeywords($requirementsTxt . ' ' . config('app.name'));
    }

    /**
     * Writes a work and child works to a sheet.
     *
     * @param Work $work
     *
     * @return void
     */
    protected function writeWork(Work $work): void
    {
        $index = $this->excel->getSheetCount();
        /** @var string $sheetTitle */
        $sheetTitle = preg_replace('/[\p{P}$]+/u', '', $work->title ?? '');
        $sheetTitle = "{$index}.{$sheetTitle}";
        $sheetTitle = mb_substr($sheetTitle, 0, 29);
        //        $sheetTitle = $index . '.';
        $ws = new Worksheet($this->excel, $sheetTitle);
        $this->excel->addSheet($ws);
        $ws->setCellValue('A1', $work->title);
        $ws->setCellValue('C1', __('requirements.summary.notes'));
        $ws->setCellValue('D1', __('nav.topics'));
        $ws->setCellValue('E1', __('corpus.doc.url'));
        $ws->setCellValue('F1', __('comments.comments_added'));
        $ws->getStyle('A1:F1')->getFont()->setBold(true);
        $ws->mergeCells('A1:B1');
        $row = 2;

        $refIds = [];
        foreach ($work->references as $reference) {
            $refIds[$row] = $reference->id;
            $this->writeReference($ws, $row, $reference, $reference->htmlContent?->cached_content ?? '');
            $row++;
        }

        unset($work->references);

        $this->writeSummaryAndCategories($ws, $refIds);

        foreach ($work->children as $child) {
            $ws->setCellValue(static::TITLE_COL . $row, $child->title);
            $ws->getStyle(static::TITLE_COL . $row)->getFont()->setBold(true);
            $ws->mergeCells(static::TITLE_COL . $row . ':' . static::CONTENT_COL . $row);
            $row++;

            $refIds = [];

            foreach ($child->references as $reference) {
                $refIds[$row] = $reference->id;
                $this->writeReference($ws, $row, $reference, $reference->htmlContent?->cached_content ?? '');
                $row++;
            }

            unset($child->references);

            $this->writeSummaryAndCategories($ws, $refIds);
        }

        unset($work->children);

        $ws->getStyle(static::TITLE_COL . '1:F500')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $ws->getColumnDimension(static::TITLE_COL)->setWidth(40);
        $ws->getColumnDimension(static::CONTENT_COL)->setWidth(160);
        $ws->getColumnDimension('C')->setWidth(40);
        $ws->getColumnDimension('D')->setWidth(40);
        $ws->getColumnDimension('E')->setWidth(40);

        $toc = $this->excel->getSheet(0);
        $toc->getCell("A{$index}")
            ->setValue($work->title)
            ->getHyperlink()->setUrl("sheet://'{$sheetTitle}'!A1");
    }

    /**
     * Write the summary and categories.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws
     * @param array<int, int>                               $referenceIds
     *
     * @return void
     */
    protected function writeSummaryAndCategories(Worksheet $ws, array $referenceIds): void
    {
        $summaries = Summary::whereIn('reference_id', array_values($referenceIds))
            ->get(['reference_id', 'summary_body'])
            ->keyBy('reference_id');
        $categories = CategoryReference::whereIn('reference_id', array_values($referenceIds))
            ->whereHas('category', fn ($query) => $query->forTagging())
            ->with('category:id,display_label,category_type_id')
            ->get()
            ->groupBy('reference_id')
            ->map(fn ($group) => $group->reduce(fn ($carry, $item) => $carry->merge([$item->category]), collect()));

        foreach ($referenceIds as $row => $refId) {
            $refCategories = $categories->get($refId) ?? collect();

            $ws->setCellValue("C{$row}", strip_tags($summaries->get($refId)?->summary_body ?? ''));
            $ws->setCellValue("D{$row}", CategorySelectorCache::updateLabel($refCategories)->pluck('display_label')->join(', '));
        }
    }

    /**
     * Writes an item to a row in the excel sheet.
     *
     * @param Reference $reference
     *
     * @return void
     */
    protected function writeReference(
        Worksheet $ws,
        int &$row,
        Reference $reference,
        string $content
    ): void {
        $url = route('my.corpus.references.show', ['reference' => $reference->id]);

        $ws->setCellValue(static::TITLE_COL . $row, $reference->refPlainText?->plain_text ?? '');
        $ws->getCell(static::TITLE_COL . $row)->getHyperlink()->setUrl($url);

        $ws->setCellValue("E{$row}", $url);

        if ($this->norma) {
            // @codeCoverageIgnoreStart
            $reference->load($this->eagerCommentsForNorma($this->norma));
            $this->writeComments($reference->comments, $ws, static::TITLE_COL . $row, "F{$row}");
            // @codeCoverageIgnoreEnd
        }

        if ($content !== '') {
            $moreRows = $this->writeContent($ws, $content, $row);

            $row += $moreRows;
        }
    }

    /**
     * Write the register content splitting it into cells to fit the maximum possible visible content.
     *
     * @param Worksheet $workSheet
     * @param string    $content
     * @param int       $row
     *
     * @return int
     */
    protected function writeContent(Worksheet $workSheet, string $content, int $row): int
    {
        $content = $this->parseHTML($content);
        $contentLength = strlen($content);
        $cellLimit = 3500;

        if ($contentLength <= $cellLimit) {
            // have to prefix with space to prevent content starting with = being interpreted as a formula
            $workSheet->setCellValue(static::CONTENT_COL . $row, ' ' . $content);

            return 0;
        }

        $chunks = explode('*(^*', wordwrap($content, $cellLimit, '*(^*'));

        $requiredRows = count($chunks) - 1;

        $workSheet->mergeCells(static::TITLE_COL . $row . ':' . static::TITLE_COL . ($row + $requiredRows));

        foreach ($chunks as $chunk) {
            // have to prefix with space to prevent content starting with = being interpreted as a formula
            $workSheet->setCellValue(static::CONTENT_COL . $row, ' ' . $chunk);
            $row++;
        }

        return $requiredRows;
    }
}
