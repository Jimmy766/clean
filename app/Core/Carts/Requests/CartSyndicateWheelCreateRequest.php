<?php

namespace App\Core\Carts\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CartSyndicateWheelCreateRequest extends FormRequest
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
            'crt_id' => 'required|integer|exists:mysql_external.carts',
            'syndicate_id' => 'required|integer|exists:mysql_external.syndicate,id',
            'syndicate_prc_id' => 'required|integer|exists:mysql_external.syndicate_prices,prc_id',
            'ticket_byDraw' => 'required|integer|min:1|max:10',
            'syndicate_picks_id' => 'required'
        ];
    }
}
