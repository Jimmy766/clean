<?php

namespace App\Core\Slides\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Terms\Models\Language;
use App\Core\Countries\Models\RegionRapi;
use App\Core\Countries\Services\GetCountryByCodeCountryService;
use App\Core\Slides\Models\Slide;
use Illuminate\Support\Collection;

/**
 * Class GetSlideByDateOrDayService
 * @package App\Services
 */
class GetSlideByDateOrDayService
{

    /**
     * @var GetCountryByCodeCountryService
     */
    private $getCountryByCodeCountryService;

    /**
     * GetSlideByDateOrDayService constructor.
     * @param \App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService
     */
    public function __construct(\App\Core\Countries\Services\GetCountryByCodeCountryService $getCountryByCodeCountryService)
    {
        $this->getCountryByCodeCountryService = $getCountryByCodeCountryService;
    }

    private $tagSlide = [ Slide::TAG_CACHE_MODEL, RegionRapi::TAG_CACHE_MODEL, Language::TAG_CACHE_MODEL];

    /**
     * @param $date
     * @param $day
     * @param $countryCode
     * @return Collection
     */
    public function execute($date, $day, $countryCode)
    {
        $dateInit = $date;
        $dateEnd  = $dateInit;
        $dayInit  = $day;
        $dayEnd   = $dayInit;

        $country = $this->getCountryByCodeCountryService->execute($countryCode);

        $idsSlideUndefinedProgram = $this->getSlideUndefinedProgram($country);

        $idsSlideByDate = $this->getSlideByRangeDate($dateInit, $dateEnd, $country);

        $idsSlideByPeriodDate = $this->getSlideByPeriodYearDate($dateInit, $dateEnd, $country);

        $idsSlideByPeriodDay = $this->getSlideByPeriodWeekDay($dayInit, $dayEnd, $country);

        $merge = $idsSlideUndefinedProgram->merge($idsSlideByDate);
        $merge = $merge->merge($idsSlideByPeriodDate);
        $merge = $merge->merge($idsSlideByPeriodDay);

        return $merge;

    }

    private function getSlideUndefinedProgram($country): Collection
    {
        return Slide::query()
            ->join("program_slides as ps", "slides.id_slide", '=', "ps.id_slide")
            ->join('region_slide', 'slides.id_slide', '=', 'region_slide.id_slide')
            ->join('country_region', 'country_region.id_region', '=', 'region_slide.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', ModelConst::PROGRAM_RANGE_UNDEFINED)
            ->groupBy([ "slides.id_slide" ])
            ->whereNull('ps.deleted_at')
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_slide.deleted_at')
            ->getFromCache([ "slides.id_slide" ], $this->tagSlide);
    }

    private function getSlideByRangeDate($dateInit, $dateEnd, $country): Collection
    {
        if ($dateInit === null || $dateEnd === null) {
            return collect([]);
        }

        return Slide::query()
            ->join("program_slides as ps", "slides.id_slide", '=', "ps.id_slide")
            ->leftJoin("date_programs as dp", "ps.id_program", '=', "dp.id_program")
            ->join('region_slide', 'slides.id_slide', '=', 'region_slide.id_slide')
            ->join('country_region', 'country_region.id_region', '=', 'region_slide.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', ModelConst::PROGRAM_RANGE_DEFINED_DATE)
            ->where('period_current_program', ModelConst::PROGRAM_PERIOD_DATE)
            ->where('date_init', '<=', $dateInit)
            ->where('date_end', '>=', $dateEnd)
            ->whereNull('ps.deleted_at')
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_slide.deleted_at')
            ->groupBy([ "slides.id_slide" ])
            ->getFromCache([ "slides.id_slide" ], $this->tagSlide);
    }

    private function getSlideByPeriodYearDate($dateInit, $dateEnd, $country): Collection
    {
        if ($dateInit === null || $dateEnd === null) {
            return collect([]);
        }

        return Slide::query()
            ->join("program_slides as ps", "slides.id_slide", '=', "ps.id_slide")
            ->leftJoin("date_programs as dp", "ps.id_program", '=', "dp.id_program")
            ->join('region_slide', 'slides.id_slide', '=', 'region_slide.id_slide')
            ->join('country_region', 'country_region.id_region', '=', 'region_slide.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', ModelConst::PROGRAM_RANGE_CURRENT_DATE)
            ->where('type_current_program', ModelConst::PROGRAM_TYPE_CURRENT_YEAR)
            ->where('period_current_program', ModelConst::PROGRAM_PERIOD_DATE)
            ->whereRaw("DATE_FORMAT(date_init, '%m-%d') <= DATE_FORMAT('{$dateInit}', '%m-%d')")
            ->whereRaw("DATE_FORMAT(date_end, '%m-%d') >= DATE_FORMAT('{$dateEnd}', '%m-%d')")
            ->whereNull('ps.deleted_at')
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_slide.deleted_at')
            ->groupBy([ "slides.id_slide" ])
            ->getFromCache([ "slides.id_slide" ], $this->tagSlide);
    }

    private function getSlideByPeriodWeekDay($dayInit, $dayEnd, $country): Collection
    {
        return Slide::query()
            ->join("program_slides as ps", "slides.id_slide", '=', "ps.id_slide")
            ->leftJoin("date_programs as dp", "ps.id_program", '=', "dp.id_program")
            ->join('region_slide', 'slides.id_slide', '=', 'region_slide.id_slide')
            ->join('country_region', 'country_region.id_region', '=', 'region_slide.id_region')
            ->whereIn('country_region.id_country', $country)
            ->where('type_range_program', '=', ModelConst::PROGRAM_RANGE_CURRENT_DATE)
            ->where('type_current_program', ModelConst::PROGRAM_TYPE_CURRENT_WEEK)
            ->where('period_current_program', ModelConst::PROGRAM_PERIOD_DAY)
            ->where('day_init', '<=', $dayInit)
            ->where('day_end', '>=', $dayEnd)
            ->whereNull('ps.deleted_at')
            ->whereNull('country_region.deleted_at')
            ->whereNull('region_slide.deleted_at')
            ->groupBy([ "slides.id_slide" ])
            ->getFromCache([ "slides.id_slide" ], $this->tagSlide);
    }
}
