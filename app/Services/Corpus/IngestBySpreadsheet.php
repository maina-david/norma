<?php

namespace App\Services\Corpus;

use App\Actions\Corpus\Work\CreateFromDoc;
use App\Enums\Corpus\ReferenceStatus;
use App\Enums\Corpus\ReferenceType;
use App\Enums\Requirements\RefRequirementChangeStatus;
use App\Models\Actions\ActionArea;
use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use App\Models\Ontology\Category;
use App\Models\Ontology\LegalDomain;
use App\Models\Ontology\WorkType;
use App\Models\Requirements\ReferenceRequirementDraft;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use RuntimeException;
use Throwable;

class IngestBySpreadsheet
{
    /** @var array<string, mixed> */
    protected array $report = [];

    /** @var array<string, int> */
    protected array $subjectCache = [];

    /** @var array<string, int> */
    protected array $controlCache = [];

    /** @var array<string, int> */
    protected array $actionAreaCache = [];

    /** @var array<string, int> */
    protected array $contextCache = [];

    /** @var array<string, int> */
    protected array $domainCache = [];

    public const COLUMN_WORK_ID = 1;
    public const COLUMN_WORK_TITLE = 2;
    public const COLUMN_WORK_URL = 3;
    public const COLUMN_WORK_TYPE = 4;
    public const COLUMN_REQ_TITLE = 6;
    public const COLUMN_REQ_TEXT = 7;
    public const COLUMN_HAS_REQS = 8;
    public const COLUMN_SUBJECT = 9;
    public const COLUMN_CONTROL = 10;
    public const COLUMN_CONTEXT = 11;
    public const COLUMN_DOMAINS = 12;

    public function __construct(
        protected CreateFromDoc $createWorkFromDoc,
    ) {
    }

    /**
     * @param Spreadsheet $spreadsheet
     * @param int         $sourceId
     * @param int         $locationId
     * @param string      $languageCode
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return array<string, mixed>
     */
    public function importFromExcel(
        Spreadsheet $spreadsheet,
        int $sourceId,
        int $locationId,
        string $languageCode
    ): array {
        $worksheet = $spreadsheet->getSheetByNameOrThrow('Entry');

        $this->report = [
            'works_created' => [],
            'missing_action_areas' => [],
        ];

        $lastWork = null;
        $lastReference = null;
        $referenceIndex = 1;

        foreach ($worksheet->getRowIterator(2) as $row) {
            $cellIterator = $row->getCellIterator();

            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }

            $workTitle = trim($cells[static::COLUMN_WORK_TITLE]);
            $workID = trim($cells[static::COLUMN_WORK_ID]);
            if (!empty($workTitle) || !empty($workID)) { // Column C: Work title
                $createWork = true;
                if (!empty($workID)) {
                    $lastWork = Work::with(['activeExpression'])->find((int) $workID);
                    if ($lastWork) {
                        $referenceIndex = $lastWork->references()->max('position') + 1;
                        $createWork = false;
                    }
                }

                // if no ID specified, or if for some reason we couldn't find the ID, then we'll create it.
                if ($createWork) {
                    $lastWork = $this->createWork($cells, $sourceId, $locationId, $languageCode);
                    $referenceIndex = 1;
                }

                $lastReference = null; // Reset last reference when a new work starts
            }

            $reqTitle = trim($cells[static::COLUMN_REQ_TITLE]);
            if ($lastWork && !empty($reqTitle)) { // Column G: Reference title
                $lastReference = $this->createReference($cells, $lastWork, $referenceIndex);
                $referenceIndex++;
            }

            if ($lastReference) {
                // locations get auto added by inherited - when a work is created, a workReference is created with the primary location of the work
                // when we create the references, they are children of the workReference, and the meta data is associated automatically

                $this->attachRelations($cells, $lastReference);
            }
        }

