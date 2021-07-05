<?php

namespace App\Core\Skins\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Skins\Models\Skin;
use App\Core\Terms\Models\Language;
use Illuminate\Support\Collection;

/**
 * Class GetSkinByDateOrDayService
 * @package App\Services
 */
class GetSkinByDateOrDayService
{
    /**
     * @var \App\Core\Countries\Services\GetCountryByCodeCountryService
     */
    private $getCountryByCodeCountryService;

    /**
     * GetSkinByDateOrDayService constructor.
     * @param \App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService
     */
    public function __construct(\App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService)
    {
        $this->getCountryByCodeCountryService = $getCountryByCodeCountryService;
    }

    private $tagSkin = [ Skin::TAG_CACHE_MODEL,  RegionRapi::TAG_CACHE_MODEL, Language::TAG_CACHE_MODEL];

    /**
     * @param $date
     * @param $day
     * @param $countryCode
     * @return Collection
     */
    public function execute($date, $day, $countryCode): Collection
    {
        $dateInit = $date;
        $dateEnd  = $dateInit;
        $dayInit  = $day;
        $dayEnd   = $dayInit;

        $country = $this->getCountryByCodeCountryService->execute($countryCode);

        $idsSkinUndefinedProgram = $this->getSkinUndefinedProgram($country);

        $idsSkinByDate = $this->getSkinByRangeDate($dateInit, $dateEnd, $country);

        $idsSkinByPeriodDate = $this->getSkinByPeriodYearDate($dateInit, $dateEnd, $country);

        $idsSkinByPeriodDay = $this->getSkinByPeriodWeekDay($dayInit, $dayEnd, $country);

        $merge = $idsSkinUndefinedProgram->merge($idsSkinByDate);
        $merge = $merge->merge($idsSkinByPeriodDate);
        $merge = $merge->merge($idsSkinByPeriodDay);

        return $merge;

    }

    /**
     * @param $country
     * @return Collection
     */
    private function getSkinUndefinedProgram($country): Collection
    {
        return Skin::query()
            ->join("program_skins as ps", "skins.id_skin", '=', "ps.id_skin")
            ->join('region_skins', 'skins.id_skin', '=', 'region_skins.id_skin')
            ->join('country_region', 'country_region.id_region', '=', 'region_skins.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', ModelConst::PROGRAM_RANGE_UNDEFINED)
            ->groupBy([ "skins.id_skin" ])
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_skins.deleted_at')
            ->whereNull('ps.deleted_at')
            ->getFromCache([ "skins.id_skin" ], $this->tagSkin);
    }

    /**
     * @param $dateInit
     * @param $dateEnd
     * @param $country
     * @return Collection
     */
    private function getSkinByRangeDate($dateInit, $dateEnd, $country): Collection
    {
        if ($dateInit === null || $dateEnd === null) {
            return collect([]);
        }

        return Skin::query()
            ->join("program_skins as ps", "skins.id_skin", '=', "ps.id_skin")
            ->leftJoin("date_programs_skins as dp", "ps.id_program", '=', "dp.id_program")
            ->join('region_skins', 'skins.id_skin', '=', 'region_skins.id_skin')
            ->join('country_region', 'country_region.id_region', '=', 'region_skins.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', ModelConst::PROGRAM_RANGE_DEFINED_DATE)
            ->where('period_current_program', ModelConst::PROGRAM_PERIOD_DATE)
            ->where('date_init', '<=', $dateInit)
            ->where('date_end', '>=', $dateEnd)
            ->groupBy([ "skins.id_skin" ])
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_skins.deleted_at')
            ->whereNull('dp.deleted_at')
            ->whereNull('ps.deleted_at')
            ->getFromCache([ "skins.id_skin" ], $this->tagSkin);
    }

    /**
     * @param $dateInit
     * @param $dateEnd
     * @param $country
     * @return Collection
     */
    private function getSkinByPeriodYearDate($dateInit, $dateEnd, $country): Collection
    {
        if ($dateInit === null || $dateEnd === null) {
            return collect([]);
        }

        return Skin::query()
            ->join("program_skins as ps", "skins.id_skin", '=', "ps.id_skin")
            ->leftJoin("date_programs_skins as dp", "ps.id_program", '=', "dp.id_program")
            ->join('region_skins', 'skins.id_skin', '=', 'region_skins.id_skin')
            ->join('country_region', 'country_region.id_region', '=', 'region_skins.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', ModelConst::PROGRAM_RANGE_CURRENT_DATE)
            ->where('type_current_program', ModelConst::PROGRAM_TYPE_CURRENT_YEAR)
            ->where('period_current_program', ModelConst::PROGRAM_PERIOD_DATE)
            ->whereRaw("DATE_FORMAT(date_init, '%m-%d') <= DATE_FORMAT('{$dateInit}', '%m-%d')")
            ->whereRaw("DATE_FORMAT(date_end, '%m-%d') >= DATE_FORMAT('{$dateEnd}', '%m-%d')")
            ->groupBy([ "skins.id_skin" ])
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_skins.deleted_at')
            ->whereNull('dp.deleted_at')
            ->whereNull('ps.deleted_at')
            ->getFromCache([ "skins.id_skin" ], $this->tagSkin);
    }

    /**
     * @param $dayInit
     * @param $dayEnd
     * @param $country
     * @return Collection
     */
    private function getSkinByPeriodWeekDay($dayInit, $dayEnd, $country): Collection
    {
        return Skin::query()
            ->join("program_skins as ps", "skins.id_skin", '=', "ps.id_skin")
            ->leftJoin("date_programs_skins as dp", "ps.id_program", '=', "dp.id_program")
            ->join('region_skins', 'skins.id_skin', '=', 'region_skins.id_skin')
            ->join('country_region', 'country_region.id_region', '=', 'region_skins.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', '=', ModelConst::PROGRAM_RANGE_DEFINED_DATE)
            ->where('type_current_program', ModelConst::PROGRAM_TYPE_CURRENT_WEEK)
            ->where('period_current_program', ModelConst::PROGRAM_PERIOD_DAY)
            ->where('day_init', '<=', $dayInit)
            ->where('day_end', '>=', $dayEnd)
            ->groupBy([ "skins.id_skin" ])
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_skins.deleted_at')
            ->getFromCache([ "skins.id_skin" ], $this->tagSkin);
    }
}
