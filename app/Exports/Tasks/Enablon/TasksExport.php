<?php

namespace App\Exports\Tasks\Enablon;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Traits\SetsUpLibryoOrgExport;
use App\Exports\Traits\UsesExportMapper;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Tasks\Task;
use App\Traits\CleansWorksheetTitle;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TasksExport implements LibryoOrganisationExport
{
    use CleansWorksheetTitle;
    use SetsUpLibryoOrgExport;
    use UsesExportMapper;

    /**
     * {@inheritDoc}
     */
    protected function getExportName(): string
    {
        return __('corpus.enablon.types.tasks_export');
    }

    /**
     * Get the default mapping of the headers.
     *
     * @return array<int, string>
     */
    protected function defaultColumnOrder(): array
    {
        return [
            'Path',
            'Name [EN]',
            'Name [FR]',
            'Name [ES]',
            'Name [DE]',
            'Name [ZH]',
            'Name [AR]',
            'Task Status',
            'Start Date',
            'Due Date',
            'Completion Date',
            'Comments',
            'Library \\ Library Documents',
            'Needs Compliance Review \\ Compliance Watch',
            'Non Compliance Comments',
            'File(s)',
            'Responsible',
            'Id',
            'Folder',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getRowValue(string $column, mixed $row): ?string
    {
        return match ($column) {
            'Path', 'Folder' => '<Top>',
            'Name [EN]', 'Name [AR]', 'Name [ZH]', 'Name [DE]', 'Name [ES]', 'Name [FR]' => $row->title,
            'Task Status' => '4',
            'Start Date' => $row->created_at?->format('m/d/Y') ?? '',
            'Due Date' => $row->due_on ? Carbon::parse($row->due_on)->format('m/d/Y') : '',
            'Id' => $row->id,
            'Description' => $row->description,
            'Requirement' => $row->taskable_type === (new Reference())->getMorphClass() ? $row->taskable_id : null,
            'Cargill Recurrence Type' => 'Fixed',
            'Cargill Frequency' => $row->frequency ? $row->frequency_interval->getRelative() : 'Once',
            'Cargill Type of Selection' => 'Date',
            'Cargill Day of the Month' => $row->due_on?->format('d'),
            'Cargill Month' => $row->due_on?->format('M'),
            'Cargill Every' => $row->frequency,
            default => null,
        };
    }

    /**
     * {@inheritDoc}
     */
    public function forLibryo(
        Libryo $libryo,
        Organisation $organisation,
        User $user,
        array $filters = [],
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->setupExport($user, $organisation, $libryo, $progressCallback);

        return $this->build();
    }

    /**
     * {@inheritDoc}
     */
    public function forOrganisation(
        Organisation $organisation,
        User $user,
        array $filters = [],
        ?callable $progressCallback = null
    ): Spreadsheet {
        $this->setupExport($user, $organisation, null, $progressCallback);

        return $this->build();
    }

    /**
     * Build the spreadsheet.
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return \PhpOffice\PhpSpreadsheet\Spreadsheet
     */
    public function build(): Spreadsheet
    {
        $sheet = $this->addNewSheet(false);
        $columns = $this->getSheetColumns();
        $this->writeColumnHeaders($sheet, $columns);

        $this->getTasksQuery()->chunk(500, function ($tasks) use ($columns, $sheet) {
            foreach ($tasks as $task) {
                $this->rowNumber++;

                /** @var \App\Models\Tasks\Task $task */
                $this->writeRow($sheet, $task, $columns);
                $this->incrementProgress();
            }
        });

        $this->excel->removeSheetByIndex(0);
        $this->excel->setActiveSheetIndex(0);

        return $this->excel;
    }

    /**
     * Get the works query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function getTasksQuery(): Builder
    {
        $places = Libryo::where('organisation_id', $this->organisation->id)
            ->select([qualify_column(Libryo::class, 'id')]);
        $places = $this->libryo ? [$this->libryo->id] : $places;

        $query = Task::whereIn('place_id', $places)
            ->select([
                'id',
                'title',
                'taskable_type',
                'taskable_id',
                'description',
                'due_on',
                'created_at',
                'frequency',
                'frequency_interval',
            ])
            ->orderBy('id');

        $this->progressTotal = $query->clone()->count();

        /** @var Builder */
        return $query;
    }
}
