<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\LibryoOrganisationExport;
use App\Exports\Tasks\TasksExcelExport;
use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;

class GenerateTasksExportExcel extends LibryoAndOrganisationExport
{
    /**
     * @param string                            $tempFileName
     * @param User                              $user
     * @param Libryo|null                       $libryo
     * @param \App\Models\Customer\Organisation $organisation
     * @param array<string, mixed>              $filters
     * @param string|null                       $domain
     * @param string                            $module
     */
    public function __construct(
        protected string $tempFileName,
        protected User $user,
        protected ?Libryo $libryo,
        protected Organisation $organisation,
        protected array $filters = [],
        protected ?string $domain = null,
        protected string $module = 'tasks',
    ) {
        parent::__construct($this->tempFileName, $this->user, $this->libryo, $this->organisation, $this->filters);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExporter(): LibryoOrganisationExport
    {
        return app(TasksExcelExport::class)->setDomain($this->domain)->setModule($this->module);
    }
}
