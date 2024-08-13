<?php

namespace App\Stores\Customer;

use App\Models\Customer\Libryo;
use App\Models\Ontology\LegalDomain;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class LibryoLegalDomainStore
{
    use AttachesDetaches;

    /**
     * Attach the legal domains to the libryo.
     *
     * @param Libryo                       $libryo
     * @param Collection<int, LegalDomain> $domains
     *
     * @return Libryo
     */
    public function attachLegalDomains(Libryo $libryo, Collection $domains): Libryo
    {
        $this->attachRelations($libryo, 'legalDomains', $domains);

        return $libryo;
    }

    /**
     * Attach the legal domains to the libryo.
     *
     * @param Libryo                       $libryo
     * @param Collection<int, LegalDomain> $domains
     *
     * @return Libryo
     */
    public function detachLegalDomains(Libryo $libryo, Collection $domains): Libryo
    {
        $this->detachRelations($libryo, 'legalDomains', $domains);

        return $libryo;
    }
}
