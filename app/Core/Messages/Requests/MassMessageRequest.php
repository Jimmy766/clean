<?php

namespace App\Core\Messages\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MassMessageRequest extends FormRequest
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
            'system' => 'required|integer',
            'template' => 'required|integer',
            'send_date' => 'required|date',
            'final_date' => 'date',
            'csv_file' => 'required|file',
        ];
    }
}
