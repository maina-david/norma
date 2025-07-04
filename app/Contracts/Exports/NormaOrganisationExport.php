<?php

namespace App\Contracts\Exports;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface NormaOrganisationExport
{
    /**
     * Set the domain to be used for the routes.
     *
     * @param string|null $domain
     *
     * @return $this
     */
    public function setDomain(?string $domain): self;

    /**
     * @param Norma                            $norma
     * @param \App\Models\Customer\Organisation $organisation
     * @param User                              $user
     * @param array<string, mixed>              $filters
     * @param callable|null                     $progressCallback
     *
     * @return Spreadsheet
     */
    public function forNorma(Norma $norma, Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet;

    /**
     * @param Organisation         $organisation
     * @param User                 $user
     * @param array<string, mixed> $filters
     * @param callable|null        $progressCallback
     *
     * @return Spreadsheet
     */
    public function forOrganisation(Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet;
}
