<?php

namespace App\Core\Lotteries\Services;

use App\Core\Base\Traits\ApiResponser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class AllLotteriesActiveService
{
    use ApiResponser;

    /**
     * @var GetLotteriesAndCheckInsureBlackListService
     */
    private $getLotteriesAndCheckInsureBlackListService;
    /**
     * @var CheckLotteriesNotExceedLimitJackpotService
     */
    private $checkLotteriesNotExceedLimitJackpotService;

    public function __construct(
        GetLotteriesAndCheckInsureBlackListService $getLotteriesAndCheckInsureBlackListService,
        CheckLotteriesNotExceedLimitJackpotService $checkLotteriesNotExceedLimitJackpotService
    ) {
        $this->getLotteriesAndCheckInsureBlackListService = $getLotteriesAndCheckInsureBlackListService;
        $this->checkLotteriesNotExceedLimitJackpotService = $checkLotteriesNotExceedLimitJackpotService;
    }

    /**
     * @param array $idsLotteries
     * @param array $relations
     * @return Collection
     */
    public function execute(array $idsLotteries = [] , array $relations = []): Collection
    {
        $arrayProductsAvailable = $idsLotteries;
        if (count($idsLotteries) === 0) {
            $arrayProductsAvailable = self::client_lotteries(1, 0)
                ->pluck('product_id')
                ->toArray();
        }

        $relationsLocal = [
            'draw_active.lottery',
            'lotteriesBoostedJackpot.lotteriesModifier',
            'routingFriendly',
        ];

        $relations = array_merge($relations, $relationsLocal);

        $idUser    = Auth::id();
        $lotteries = $this->getLotteriesAndCheckInsureBlackListService->execute(
            $arrayProductsAvailable,
            null,
            $relations,
            $idUser
        );

        $lotteries = $this->checkLotteriesNotExceedLimitJackpotService->execute($lotteries);

        return $lotteries;
    }

}
