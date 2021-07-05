<?php

namespace App\Core\Rapi\Services;

use App\Core\Base\Traits\ApiResponser;
use App\Core\Lotteries\Services\AllLotteriesActiveService;
use App\Core\Raffles\Services\AllRafflesActiveService;
use App\Core\ScratchCards\Services\AllScratchCardsActiveService;
use App\Core\Syndicates\Services\AllSyndicatesActiveService;
use Illuminate\Validation\ValidationException;

/**
 * Get all products actives
 * Class GlobalActiveService
 * @package App\Services
 */
class GlobalActiveService
{

    use ApiResponser;
    /**
     * @var AllLotteriesActiveService
     */
    private $allLotteriesActiveService;
    /**
     * @var AllSyndicatesActiveService
     */
    private $allSyndicatesActiveService;
    /**
     * @var AllRafflesActiveService
     */
    private $allRafflesActiveService;
    /**
     * @var AllScratchCardsActiveService
     */
    private $allScratchCardsActiveService;

    public function __construct(
        AllLotteriesActiveService $allLotteriesActiveService,
        AllSyndicatesActiveService $allSyndicatesActiveService,
        AllRafflesActiveService $allRafflesActiveService,
        AllScratchCardsActiveService $allScratchCardsActiveService
    ) {
        $this->allLotteriesActiveService    = $allLotteriesActiveService;
        $this->allSyndicatesActiveService   = $allSyndicatesActiveService;
        $this->allRafflesActiveService      = $allRafflesActiveService;
        $this->allScratchCardsActiveService = $allScratchCardsActiveService;
    }

    /**
     * @param $request
     * @return array
     * @throws ValidationException
     */
    public function execute($request): array
    {
        /*===========LOTTERIES===========*/
        $lotteries = $this->allLotteriesActiveService->execute();
        $lotteries = $this->setTransformToCollection($lotteries);
        $lotteries = $this->getInternalDataFromTransform($lotteries);
        $arrayReturn[ 'lotteries' ] = $lotteries;

        /*===========SYNDICATES===========*/
        $syndicates = $this->allSyndicatesActiveService->execute();
        $syndicates = $this->setTransformToCollection($syndicates);
        $syndicates = $this->getInternalDataFromTransform($syndicates);
        $arrayReturn[ 'syndicates' ] = $syndicates;

        /*===========RAFFLES===========*/
        $raffles = $this->allRafflesActiveService->execute($request);
        $raffles = $this->setTransformToCollection($raffles);
        $raffles = $this->getInternalDataFromTransform($raffles);
        $arrayReturn[ 'raffles' ] = $raffles;

        /*===========SCRATCH_CARDS===========*/
        $scratches = $this->allScratchCardsActiveService->execute();
        $scratches = $this->setTransformToCollection($scratches);
        $scratches = $this->getInternalDataFromTransform($scratches);
        $arrayReturn[ 'scratches' ] = $scratches;

        return $arrayReturn;
    }

}
