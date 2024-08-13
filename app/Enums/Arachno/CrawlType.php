<?php

namespace App\Enums\Arachno;

enum CrawlType: int
{
    case GENERAL = 0;
    case FULL_CATALOGUE = 1; // a full crawl of the source to populate the catalogue
    case NEW_CATALOGUE_WORK_DISCOVERY = 2; // a partial crawl of the source to find new works that need to be added to the catalogue
    case FETCH_WORKS = 3; // a crawl to fetch specific works from the source
    case WORK_CHANGES_DISCOVERY = 4; // a crawl to check which works have changed and need to be fetched again
    case FOR_UPDATES = 5; // a crawl to fetch documents for legal updates
    case SEARCH = 6; // a crawl for the general purpose search engine
}
