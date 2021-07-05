<?php

namespace App\Core\Base\Traits;


use App\Core\Carts\Models\CartSubscriptionPick;

trait PicksValidation
{
    public function validatePick($pick_balls, $pick_extra_balls, $lottery) {
        $count_pick_balls = 0;
        foreach ($pick_balls as $pb) {
            if (!is_numeric($pb))
                return $this->errorResponse(trans('lang.pick_ball_only_integer'), 422);
            if ($pb > $lottery->lot_maxNum)
                return $this->errorResponse(trans('lang.pick_ball_max'), 422);
            if ($pb <= 0)
                return $this->errorResponse(trans('lang.pick_ball_min'), 422);
            $count_pick_balls++;
        }
        if ($lottery->lot_pick_balls != $count_pick_balls)
            return $this->errorResponse(trans('lang.pick_ball_qty'), 422);
        if ($count_pick_balls != count(array_unique($pick_balls)))
            return $this->errorResponse(trans('lang.distinct_pick_balls'), 422);


        $count_pick_extra_balls = 0;
        if ($pick_extra_balls) {
            foreach ($pick_extra_balls as $peb) {
                if (!is_numeric($peb))
                    return $this->errorResponse(trans('lang.extra_pick_ball_only_integer'), 422);
                if ($peb > $lottery->lot_extra_maxNum)
                    return $this->errorResponse(trans('lang.extra_pick_ball_max'), 422);
                if ($pb < $lottery->lot_extra_startNum)
                    return $this->errorResponse(trans('lang.extra_pick_ball_min'), 422);
                $count_pick_extra_balls++;
            }
            if ($lottery->lot_pick_extra != $count_pick_extra_balls)
                return $this->errorResponse(trans('lang.extra_pick_ball_qty'), 422);
            if ($count_pick_extra_balls != count(array_unique($pick_extra_balls)))
                return $this->errorResponse(trans('lang.distinct_extra_pick_balls'), 422);
        }
        return false;
    }

    public function validatePicks($request, $lottery, &$cart_subscription_picks) {
        $pick_lines_qty = $request->cts_ticket_byDraw * $lottery->slip_min_lines;
        $pick_balls_array = $request->pick_balls;

        if (count($pick_balls_array) < $pick_lines_qty) {
            return $this->errorResponse(trans('lang.pick_ball_line_qty'), 422);
        }
        if ($lottery->lot_pick_extra > 0) {
            $rules = [
                'pick_extra_balls' => 'required|array',
            ];
            $this->validate($request, $rules);
            $pick_extra_balls_array = $request->pick_extra_balls;
            if (count($pick_extra_balls_array) < $pick_lines_qty) {
                return $this->errorResponse(trans('lang.extra_pick_ball_line_qty'), 422);
            }
        } else {
            $pick_extra_balls_array = null;
        }

        for ($j = 0; $j < count($pick_balls_array); $j++) {
            if (!is_string($pick_balls_array[$j])) {
                return $this->errorResponse(trans('lang.pick_ball_not_string'), 422);
            }
            $pick_balls = explode(',', $pick_balls_array[$j]);
            if ($lottery->lot_pick_extra > 0 && !is_string($pick_extra_balls_array[$j])) {
                return $this->errorResponse(trans('lang.extra_pick_ball_not_string'), 422);
            }
            $pick_extra_balls = $pick_extra_balls_array ? explode(',', $pick_extra_balls_array[$j]) : null;
            $validation = $this->validatePick($pick_balls, $pick_extra_balls, $lottery);
            if ($validation)
                return $validation;

            sort($pick_balls);
            if ($pick_extra_balls)
                sort($pick_extra_balls);

            $cart_subscription_pick = new CartSubscriptionPick();

            for ($i = 1; $i <= $lottery->lot_pick_balls; $i++) {
                $cart_subscription_pick->{"ctpck_" . $i} = $pick_balls[$i - 1];
            }
            for ($i; $i <= 12; $i++) {
                $cart_subscription_pick->{"ctpck_" . $i} = isset($pick_extra_balls[$i - $lottery->lot_pick_balls - 1])
                    ? $pick_extra_balls[$i - $lottery->lot_pick_balls - 1] : 0;
            }
            $cart_subscription_picks [] = $cart_subscription_pick;
        }
        return false;

    }
}
