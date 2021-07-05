<?php

namespace App\Core\Rapi\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendContactRequest extends FormRequest
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
            'name'      => 'required|string|max:250',
            'last_name' => 'required|string|max:250',
            'email'     => [
                'required',
                'max:250',
                'email',
            ],
            'notes'     => 'nullable|string|max:500',
        ];
    }
}
