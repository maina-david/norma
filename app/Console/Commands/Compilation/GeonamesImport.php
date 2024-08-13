<?php

namespace App\Console\Commands\Compilation;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Geonames\Geoname;
use App\Services\Compilation\GeonamesImporter;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class GeonamesImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'geonames:import {countries}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import geonames locations into locations database';

    /** @var GeonamesImporter */
    protected $geonames = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(GeonamesImporter $geonames)
    {
        $this->geonames = $geonames;

        $this->start();

        return 0;
    }

    public function start(): void
    {
        $countries = $this->getCountriesQuery()->get();
        /** @var string|null */
        $countriesArg = $this->argument('countries');
        if (is_null($countriesArg)) {
            $this->error('Please provide a comma separated list of country Codes');
            exit;
        }
        /** @var array<string> */
        $limitToCountries = explode(',', $countriesArg);
        /** @var array<string> */
        $limitToCountries = array_intersect($limitToCountries, $countries->pluck('country')->toArray());

        if (empty($limitToCountries)) {
            $this->error('No valid countries detected');
            exit;
        }
        foreach ($countries as $c) {
            if (!in_array($c->country, $limitToCountries)) {
                continue;
            }
            if ($country = RequirementsCollection::where('location_type_id', 1)->where('flag', strtolower($c->country))->first()) {
                /** @var RequirementsCollection $country */
                $this->warn('Found: ' . $country->title);
                $country = $this->geonames->updateFromExisting($country->id, $c, true);
            } else {
                $data = [
                    'level' => 1,
                ];
                /** @var RequirementsCollection|null */
                $country = $this->geonames->createFromGeoname($c, null, true, $data);
                if (!$country) {
                    throw new Exception('Country was not created');
                }

                DB::table((new RequirementsCollection())->getTable())
                    ->where('id', $country->id)
                    ->update(['location_country_id' => $country->id]);
                $this->info('created:' . $country->slug . ' , ' . $country->title);
            }

            /** @var RequirementsCollection $country */
            $level1ChildrenQ = $this->getChildrenQuery($c);
            $level1ChildrenQ->chunk(1, function ($level1Children) use ($country) {
                foreach ($level1Children as $level1Child) {
                    gc_collect_cycles();
                    $createdLocationLevel1 = $this->processChild($country, $level1Child, 2, $country);
                    if (!$createdLocationLevel1) {
                        continue;
                    }
                    $level2Children = $this->getChildrenQuery($level1Child)->get();
                    foreach ($level2Children as $level2Child) {
                        gc_collect_cycles();
                        $createdLocationLevel2 = $this->processChild($createdLocationLevel1, $level2Child, 3, $country);
                        if (!$createdLocationLevel2) {
                            continue;
                        }
                        $level3Children = $this->getChildrenQuery($level2Child)->get();
                        foreach ($level3Children as $level3Child) {
                            gc_collect_cycles();
                            $createdLocationLevel3 = $this->processChild($createdLocationLevel2, $level3Child, 4, $country);
                            if (!$createdLocationLevel3) {
                                continue;
                            }
                            $level4Children = $this->getChildrenQuery($level3Child)->get();
                            foreach ($level4Children as $level4Child) {
                                $createdLocationLevel4 = $this->processChild($createdLocationLevel3, $level4Child, 5, $country);
                            }
                        }
                    }
                }
            });
        }
    }

    /**
     * @param RequirementsCollection $parent
     * @param Geoname                $geoname
     * @param int                    $level
     * @param RequirementsCollection $country
     *
     * @return RequirementsCollection|null
     */
    private function processChild(RequirementsCollection $parent, $geoname, int $level, RequirementsCollection $country): ?RequirementsCollection
    {
        /** @var RequirementsCollection|null */
        $reqCollection = RequirementsCollection::where('parent_id', $parent->id)
            ->where(function ($q) use ($geoname) {
                // exact name match
                $q->where('geoname_id', $geoname->geonameid);
                $q->orwhere('title', $geoname->name);
                if ($geoname->alternatenames) {
                    $q->orWhereIn('title', $this->geonames->explodeAlternateNames($geoname->alternatenames) ?? []);
                }
            })
            ->first();
        if ($reqCollection) {
            $this->warn('Found: ' . $reqCollection->slug . ', ' . $reqCollection->title);
            $reqCollection = $this->geonames->updateFromExisting($reqCollection->id, $geoname, false);
        } else {
            $data = [
                'level' => $level,
                'parent_id' => $parent->id,
            ];
            $reqCollection = $this->geonames->createFromGeoname($geoname, $country, false, $data);
            $this->info($level . ' created:' . $reqCollection->slug . ' , ' . $reqCollection->title);
        }

        return $reqCollection;
    }

    /**
     * @param Geoname $geoname
     *
     * @return Builder
     */
    private function getChildrenQuery($geoname): Builder
    {
        $childIds = DB::table('corpus_geonames_geonames', 'gg')
            ->join('corpus_geonames_hierarchy as gh', 'parentId', '=', 'gg.geonameid')
            ->join('corpus_geonames_geonames as ggg', 'gh.childId', 'ggg.geonameid')
            ->where('gg.fclass', 'A')
            ->where('gh.type', 'ADM')
            ->where('gg.geonameid', $geoname->geonameid)
            ->where('ggg.fclass', 'A')
            ->pluck('childId')
            ->toArray();

        return DB::table('corpus_geonames_geonames')
            ->whereIn('geonameid', $childIds)
            ->orderBy('geonameid');
    }

    public function getCountriesQuery(): Builder
    {
        return
            DB::table('corpus_geonames_geonames')
                ->where('fclass', 'A')
                ->where('admin1', '00')
                ->whereIn('fcode', ['PCL', 'PCLD', 'PCLF', 'PCLI', 'PCLS']);
    }
}