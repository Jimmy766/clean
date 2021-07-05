<?php

namespace App\Core\Lotteries\Services;

use App\Core\Base\Classes\ModelConst;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Base\Traits\Encoding;
use App\Core\Base\Traits\PicksValidation;
use App\Core\Base\Traits\Utils;
use Illuminate\Foundation\Validation\ValidatesRequests;

class GenerateQuickPicksService
{

    use ApiResponser;
    use Encoding;
    use Utils;
    use ValidatesRequests;
    use PicksValidation;

    /**
     * @param $request
     * @param $lottery
     * @return array
     */
    public function execute($request, $lottery): array
    {
        $cartSubscriptionPicks = [];
        if ($request->cts_pck_type !== 3 && $lottery->lot_pick_type == 1) {
            if ($lottery->lot_id !== ModelConst::MAX_LOTTO_ID_LOTTERY) {
                $request->cts_pck_type = 2;
                $cartSubscriptionP = LotteryService::getInstance()
                    ->generateUserPicks($lottery, $request->cts_ticket_byDraw);

                $request->merge(
                    [
                        "pick_balls"       => $cartSubscriptionP[ "balls" ],
                        "pick_extra_balls" => $cartSubscriptionP[ "extra" ],
                    ]
                );
                /** Los valida y también los agrega a la BD */
                $validation = $this->validatePicks($request, $lottery, $cartSubscriptionPicks);
                if ($validation) {
                    return [$request, $cartSubscriptionPicks, $validation];
                }

            }
        }

        return [$request, $cartSubscriptionPicks, null];
    }

}
