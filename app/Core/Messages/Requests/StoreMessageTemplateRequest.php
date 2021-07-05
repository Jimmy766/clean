<?php

namespace App\Core\Messages\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageTemplateRequest extends FormRequest
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
            'name'  => 'required|string',
            'type' => 'required|integer|min:1',
            'system' => 'required|integer|min:1',
            'category' => 'required|integer|min:1',
        ];
    }
}
