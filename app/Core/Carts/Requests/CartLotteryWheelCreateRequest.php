<?php

namespace App\Core\Carts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartLotteryWheelCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'crt_id' => 'required|integer|exists:mysql_external.carts,crt_id',
            'prc_id' => 'required|integer|exists:mysql_external.prices,prc_id',
            'lot_id' => 'required|integer|exists:mysql_external.lotteries,lot_id',
            'cts_ticket_byDraw' => 'required|integer|min:1|max:10',
            'cts_pck_type' => 'required|integer|in:1,3',
            'pick_balls' => 'required_if:cts_pck_type,3|array'
        ];
    }
}
