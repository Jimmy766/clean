<?php

namespace App\Core\Telem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelemPricesRequest extends FormRequest
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
            "product" => "required",
            "user_group" => "required"
        ];
    }
}
