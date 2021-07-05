<?php

namespace App\Core\Messages\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageTemplateLanguageRequest extends FormRequest
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
            'template_id'  => 'required|integer|exists:mysql_external.messages_templates,template_id',
            'site_id'  => 'required|integer|exists:mysql_external.sites,site_id',
            'language' => 'required|string',
            'subject' => 'required|string',
            'body' => 'required|string',
        ];
    }
}
