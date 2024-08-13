<?php

namespace App\Services\Geo;

use Geocoder\Location;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geohash\Geohash;
use League\Geotools\Geotools;

/**
 * Geohashes co-ordinates.
 **/
class Geohasher
{
    /**
     * @param Geotools $geoTools
     **/
    public function __construct(protected Geotools $geoTools)
    {
    }

    /**
     * @param Location|array<float>|string $coordinates
     *
     * @return string
     */
    public function encode(Location|array|string $coordinates): string
    {
        $coordToGeohash = new Coordinate($coordinates);

        /** @var Geohash */
        $geohash = $this->geoTools->geohash();
        $geohash->encode($coordToGeohash);

        return $geohash->getGeohash();
    }

    /**
     * @param int $zoom
     *
     * @return int
     */
    public function getGeohashLength(int $zoom): int
    {
        $minZoom = config('services.google_maps.zoom.min');
        $maxZoom = config('services.google_maps.zoom.max');
        $minGeohashLength = config('services.google_maps.geohash.min');
        $maxGeohashLength = config('services.google_maps.geohash.max');
        $a = (float) $minGeohashLength / exp($minZoom / ($maxZoom - $minZoom) * log($maxGeohashLength / $minGeohashLength));
        $b = (float) log($maxGeohashLength / $minGeohashLength) / ($maxZoom - $minZoom);

        return (int) max($minGeohashLength, min($a * exp($b * $zoom), $maxGeohashLength));
    }
}
