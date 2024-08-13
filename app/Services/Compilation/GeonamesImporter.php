<?php

namespace App\Services\Compilation;

use App\Models\Compilation\RequirementsCollection;
use App\Models\Geonames\Geoname;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * @codeCoverageIgnore
 */
class GeonamesImporter
{
    /**
     * @param Geoname                $geoname
     * @param RequirementsCollection $country
     * @param bool                   $isCountry
     * @param array<string, mixed>   $data
     *
     * @return RequirementsCollection
     */
    public function createFromGeoname($geoname, ?RequirementsCollection $country, bool $isCountry = false, array $data = []): RequirementsCollection
    {
        $alternateNames = $this->explodeAlternateNames($geoname->alternatenames);
        /** @var RequirementsCollection */
        $reqCollection = RequirementsCollection::create([
            'title' => $geoname->name,
            'alternate_names' => $alternateNames ? json_encode($alternateNames) : null,
            'geoname_id' => $geoname->geonameid,
            'parent_id' => $data['parent_id'] ?? null,
            'location_type_id' => $isCountry ? 1 : ($data['location_type_id'] ?? $this->getAdminLevelLocationType($geoname->fcode)),
            'level' => $data['level'] ?? 1,
            'location_country_id' => $country->id ?? null,
            'flag' => strtolower($geoname->country ?? ''),
            'currency' => $country->currency ?? $this->getCurrency($geoname->country),
            'location_code' => $isCountry ? strtolower($geoname->country ?? '') : $this->getLocationCode($geoname),
            'latitude' => $geoname->latitude,
            'longitude' => $geoname->longitude,
            'slug' => $this->getSlug($geoname),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        return $reqCollection;
    }

    /**
     * @param int                $locationId
     * @param Geoname|int|string $geoname
     * @param bool               $isCountry
     *
     * @return RequirementsCollection|null
     */
    public function updateFromExisting(int $locationId, $geoname, bool $isCountry = false): ?RequirementsCollection
    {
        if (is_numeric($geoname)) {
            $geoname = DB::table('corpus_geonames_geonames')->where('geonameid', $geoname)->first();
        }

        /** @var Geoname $geoname */

        /** @var RequirementsCollection|null $reqCollection */
        $reqCollection = RequirementsCollection::find($locationId);
        if (!$reqCollection) {
            return null;
        }

        $reqCollection->update([
            'alternate_names' => $this->explodeAlternateNames($geoname->alternatenames),
            'geoname_id' => $geoname->geonameid,
            'slug' => $this->getSlug($geoname),
            'location_code' => $isCountry ? strtolower($geoname->country ?? '') : $this->getLocationCode($geoname),
            'latitude' => $geoname->latitude,
            'longitude' => $geoname->longitude,
            'updated_at' => Carbon::now(),
        ]);

        /** @var RequirementsCollection */
        return $reqCollection;
    }

    /**
     * @param int                    $geonameId
     * @param RequirementsCollection $parent
     * @param array<string,mixed>    $data
     *
     * @return RequirementsCollection|null
     */
    public function createAsChildOfExisting(int $geonameId, RequirementsCollection $parent, array $data = []): ?RequirementsCollection
    {
        /** @var Geoname|null */
        $geoname = Geoname::where('geonameid', $geonameId)->first();
        if (!$geoname) {
            return null;
        }

        $data = array_merge([
            'level' => $parent->level + 1,
            'parent_id' => $parent->id,
        ], $data);

        return $this->createFromGeoname($geoname, $parent->country, false, $data);
    }

    /**
     * @param string|null $alternateNames
     *
     * @return array<string>|null
     */
    public function explodeAlternateNames(?string $alternateNames): ?array
    {
        if (!$alternateNames) {
            return null;
        }
        /** @var array<string> */
        $names = explode(',', $alternateNames);

        return count($names) === 0 ? null : $names;
    }

    private function getAdminLevelLocationType(?string $admLevel): int
    {
        switch ($admLevel) {
            case 'ADM1':
                return 20;
            case 'ADM2':
                return 21;
            case 'ADM3':
                return 22;
            case 'ADM4':
                return 21;

            default:
                return 20;
        }
    }

    /**
     * @param Geoname $geoname
     *
     * @return string|null
     */
    private function getLocationCode($geoname): ?string
    {
        switch ($geoname->fcode) {
            case 'ADM1':
                return $geoname->admin1;
            case 'ADM2':
                return $geoname->admin2;
            case 'ADM3':
                return $geoname->admin3;
            case 'ADM4':
                return $geoname->admin4;
            default:
                return $geoname->admin1;
        }
    }

    /**
     * @param Geoname $geoname
     *
     * @return string
     */
    public function getSlug($geoname): string
    {
        $codes = [];
        if ($geoname->admin1 !== '' && !is_null($geoname->admin1)) {
            $codes[] = $geoname->admin1;
        }
        if ($geoname->admin2 !== '' && !is_null($geoname->admin2)) {
            $codes[] = $geoname->admin2;
        }
        if ($geoname->admin3 !== '' && !is_null($geoname->admin3)) {
            $codes[] = $geoname->admin3;
        }
        if ($geoname->admin4 !== '' && !is_null($geoname->admin4)) {
            $codes[] = $geoname->admin4;
        }
        switch ($geoname->fcode) {
            case 'ADM1':
            case 'ADM2':
            case 'ADM3':
            case 'ADM4':
            case 'ADMD':
                $slug = strtolower(($geoname->country ?? '') . '-' . implode('-', $codes));
                break;
            default:
                $slug = strtolower($geoname->country ?? '');
                break;
        }

        if (!str_contains($slug, (string) $geoname->geonameid) && RequirementsCollection::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . $geoname->geonameid;
        }

        return $slug;
    }

    private function getCurrency(?string $countryName): ?string
    {
        $map = [
            'AF' => 'AFN',
            'AL' => 'ALL',
            'DZ' => 'DZD',
            'AS' => 'USD',
            'AD' => 'EUR',
            'AO' => 'AOA',
            'AI' => 'XCD',
            'AQ' => '',
            'AG' => 'XCD',
            'AR' => 'ARS',
            'AM' => 'AMD',
            'AW' => 'AWG',
            'AU' => 'AUD',
            'AT' => 'EUR',
            'AZ' => 'AZM',
            'BS' => 'BSD',
            'BH' => 'BHD',
            'BD' => 'BDT',
            'BB' => 'BBD',
            'BY' => 'BYR',
            'BE' => 'EUR',
            'BZ' => 'BZD',
            'BJ' => 'XOF',
            'BM' => 'BMD',
            'BT' => 'BTN',
            'BO' => 'BOB',
            'BA' => 'BAM',
            'BW' => 'BWP',
            'BV' => 'NOK',
            'BR' => 'BRL',
            'IO' => 'USD',
            'BN' => 'BND',
            'BG' => 'BGN',
            'BF' => 'XOF',
            'BI' => 'BIF',
            'KH' => 'KHR',
            'CM' => 'XAF',
            'CA' => 'CAD',
            'CV' => 'CVE',
            'KY' => 'KYD',
            'CF' => 'XAF',
            'TD' => 'XAF',
            'CL' => 'CLP',
            'CN' => 'CNY',
            'CX' => 'AUD',
            'CC' => 'AUD',
            'CO' => 'COP',
            'KM' => 'KMF',
            'CG' => 'XAF',
            'CD' => 'CDF',
            'CK' => 'NZD',
            'CR' => 'CRC',
            'CI' => 'XOF',
            'HR' => 'HRK',
            'CU' => 'CUP',
            'CY' => 'CYP',
            'CZ' => 'CZK',
            'DK' => 'DKK',
            'DJ' => 'DJF',
            'DM' => 'XCD',
            'DO' => 'DOP',
            'EC' => 'USD',
            'EG' => 'EGP',
            'SV' => 'SVC',
            'GQ' => 'XAF',
            'ER' => 'ERN',
            'EE' => 'EEK',
            'ET' => 'ETB',
            'FK' => 'FKP',
            'FO' => 'DKK',
            'FJ' => 'FJD',
            'FI' => 'EUR',
            'FR' => 'EUR',
            'GF' => 'EUR',
            'PF' => 'XPF',
            'TF' => 'EUR',
            'GA' => 'XAF',
            'GM' => 'GMD',
            'GE' => 'GEL',
            'DE' => 'EUR',
            'GH' => 'GHC',
            'GI' => 'GIP',
            'GR' => 'EUR',
            'GL' => 'DKK',
            'GD' => 'XCD',
            'GP' => 'EUR',
            'GU' => 'USD',
            'GT' => 'GTQ',
            'GN' => 'GNF',
            'GW' => 'GWP',
            'GY' => 'GYD',
            'HT' => 'HTG',
            'HM' => 'AUD',
            'VA' => 'EUR',
            'HN' => 'HNL',
            'HK' => 'HKD',
            'HU' => 'HUF',
            'IS' => 'ISK',
            'IN' => 'INR',
            'ID' => 'IDR',
            'IR' => 'IRR',
            'IQ' => 'IQD',
            'IE' => 'EUR',
            'IL' => 'ILS',
            'IT' => 'EUR',
            'JM' => 'JMD',
            'JP' => 'JPY',
            'JO' => 'JOD',
            'KZ' => 'KZT',
            'KE' => 'KES',
            'KI' => 'AUD',
            'KP' => 'KPW',
            'KR' => 'KRW',
            'KW' => 'KWD',
            'KG' => 'KGS',
            'LA' => 'LAK',
            'LV' => 'LVL',
            'LB' => 'LBP',
            'LS' => 'LSL',
            'LR' => 'LRD',
            'LY' => 'LYD',
            'LI' => 'CHF',
            'LT' => 'LTL',
            'LU' => 'EUR',
            'MO' => 'MOP',
            'MK' => 'MKD',
            'MG' => 'MGA',
            'MW' => 'MWK',
            'MY' => 'MYR',
            'MV' => 'MVR',
            'ML' => 'XOF',
            'MT' => 'MTL',
            'MH' => 'USD',
            'MQ' => 'EUR',
            'MR' => 'MRO',
            'MU' => 'MUR',
            'YT' => 'EUR',
            'MX' => 'MXN',
            'FM' => 'USD',
            'MD' => 'MDL',
            'MC' => 'EUR',
            'MN' => 'MNT',
            'MS' => 'XCD',
            'MA' => 'MAD',
            'MZ' => 'MZM',
            'MM' => 'MMK',
            'NA' => 'NAD',
            'NR' => 'AUD',
            'NP' => 'NPR',
            'NL' => 'EUR',
            'AN' => 'ANG',
            'NC' => 'XPF',
            'NZ' => 'NZD',
            'NI' => 'NIO',
            'NE' => 'XOF',
            'NG' => 'NGN',
            'NU' => 'NZD',
            'NF' => 'AUD',
            'MP' => 'USD',
            'NO' => 'NOK',
            'OM' => 'OMR',
            'PK' => 'PKR',
            'PW' => 'USD',
            'PS' => '',
            'PA' => 'PAB',
            'PG' => 'PGK',
            'PY' => 'PYG',
            'PE' => 'PEN',
            'PH' => 'PHP',
            'PN' => 'NZD',
            'PL' => 'PLN',
            'PT' => 'EUR',
            'PR' => 'USD',
            'QA' => 'QAR',
            'RE' => 'EUR',
            'RO' => 'ROL',
            'RU' => 'RUB',
            'RW' => 'RWF',
            'SH' => 'SHP',
            'KN' => 'XCD',
            'LC' => 'XCD',
            'PM' => 'EUR',
            'VC' => 'XCD',
            'WS' => 'WST',
            'SM' => 'EUR',
            'ST' => 'STD',
            'SA' => 'SAR',
            'SN' => 'XOF',
            'CS' => 'CSD',
            'SC' => 'SCR',
            'SL' => 'SLL',
            'SG' => 'SGD',
            'SK' => 'SKK',
            'SI' => 'SIT',
            'SB' => 'SBD',
            'SO' => 'SOS',
            'ZA' => 'ZAR',
            'GS' => '',
            'ES' => 'EUR',
            'LK' => 'LKR',
            'SD' => 'SDD',
            'SR' => 'SRD',
            'SJ' => 'NOK',
            'SZ' => 'SZL',
            'SE' => 'SEK',
            'CH' => 'CHF',
            'SY' => 'SYP',
            'TW' => 'TWD',
            'TJ' => 'TJS',
            'TZ' => 'TZS',
            'TH' => 'THB',
            'TL' => 'USD',
            'TG' => 'XOF',
            'TK' => 'NZD',
            'TO' => 'TOP',
            'TT' => 'TTD',
            'TN' => 'TND',
            'TR' => 'TRL',
            'TM' => 'TMM',
            'TC' => 'USD',
            'TV' => 'AUD',
            'UG' => 'UGX',
            'UA' => 'UAH',
            'AE' => 'AED',
            'GB' => 'GBP',
            'US' => 'USD',
            'UM' => 'USD',
            'UY' => 'UYU',
            'UZ' => 'UZS',
            'VU' => 'VUV',
            'VE' => 'VEB',
            'VN' => 'VND',
            'VG' => 'USD',
            'VI' => 'USD',
            'WF' => 'XPF',
            'EH' => 'MAD',
            'YE' => 'YER',
            'ZM' => 'ZMK',
            'ZW' => 'ZWD',
        ];

        return $map[$countryName] ?? null;
    }
}
