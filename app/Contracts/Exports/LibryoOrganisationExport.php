<?php

namespace App\Contracts\Exports;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

interface LibryoOrganisationExport
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
     * @param Libryo                            $libryo
     * @param \App\Models\Customer\Organisation $organisation
     * @param User                              $user
     * @param array<string, mixed>              $filters
     * @param callable|null                     $progressCallback
     *
     * @return Spreadsheet
     */
    public function forLibryo(Libryo $libryo, Organisation $organisation, User $user, array $filters = [], ?callable $progressCallback = null): Spreadsheet;

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
