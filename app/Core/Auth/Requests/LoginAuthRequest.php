<?php

namespace App\Core\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 *
 * @package App\Core\User\Request
 */
class LoginAuthRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'email'    => 'nullable|max:250',
            'password' => 'required|max:250',
            'remember' => 'nullable',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }

    public function attributes()
    {
        return [
            'email'    => __('Email'),
            'password' => __('Password'),
        ];

    }
}

