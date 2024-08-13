<?php

namespace App\Stores\Arachno;

use App\Enums\Storage\FileType;
use App\Stores\Traits\UsesContentAddressableStorage;

class SearchPageContentStore
{
    use UsesContentAddressableStorage;

    public const PATH_PREFIX = '/search';

    /**
     * @codeCoverageIgnore
     *
     * @return string
     */
    public function getDiskName(): string
    {
        return config('filesystems.filetypes.' . FileType::content()->value . '.disk');
    }
}