        return $this->report;
    }

    /**
     * @param array<string> $cells
     * @param int           $sourceId
     * @param int           $locationId
     * @param string        $languageCode
     *
     * @return Work
     */
    protected function createWork(array $cells, int $sourceId, int $locationId, string $languageCode): Work
    {
        $workTypeSlug = $cells[4];
        if (!$workType = WorkType::where('slug', $workTypeSlug)->first()) {
            $workType = WorkType::where('slug', 'act')->first();
        }

        $title = trim(str_replace(["\r\n", "\r", "\n"], ' ', $cells[static::COLUMN_WORK_TITLE] ?? ''));

        $doc = Doc::create([
            'source_id' => $sourceId,
            'title' => $title,
            'primary_location_id' => $locationId,
        ]);

        $doc->docMeta->update([
            'source_url' => $cells[static::COLUMN_WORK_URL] ?: null,
            'work_type_id' => $workType?->id,
            'language_code' => $languageCode,
        ]);

        $work = $this->createWorkFromDoc->handle($doc);
        if (!$work) {
            // @codeCoverageIgnoreStart
            throw new RuntimeException('Could not create work');
            // @codeCoverageIgnoreEnd
        }
        $work->activeExpression?->update(['show_source_document' => 0]);

        $this->report['works_created'][] = $work->id;

        return $work;
    }

    /**
     * @param array<string> $cells
     * @param Work          $work
     * @param int           $index
     *
     * @return Reference
     */
    protected function createReference(array $cells, Work $work, int $index): Reference
    {
        $ref = $work->references()->create([
            'parent_id' => $work->workReference?->id,
            'status' => ReferenceStatus::pending()->value,
            'level' => 1,
            'start' => $index,
            'position' => $index,
            'volume' => 1,
            'type' => ReferenceType::citation()->value,
        ]);
        $ref->work()->associate($work);

        $title = substr(trim(str_replace(["\r\n", "\r", "\n"], ' ', $cells[static::COLUMN_REQ_TITLE] ?? '')), 0, 3000);

        $lines = trim(str_replace(["\r\n", "\r", "\n"], PHP_EOL, $cells[static::COLUMN_REQ_TEXT] ?? ''));
        $lines = explode(PHP_EOL, $lines);
        $htmlContent = '';
        foreach ($lines as $line) {
            $htmlContent .= '<p>' . $line . '</p>';
        }
        ReferenceText::create([
            'reference_id' => $ref->id,
            'plain_text' => $title,
        ]);
        ReferenceContentDraft::create([
            'reference_id' => $ref->id,
            'title' => $title,
            'html_content' => $htmlContent,
        ]);

        $requirementsCell = trim($cells[static::COLUMN_HAS_REQS] ?? '');
        if (!empty($requirementsCell) && strtoupper($requirementsCell) === 'YES') {
            ReferenceRequirementDraft::create([
                'reference_id' => $ref->id,
                'change_status' => RefRequirementChangeStatus::ADD->value,
            ]);
        }

        return $ref;
    }

    /**
     * @param array<string> $cells
     * @param Reference     $reference
     *
     * @return void
     */
    protected function attachRelations(array $cells, Reference $reference): void
    {
        $subjectCell = trim($cells[static::COLUMN_SUBJECT] ?? '');
        $controlCell = trim($cells[static::COLUMN_CONTROL] ?? '');
        // Action Areas
        if (!empty($subjectCell) && !empty($controlCell)) {
            $subject = trim(explode('|', $subjectCell)[0]);
            $control = trim(explode('|', $controlCell)[0]);
            $subjectId = $this->subjectCache[$subject] ?? Category::where('display_label', $subject)->first()?->id;
            $controlId = $this->controlCache[$control] ?? Category::where('display_label', $control)->first()?->id;

            if ($subjectId && $controlId) {
                $this->controlCache[$control] = $controlId;
                $this->subjectCache[$subject] = $subjectId;
                $actionAreaCacheId = $controlId . '_' . $subjectId;

                $actionAreaId = $this->actionAreaCache[$actionAreaCacheId] ?? ActionArea::where('control_category_id', $controlId)
                    ->where('subject_category_id', $subjectId)
                    ->first()?->id;
                if ($actionAreaId) {
                    $this->actionAreaCache[$actionAreaCacheId] = $actionAreaId;
                    // trying out attaching directly. Production might require us to switch back to draft first.
                    // $reference->actionAreaDrafts()->attach($actionAreaId, ['change_status' => MetaChangeStatus::ADD->value]);
                    try {
                        $reference->actionAreas()->attach($actionAreaId);
                        // @codeCoverageIgnoreStart
                    } catch (Throwable $th) {
                    }
                // @codeCoverageIgnoreEnd
                } else {
                    $this->report['missing_action_areas'][] = [
                        'subject' => $subject,
                        'control' => $control,
                        'reference' => $reference->work->title . ': ' . $reference->refPlainText?->plain_text,
                    ];
                }
            }
        }
        $contextCell = trim($cells[static::COLUMN_CONTEXT] ?? '');
        if (!empty($contextCell)) {
            $contextQ = $contextCell;
            $contextQ = str_replace('?', '', trim(explode('|', $contextQ)[0]));
            $contextId = $this->contextCache[$contextQ] ?? ContextQuestion::whereRaw("concat_ws(' ', prefix, predicate, pre_object, object, post_object) = ?", [$contextQ])->first()?->id;

            if ($contextId) {
                $this->contextCache[$contextQ] = $contextId;
                // trying out attaching directly. Production might require us to switch back to draft first.
                // $reference->contextQuestionDrafts()->attach(
                //     $contextId,
                //     ['change_status' => MetaChangeStatus::ADD->value]
                // );
                try {
                    $reference->contextQuestions()->attach($contextId);
                    // @codeCoverageIgnoreStart
                } catch (Throwable $th) {
                }
                // @codeCoverageIgnoreEnd
            }
        }
        $domainCell = trim($cells[static::COLUMN_DOMAINS] ?? '');
        if (!empty($domainCell)) {
            $domain = $domainCell;
            $domain = trim($domain);
            $domainId = $this->domainCache[$domain] ?? LegalDomain::where('title', $domain)->first()?->id;

            if ($domainId) {
                $this->domainCache[$domain] = $domainId;
                // trying out attaching directly. Production might require us to switch back to draft first.
                // $reference->legalDomainDrafts()->attach(
                //     $domainId,
                //     ['change_status' => MetaChangeStatus::ADD->value]
                // );
                try {
                    $reference->legalDomains()->attach($domainId);
                    // @codeCoverageIgnoreStart
                } catch (Throwable $th) {
                }
                // @codeCoverageIgnoreEnd
            }
        }
    }
}
