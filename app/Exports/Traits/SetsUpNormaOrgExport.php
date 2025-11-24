<?php

namespace App\Exports\Traits;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

trait SetsUpNormaOrgExport
{
    /** @var callable|null */
    protected $progressCallback = null;

    /** @var int */
    protected int $progress = 0;

    /** @var int */
    protected int $progressTotal = 0;

    /** @var int */
    protected int $rowNumber = 1;

    /** @var Spreadsheet */
    protected Spreadsheet $excel;

    /** @var Norma|null */
    protected ?Norma $norma = null;

    /** @var Organisation */
    protected Organisation $organisation;

    /** @var User|null */
    protected ?User $user = null;

    /** @var string|null */
    protected ?string $domain = null;

    /**
     * Get the export name to be used.
     *
     * @return string
     */
    abstract protected function getExportName(): string;

    /**
     * Set the domain to be used for the routes.
     *
     * @param string|null $domain
     *
     * @return self
     */
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Set up the common items.
     *
     * @param \App\Models\Auth\User             $user
     * @param \App\Models\Customer\Organisation $organisation
     * @param \App\Models\Customer\Norma|null  $norma
     * @param callable|null                     $progressCallback
     *
     * @return void
     */
    protected function setupExport(User $user, Organisation $organisation, ?Norma $norma = null, ?callable $progressCallback = null): void
    {
        $this->progress = 0;
        $this->rowNumber = 1;
        $this->organisation = $organisation;
        $this->norma = $norma;
        $this->user = $user;
        $this->progressCallback = $progressCallback;
        $this->setUpSpreadsheet();
    }

    /**
     * @return void
     */
    protected function setUpSpreadsheet(): void
    {
        $this->excel = new Spreadsheet();
        $title = $this->getExportName();

        $this->excel->getProperties()
            ->setCreator(config('app.name'))
            ->setLastModifiedBy(config('app.name'))
            ->setTitle($title)
            ->setSubject($title)
            ->setKeywords($title . ' ' . config('app.name'));
    }

    /**
     * Increment the progress.
     *
     * @return void
     */
    protected function incrementProgress(): void
    {
        $this->progress++;

        if (!is_null($this->progressCallback)) {
            $percentage = round(($this->progress / $this->progressTotal) * 100);
            $percentage = min($percentage, 99);

            call_user_func_array($this->progressCallback, [$percentage]);
        }
    }

    /**
     * Create a new worksheet and set it as active.
     *
     * @param bool $align
     *
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     *
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    protected function addNewSheet(bool $align = true): Worksheet
    {
        $index = $this->excel->getSheetCount();
        $sheet = new Worksheet($this->excel);
        $this->excel->addSheet($sheet);
        $this->excel->setActiveSheetIndex($index);

        // @codeCoverageIgnoreStart
        if ($align) {
            $sheet->getStyle('A1:O3000')->getAlignment()->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
            $sheet->getDefaultColumnDimension()->setWidth(40);
            $sheet->getStyle('A1:CC1')->getFont()->setBold(true);
        }
        // @codeCoverageIgnoreEnd

        return $sheet;
    }
}
