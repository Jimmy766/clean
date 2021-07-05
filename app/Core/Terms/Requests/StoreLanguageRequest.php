<?php

namespace App\Core\Terms\Requests;

use App\Core\Base\Requests\Rules\CheckUniqueAttributeModel;
use App\Core\Terms\Models\Language;
use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
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
            'name' => 'required|string',
            'code' => [
                'required',
                'string',
                new CheckUniqueAttributeModel(new Language, request()->language)
            ],
        ];
    }
}
