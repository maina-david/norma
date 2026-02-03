<?php

namespace App\Stores\Customer;

use App\Models\Customer\Norma;
use App\Models\Ontology\LegalDomain;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class NormaLegalDomainStore
{
    use AttachesDetaches;

    /**
     * Attach the legal domains to the norma.
     *
     * @param Norma                       $norma
     * @param Collection<int, LegalDomain> $domains
     *
     * @return Norma
     */
    public function attachLegalDomains(Norma $norma, Collection $domains): Norma
    {
        $this->attachRelations($norma, 'legalDomains', $domains);

        return $norma;
    }

    /**
     * Attach the legal domains to the norma.
     *
     * @param Norma                       $norma
     * @param Collection<int, LegalDomain> $domains
     *
     * @return Norma
     */
    public function detachLegalDomains(Norma $norma, Collection $domains): Norma
    {
        $this->detachRelations($norma, 'legalDomains', $domains);

        return $norma;
    }
}
