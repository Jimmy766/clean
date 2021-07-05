<?php

namespace App\Core\Countries\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class CheckCountryAndStateBlocksService
 * @package App\Services
 */
class CheckCountryAndStateBlocksService
{

    /**
     * @param Collection $exceptCountryState
     * @param            $data
     * @param int        $paginate
     * @return LengthAwarePaginator|Collection
     */
    public static function execute(Collection $exceptCountryState, $data, int $paginate = 0)
    {
        $isoState   = strtolower(request()->client_country_region_iso);
        $isoCountry = strtolower(request()->client_country_iso);

        $exceptCountryState = $exceptCountryState->filter(
            self::filterExistCountryStateTransform(
                $isoState,
                $isoCountry
            )
        );

        if ($exceptCountryState->count() > 0) {
            $dataReturn = collect([]);
            if ($paginate === 1) {
                return new LengthAwarePaginator(
                    $dataReturn, $dataReturn->count(), 50, 1, [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                ]
                );
            }

            return $dataReturn;
        }

        return $data;
    }

    /**
     * @param $isoState
     * @param $isoCountry
     * @return callable
     */
    private static function filterExistCountryStateTransform($isoState, $isoCountry): callable
    {
        return static function ($item, $key) use ($isoState, $isoCountry) {
            if ( !array_key_exists('iso_country', $item) || !array_key_exists('iso_state', $item)) {
                return false;
            }

            $checkState = strtolower($item[ 'iso_state' ]) == strtolower($isoState);
            $checkCountry = strtolower($item[ 'iso_country' ]) == strtolower($isoCountry);
            if ($checkState && $checkCountry) {
                return true;
            }

            return false;
        };
    }

}
