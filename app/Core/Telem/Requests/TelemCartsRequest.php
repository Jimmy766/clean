<?php

namespace App\Core\Telem\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelemCartsRequest extends FormRequest
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
            "campaign_id" => "required",
            "admin_user_id" => "required",
            "usr_id" => "required",
            "crt_id" => "required"
        ];
    }
}
