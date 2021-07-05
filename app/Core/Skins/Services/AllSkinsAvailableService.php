<?php

namespace App\Core\Skins\Services;

use App\Core\Clients\Services\IP2LocTrillonario;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Support\Collection;

/**
 * Class AllSkinsAvailableService
 * @package App\Services
 */
class AllSkinsAvailableService
{
    /**
     * @var GetSkinByDateOrDayService
     */
    private $getSkinByDateOrDayService;
    /**
     * @var GetSkinByIdsService
     */
    private $getSkinByIdsService;

    public function __construct(
        GetSkinByDateOrDayService $getSkinByDateOrDayService,
        GetSkinByIdsService $getSkinByIdsService
    ) {
        $this->getSkinByDateOrDayService = $getSkinByDateOrDayService;
        $this->getSkinByIdsService       = $getSkinByIdsService;
    }

    /**
     * @return Collection
     */
    public function execute(): Collection
    {
        [ $codeCountry ] = IP2LocTrillonario::get_iso('');
        $timeZone = DateTimeZone::listIdentifiers(
            DateTimeZone::UTC,
            $codeCountry
        );

        $date = new Carbon();
        $date->setTimeZone($timeZone[ 0 ]);
        $day = $date->dayOfWeek;

        $idsSkinByDate = $this->getSkinByDateOrDayService->execute(
            $date->toDateString(),
            $day,
            $codeCountry
        );

        return $this->getSkinByIdsService->execute($idsSkinByDate);
    }

}
