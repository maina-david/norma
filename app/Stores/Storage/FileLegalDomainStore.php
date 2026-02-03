<?php

namespace App\Stores\Storage;

use App\Models\Ontology\LegalDomain;
use App\Models\Storage\My\File;
use App\Stores\Traits\AttachesDetaches;
use Illuminate\Support\Collection;

class FileLegalDomainStore
{
    use AttachesDetaches;

    /**
     * Attach the legal domains to the norma.
     *
     * @param File                         $file
     * @param Collection<int, LegalDomain> $domains
     *
     * @return File
     */
    public function attachLegalDomains(File $file, Collection $domains): File
    {
        $this->attachRelations($file, 'legalDomains', $domains);

        return $file;
    }

    /**
     * Attach the legal domains to the norma.
     *
     * @param File                         $file
     * @param Collection<int, LegalDomain> $domains
     *
     * @return File
     */
    public function detachLegalDomains(File $file, Collection $domains): File
    {
        $this->detachRelations($file, 'legalDomains', $domains);

        return $file;
    }
}
