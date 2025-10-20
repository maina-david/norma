<?php

namespace App\Jobs\Exports;

use App\Contracts\Exports\NormaOrganisationExport;
use App\Exports\Tasks\TasksExcelExport;
use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;

class GenerateTasksExportExcel extends NormaAndOrganisationExport
{
    /**
     * @param string                            $tempFileName
     * @param User                              $user
     * @param Norma|null                       $norma
     * @param \App\Models\Customer\Organisation $organisation
     * @param array<string, mixed>              $filters
     * @param string|null                       $domain
     * @param string                            $module
     */
    public function __construct(
        protected string $tempFileName,
        protected User $user,
        protected ?Norma $norma,
        protected Organisation $organisation,
        protected array $filters = [],
        protected ?string $domain = null,
        protected string $module = 'tasks',
    ) {
        parent::__construct($this->tempFileName, $this->user, $this->norma, $this->organisation, $this->filters);
    }

    /**
     * {@inheritDoc}
     */
    protected function getExporter(): NormaOrganisationExport
    {
        return app(TasksExcelExport::class)->setDomain($this->domain)->setModule($this->module);
    }
}
