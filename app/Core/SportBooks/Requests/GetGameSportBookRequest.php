<?php

namespace App\Core\SportBooks\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetGameSportBookRequest extends FormRequest
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

    public function rules()
    {
        return [
            'language'           => 'required|string',
        ];
    }
}
