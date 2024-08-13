<?php

namespace App\Services\Storage;

use App\Models\Customer\Organisation;
use App\Models\Storage\My\File;
use Illuminate\Http\UploadedFile;

class OrganisationStorageService
{
    public const BYTES_IN_GB = 1073741824;

    /**
     * Returns the storage allocations for the organisation.
     *
     * @param Organisation $organisation
     *
     * @return array<string, mixed>
     */
    public function getAllocation(Organisation $organisation): array
    {
        $used_bytes = (int) $this->getStorageUsed($organisation);

        return [
            'total_gb' => $organisation->getStorageAllocation(),
            'total_bytes' => $this->getTotalStorage($organisation),
            'used_bytes' => $used_bytes,
            'used_gb' => $this->toRoundedGb($used_bytes),
        ];
    }

    /**
     * Get the number of bytes used by the organisation.
     *
     * @param Organisation $organisation
     *
     * @return int
     */
    public function getStorageUsed(Organisation $organisation): int
    {
        return File::allForOrganisation($organisation)->sum('size');
    }

    /**
     * Checks whether the organisation has enough remaining space to store the document.
     *
     * @param Organisation $organisation
     * @param UploadedFile $file
     *
     * @return bool
     */
    public function canStoreDocument(Organisation $organisation, UploadedFile $file): bool
    {
        $remaining = $this->getRemainingStorage($organisation);

        return $remaining > $file->getSize();
    }

    /**
     * Get the number of bytes used by the organisation.
     *
     * @param Organisation $organisation
     *
     * @return float
     */
    public function getRemainingStorage(Organisation $organisation): float
    {
        $used_bytes = $this->getStorageUsed($organisation);
        $total = $this->getTotalStorage($organisation);

        return $total - $used_bytes;
    }

    /**
     * Get the total storage allocated to the organisation in bytes.
     *
     * @param Organisation $organisation
     *
     * @return float
     */
    private function getTotalStorage(Organisation $organisation): float
    {
        $total = $organisation->getStorageAllocation();

        return $total * self::BYTES_IN_GB;
    }

    /**
     * Convert bytes to rounded (readable) gigabytes.
     *
     * @param int $bytes
     *
     * @return float
     */
    private function toRoundedGb(int $bytes): float
    {
        return round($bytes / self::BYTES_IN_GB, 2);
    }
}
