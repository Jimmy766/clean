<?php

namespace App\Core\Slides\Services;

use App\Core\Clients\Services\IP2LocTrillonario;
use App\Core\Countries\Services\ConvertCurrencyCountryService;
use Carbon\Carbon;
use DateTimeZone;

/**
 * Class AllSlideAvailableService
 * @package App\Services
 */
class AllSlidesAvailableService
{
    /**
     * @var GetSlideByDateOrDayService
     */
    private $getSlideByDateOrDayService;
    /**
     * @var GetSlideByIdsService
     */
    private $getSlideByIdsService;
    /**
     * @var ConvertCurrencyCountryService
     */
    private $convertCurrencyCountryService;

    public function __construct(
        GetSlideByDateOrDayService $getSlideByDateOrDayService,
        GetSlideByIdsService $getSlideByIdsService,
        ConvertCurrencyCountryService $convertCurrencyCountryService
    ) {
        $this->getSlideByDateOrDayService    = $getSlideByDateOrDayService;
        $this->getSlideByIdsService          = $getSlideByIdsService;
        $this->convertCurrencyCountryService = $convertCurrencyCountryService;
    }

    /**
     * @return array
     */
    public function execute(): array
    {
        [ $codeCountry ] = IP2LocTrillonario::get_iso('');
        $timeZone = DateTimeZone::listIdentifiers(DateTimeZone::UTC, $codeCountry);

        $date = new Carbon();
        $date->setTimeZone($timeZone[ 0 ]);
        $day = $date->dayOfWeek;

        $idsSlideByDate = $this->getSlideByDateOrDayService->execute(
            $date->toDateString(),
            $day,
            $codeCountry
        );

        $slides = $this->getSlideByIdsService->execute($idsSlideByDate);

        $arrayReturn[ 'codeCountry' ] = $codeCountry;
        $arrayReturn[ 'date' ]        = $date->toDateString();
        $arrayReturn[ 'day' ]         = $day;
        $arrayReturn[ 'slides' ]      = $this->convertCurrencyCountryService->execute(
            $slides,
            $codeCountry
        );

        return $arrayReturn;
    }

}
