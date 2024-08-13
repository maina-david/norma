<?php

namespace App\Services\Arachno\Crawlers\Sources\Us;

use App\Models\Arachno\Crawl;
use App\Services\Arachno\Frontier\PageCrawl;
use Illuminate\Filesystem\FilesystemAdapter;
use League\Flysystem\Ftp\FtpAdapter;

/**
 * @codeCoverageIgnore
 */
class CaseMakerFull extends AbstractCaseMakerConfig
{
    public function parsePage(PageCrawl $pageCrawl): void
    {
    }

    public function customStartCrawl(Crawl $crawl): void
    {
        $this->fullCrawl($crawl);
    }

    public function fullCrawl(Crawl $crawl): void
    {
        /** @var FilesystemAdapter $disk */
        $disk = $this->getDisk();

        $adapter = $disk->getAdapter();
        foreach ($this->getJurisdictionDirs() as $juriDirName) {
            // have to force a reconnection every time, as it seems to somehow not work for every second request
            /** @var FtpAdapter $adapter */
            $adapter->__destruct();
            $year = $this->getMaxYear($juriDirName);
            $dir = sprintf('%s/%s/ADMIN/XML', $juriDirName, $year);
            if ($disk->directoryExists($dir)) {
                $this->parseDirForXmlFiles($crawl, $dir);
            }
            $dir = sprintf('%s/%s/STAT/XML', $juriDirName, $year);
            if ($disk->directoryExists($dir)) {
                $this->parseDirForXmlFiles($crawl, $dir);
            }
        }
    }

    /**
     * @return array<string>
     */
    public function getJurisdictionDirs(): array
    {
        return [
            // 'AK',
            // 'AL',
            // 'AR',
            // 'AZ',
            // 'CA',
            // 'CD',
            // 'CO',
            // 'CT',
            'DE',
            // 'FL',
            // 'GA',
            // 'HI',
            // 'IA',
            // 'ID',
            // 'IL',
            // 'IN',
            // 'KS',
            // 'KY',
            // 'LA',
            // 'MA',
            // 'MD',
            'ME',
            // 'MI',
            // 'MN',
            // 'MO',
            // 'MS',
            // 'MT',
            // 'NC',
            // 'ND',
            // 'NE',
            // 'NH',
            // 'NJ',
            // 'NM',
            // 'NV',
            // 'NY',
            // 'OH',
            // 'OK',
            // 'OR',
            // 'PA',
            // 'PR',
            // 'RI',
            // 'SC',
            // 'SD',
            // 'TN',
            // 'TX',
            // 'US',
            // 'USCFR/US',
            // 'UT',
            // 'VA',
            // 'VT',
            // 'WA',
            // 'WI',
            // 'WV',
            // 'WY',
        ];
    }
}
