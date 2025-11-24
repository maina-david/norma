<?php

namespace App\Exports\Actions;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Enums\Tasks\TaskStatus;
use App\Exports\Traits\SetsUpNormaOrgExport;
use App\Models\Actions\ActionArea;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceText;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Models\Ontology\Category;
use App\Models\Ontology\Pivots\CategoryClosure;
use App\Models\Tasks\TaskActivity;
use App\Traits\Comments\ExportsComments;
use App\Traits\UsesReferencesForNorma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ActionsPlannerExport implements NormaOrganisationExport
{
    use UsesReferencesForNorma;
    use ExportsComments;
    use SetsUpNormaOrgExport;

    /** @var string */
    protected string $groupBy = 'subject';

    /** @var bool */
    protected bool $includeTasks = true;

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

    /**
     * @return self
     */
    public function usingControl(): self
    {
        $this->groupBy = 'control';

        return $this;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function forNorma(Norma $norma, Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $this->setupExport($user, $organisation, $norma, $progressCallback);

        $this->build($filters);

        return $this->excel;
    }

    /**
     * {@inheritDoc}
     */
    public function forOrganisation(Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet
    {
        $this->setupExport($user, $organisation, null, $progressCallback);

        $this->build($filters);

        return $this->excel;
    }

    /**
     * Get the export name to be used.
     *
     * @return string
     */
    protected function getExportName(): string
    {
        $suffix = $this->groupBy === 'subject' ? __('nav.topics') : __('actions.action_area.control_types');

        return sprintf('%s - %s', __('actions.action_area.actions_planner'), $suffix);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return void
     */
    protected function build(array $filters): void
    {
        $this->includeTasks = ($filters['included_tasks'] ?? 'all') !== 'none';
        $this->domain = $this->domain ?? route('my.dashboard');

        $actions = $this->getActions($filters);

        $index = $this->excel->getSheetCount();
        $ws = new Worksheet($this->excel);
        $this->excel->addSheet($ws);
        $this->excel->setActiveSheetIndex($index);

        $ws->setCellValue('A1', __('actions.action_area.parent_topic'));
        $ws->setCellValue('B1', __('actions.action_area.action_area'));
        $ws->setCellValue('C1', __('tasks.task.views.topic'));
        $ws->setCellValue('D1', __('actions.action_area.control'));
        $ws->setCellValue('E1', __('corpus.reference.linked_requirement'));

        if ($this->includeTasks) {
            $ws->setCellValue('F1', __('actions.action_area.tasks'));
            $ws->setCellValue('G1', __('tasks.impact'));
            $ws->setCellValue('H1', __('assess.assessment_item.description'));
            $ws->setCellValue('I1', __('auth.user.status'));
            $ws->setCellValue('J1', __('tasks.due_date'));
            $ws->setCellValue('K1', __('tasks.last_status_change'));
            $ws->setCellValue('L1', __('tasks.changed_by'));
            $ws->setCellValue('M1', __('auth.user.notifications.assignee'));
            $ws->setCellValue('N1', __('tasks.followers'));
            $ws->setCellValue('O1', __('comments.comments_added'));
        }

        $this->progress = 0;
        $this->progressTotal = $actions->count();

        $actions = $this->groupActionAreas($actions);

        $this->rowNumber = 2;

        foreach ($actions as $group) {
            $this->writeActionGroup($group);
        }

        $ws->getStyle('A1:O3000')->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
        $ws->getDefaultColumnDimension()->setWidth(40);
        $ws->getStyle('A1:Z1')->getFont()->setBold(true);

        $this->excel->removeSheetByIndex(0);
        $this->excel->setActiveSheetIndex(0);
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getActions(array $filters): Collection
    {
        $usableFilters = $filters;
        unset($usableFilters['included_tasks']);

        $references = $this->getReferenceSubQuery($this->norma, $this->organisation);
        $orgSubQuery = Norma::select(['id'])->where('organisation_id', $this->organisation->id);

        return ActionArea::join(get_table(CategoryClosure::class, 'subject_closure'), 'subject_category_id', 'subject_closure.descendant')
            ->join(get_table(CategoryClosure::class, 'control_closure'), 'control_category_id', 'control_closure.descendant')
            ->join(get_table(Category::class, 'subject'), 'subject_closure.ancestor', 'subject.id')
            ->join(get_table(Category::class, 'control'), 'control_closure.ancestor', 'control.id')
            ->where('subject.level', 1)
            ->where('control.level', 2)
            ->whereHas('references', fn ($query) => $query->whereIn('id', $references))
            ->select([
                qualify_column(ActionArea::class, 'id'),
                qualify_column(ActionArea::class, 'title'),
                'subject.display_label as subject_label',
                'control.display_label as control_label',
            ])
            ->when(!empty($usableFilters), fn ($q) => $q->whereHas('tasks', fn ($query) => $query->forNormaOrOrganisation($this->norma, $this->organisation)->filter($usableFilters)))
            ->when($this->includeTasks, function ($builder) use ($filters, $orgSubQuery) {
                $builder->with([
                    'tasks' => fn ($query) => $query
                        ->forNormaOrOrganisation($this->norma, $this->organisation)
                        ->whereIn('place_id', $this->norma ? [$this->norma->id] : $orgSubQuery)
                        ->when(($filters['included_tasks'] ?? '') === 'by_users', fn ($q) => $q->whereNull('reference_content_extract_id')),
                    'tasks.assignee' => fn ($query) => $query->select(['id', 'fname', 'sname']),
                    'tasks.watchers' => fn ($query) => $query->select(['id', 'fname', 'sname']),
                    'tasks.latestStatusActivity' => fn ($query) => $query->select(['id', 'user_id', 'created_at', qualify_column(TaskActivity::class, 'task_id')]),
                    'tasks.latestStatusActivity.user' => fn ($query) => $query->select(['id', 'fname', 'sname']),
                    'tasks.comments',
                ]);
            })
            ->with([
                'references' => fn ($query) => $this->applyReferenceFilter($query, $references, $filters),
            ])
            ->get();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation $builder
     * @param \Illuminate\Database\Eloquent\Builder                                                  $placeRefs
     * @param array<string, mixed>                                                                   $filters
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation
     */
    protected function applyReferenceFilter(Builder|Relation $builder, Builder $placeRefs, array $filters): Builder|Relation
    {
        return $builder->whereIn(qualify_column(Reference::class, 'id'), $placeRefs)
            ->join(get_table(ReferenceText::class), qualify_column(ReferenceText::class, 'reference_id'), qualify_column(Reference::class, 'id'))
            ->select([
                qualify_column(Reference::class, 'id'),
                sprintf('%s as title', (new ReferenceText())->qualifyColumn('plain_text')),
            ])
            ->filter($filters);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Collection $actions
     *
     * @return array<int, mixed>
     */
    protected function groupActionAreas(Collection $actions): array
    {
        $grouped = [];

        foreach ($actions as $action) {
            $field = "{$this->groupBy}_label";
            $groupKey = $action->{$field};
            $grouped[$groupKey] = $grouped[$groupKey] ?? ['title' => $groupKey, 'children' => collect()];
            $grouped[$groupKey]['children']->push($action);
        }

        return collect(array_values($grouped))->sortBy('title')->values()->all();
    }

    /**
     * @param array<string, mixed> $group
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return void
     */
    protected function writeActionGroup(array $group): void
    {
        $sheet = $this->excel->getActiveSheet();

        $sheet->getStyle("A{$this->rowNumber}:O{$this->rowNumber}")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setRGB('E7E5E4');

        $sheet->setCellValue("A{$this->rowNumber}", $group['title']);

        $group['children']->sortBy('title')
            ->each(function ($child) use ($sheet) {
                $this->writeActionArea($sheet, $child);
                $this->incrementProgress();
            });
    }

    /**
     * Write the given action area.
     *
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     * @param \App\Models\Actions\ActionArea                $area
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return void
     */
    protected function writeActionArea(Worksheet $sheet, ActionArea $area): void
    {
        $sheet->setCellValue("B{$this->rowNumber}", $area->title);
        $sheet->setCellValue("C{$this->rowNumber}", $area->subject_label); // @phpstan-ignore-line
        $sheet->setCellValue("D{$this->rowNumber}", $area->control_label); // @phpstan-ignore-line

        $this->rowNumber++;

        $this->writeRequirements($sheet, $area);
    }

    /**
     * @param \App\Models\Actions\ActionArea $area
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getActionTasks(ActionArea $area): Collection
    {
        return $this->includeTasks && $area->relationLoaded('tasks') ? $area->tasks : new Collection();
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    protected function writeRequirements(Worksheet $sheet, ActionArea $area): void
    {
        $tasks = $this->getActionTasks($area)->where('taskable_type', (new Reference())->getMorphClass())->groupBy('taskable_id');

        foreach ($area->references as $reference) {
            $sheet->setCellValue("E{$this->rowNumber}", $reference->title); // @phpstan-ignore-line
            $sheet->getHyperlink("E{$this->rowNumber}")->setUrl($this->domain . route('my.corpus.references.show', ['reference' => $reference->id], false));
            $sheet->getStyle("E{$this->rowNumber}")->getFont()->setUnderline(true);
            $sheet->getStyle("E{$this->rowNumber}")->getFont()->getColor()->setARGB(Color::COLOR_BLUE);

            /** @var Collection $refTasks */
            $refTasks = $tasks->get($reference->id) ?? collect();

            foreach ($refTasks as $refTask) {
                /** @var \App\Models\Tasks\Task $refTask */
                $sheet->setCellValue("F{$this->rowNumber}", $refTask->title);
                $sheet->getHyperlink("F{$this->rowNumber}")->setUrl($this->domain . route('my.actions.tasks.show', ['task' => $refTask->hash_id], false));
                $sheet->getStyle("F{$this->rowNumber}")->getFont()->setUnderline(true);
                $sheet->getStyle("F{$this->rowNumber}")->getFont()->getColor()->setARGB(Color::COLOR_BLUE);

                $sheet->setCellValue("G{$this->rowNumber}", $refTask->impact ? "{$refTask->impact}/10" : '');
                $sheet->setCellValue("H{$this->rowNumber}", html_to_text($refTask->description ?? ''));
                $sheet->setCellValue("I{$this->rowNumber}", TaskStatus::fromValue($refTask->task_status)->label());
                $sheet->setCellValue("J{$this->rowNumber}", $refTask->due_on?->format('d F Y') ?? '');
                $sheet->setCellValue("K{$this->rowNumber}", $refTask->latestStatusActivity?->created_at?->format('d F Y') ?? '');
                $sheet->setCellValue("L{$this->rowNumber}", $refTask->latestStatusActivity->user->full_name ?? '');
                $sheet->setCellValue("M{$this->rowNumber}", $refTask->assignee->full_name ?? '');
                $sheet->setCellValue("N{$this->rowNumber}", $refTask->watchers->map(fn ($item) => $item->full_name)->join(', '));
                $this->writeComments($refTask->comments, $sheet, "O{$this->rowNumber}", "O{$this->rowNumber}");
                $this->rowNumber++;
            }

            if ($refTasks->isEmpty()) {
                $this->rowNumber++;
            }
        }
    }
}
