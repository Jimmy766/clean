<?php

namespace App\Core\Casino\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CasinoCategoryRequest extends FormRequest
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
            'provider' => 'nullable|integer|min:1', // 1=MultiSlot, 2=Oryx, 3=RedTiger
            'live' => 'nullable|integer|in:0,1' // 0=Casino ,1=Live Casino
        ];
    }
}
