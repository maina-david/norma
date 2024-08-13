<?php

namespace App\Exports\Tasks;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Enums\System\LibryoModule;
use App\Exports\Traits\CleansHtmlForExcel;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Tasks\Task;
use App\Traits\Comments\ExportsComments;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TasksExcelExport implements LibryoOrganisationExport
{
    use CleansHtmlForExcel;
    use ExportsComments;

    public const TITLE_COL = 'A';

    /** @var int */
    protected int $progress = 0;

    /** @var Spreadsheet */
    protected $excel;

    protected ?string $domain = null;

    protected ?Libryo $libryo = null;
    protected ?Organisation $organisation = null;
    protected ?User $user = null;

    protected string $module = 'tasks';

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
     * Set the module to be used for the routes.
     *
     * @param string $module
     *
     * @return $this
     */
    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
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
        $this->libryo = $libryo;
        $this->organisation = $organisation;
        /** @var Builder */
        $query = Task::forLibryo($libryo);
        $this->buildQuery($query, $filters);

        return $this->build($query, $progressCallback);
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
        $this->organisation = $organisation;
        $this->user = $user;

        /** @var Builder */
        $query = Task::forOrganisationUserAccess($organisation, $user);
        $this->buildQuery($query, $filters);

        return $this->build($query, $progressCallback);
    }

    /**
     * @param Builder              $query
     * @param array<string, mixed> $filters
     *
     * @return void
     */
    private function buildQuery(Builder $query, array $filters = []): void
    {
        if (count($filters) > 0) {
            $query->filter($filters);
        }
        if (!isset($filters['archived'])) {
            $query->active();
        }
    }

    /**
     * @param Builder       $query
     * @param callable|null $progressCallback
     *
     * @return Spreadsheet
     */
    protected function build(
        Builder $query,
        ?callable $progressCallback = null,
    ): Spreadsheet {
        $this->setUpPage();

        $this->buildReport($query, $progressCallback);

        $this->excel->removeSheetByIndex(0);
        $this->excel->setActiveSheetIndex(0);

        return $this->excel;
    }

    /**
     * @param Builder       $query
     * @param callable|null $progressCallback
     *
     * @return void
     */
    protected function buildReport(Builder $query, ?callable $progressCallback = null)
    {
        $this->domain = $this->domain ?? route('my.dashboard');

        /** @var string */
        $title = __('tasks.task.tasks_export');
        $index = $this->excel->getSheetCount();
        // @phpstan-ignore-next-line
        $sheetTitle = $index . '.' . substr(preg_replace('/[^a-zA-Z0-9 ]+/', '', $title), 0, 25);
        $ws = new Worksheet($this->excel, $sheetTitle);
        $this->excel->addSheet($ws);
        $this->excel->setActiveSheetIndex($index);

        $ws->setCellValue('A1', __('tasks.task_item'));
        $ws->setCellValue('B1', __('tasks.project'));
        $ws->setCellValue('C1', __('interface.description'));
        $ws->setCellValue('D1', __('tasks.due_on'));
        $ws->setCellValue('E1', __('tasks.reminders'));
        $ws->setCellValue('F1', __('tasks.assigned_to'));
        $ws->setCellValue('G1', __('tasks.created_by'));
        $ws->setCellValue('H1', __('tasks.current_status'));
        $ws->setCellValue('I1', __('tasks.completed_on'));
        $ws->setCellValue('J1', __('tasks.priority'));
        $ws->setCellValue('K1', __('tasks.task_type'));
        $ws->setCellValue('L1', __('tasks.associated_title'));
        $ws->setCellValue('M1', __('comments.comments_added'));

        $this->progress = 0;
        $progressTotal = $query->count();

        $query->with([
            'author', 'assignee', 'project', 'taskable', 'reminders',
        ])->chunk(100, function ($tasks) use ($progressCallback, $progressTotal) {
            foreach ($tasks as $row => $task) {
                /** @var Task $task */
                $this->writeTaskItem($task, $this->progress);
                $this->progress++;
                $percentage = round(($this->progress / $progressTotal) * 100);
                $percentage = $percentage > 99 ? 99 : $percentage;
                if (!is_null($progressCallback)) {
                    call_user_func_array($progressCallback, [$percentage]);
                }
            }
        });

        $ws->getStyle('A1:M3000')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_TOP)
            ->setWrapText(true);
        $ws->getDefaultColumnDimension()->setWidth(20);
        $ws->getStyle('A1:M1')->getFont()->setBold(true);
        $ws->getColumnDimension('A')->setWidth(30);
        $ws->getColumnDimension('C')->setWidth(50);
    }

    /**
     * @return void
     */
    protected function setUpPage()
    {
        $this->excel = new Spreadsheet();

        /** @var string */
        $title = __('tasks.task.tasks_export');
        $this->excel->getProperties()
            ->setCreator(config('app.name'))
            ->setLastModifiedBy(config('app.name'))
            ->setTitle($title)
            ->setSubject($title)
            ->setKeywords($title . ' ' . config('app.name'));
    }

    /**
     * @param Task $task
     * @param int  $key
     *
     * @return void
     */
    protected function writeTaskItem(Task $task, int $key)
    {
        $row = $key + 2; // Row to insert content at.
        $ws = $this->excel->getActiveSheet();

        if ($task->taskable instanceof AssessmentItemResponse) {
            // @codeCoverageIgnoreStart
            $task->taskable->load(['assessmentItem']);
            $task->taskable->setAttribute('title', $task->taskable->assessmentItem->description);
            // @codeCoverageIgnoreEnd
        }

        $description = !empty($task->description) ? $this->parseHTML($task->description) : '';
        $author = $task->author?->fname . ' ' . $task->author?->sname;
        $assignee = $task->assignee?->fname . ' ' . $task->assignee?->sname;
        $project = $task->project?->title ?? '';

        $route = route($this->module === LibryoModule::tasks()->value ? 'my.tasks.tasks.show' : 'my.actions.tasks.show', ['task' => $task->hash_id], false);
        $route = $this->domain . $route;

        $reminders = $task->reminders->sortByDesc('id')->first()?->remind_on?->format('Y-m-d') ?? '';

        $ws->setCellValue('A' . $row, $task->title);
        $ws->getHyperlink('A' . $row)->setUrl($route);
        $ws->getStyle('A' . $row)->getFont()->setUnderline(true);
        $ws->setCellValue('B' . $row, $project);
        $ws->setCellValue('C' . $row, $description);
        $ws->setCellValue('D' . $row, $task->due_on?->format('Y-m-d') ?? '');
        $ws->setCellValue('E' . $row, $reminders);
        $ws->setCellValue('F' . $row, trim($assignee));
        $ws->setCellValue('G' . $row, trim($author));
        $ws->setCellValue('H' . $row, __('tasks.task.status.' . $task->task_status));
        $ws->setCellValue('I' . $row, $task->completed_at?->format('Y-m-d') ?? '');
        $ws->setCellValue('J' . $row, __('tasks.task.priority.' . $task->priority));
        $ws->setCellValue('K' . $row, __('tasks.task.types.' . $task->taskable_type));
        $ws->setCellValue('L' . $row, $task->taskable->title ?? '');

        if ($this->libryo) {
            $task->load($this->eagerCommentsForLibryo($this->libryo));
        }

        if ($this->organisation && $this->user) {
            $task->load($this->eagerCommentsForOrganisation($this->organisation, $this->user));
        }

        $this->writeComments($task->comments, $ws, static::TITLE_COL . $row, "M{$row}");
    }
}
