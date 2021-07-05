<?php

namespace App\Core\Users\Requests;

use App\Core\Users\Requests\Rules\CheckDuplicateUserRule;
use App\Core\Users\Requests\Rules\CheckRegistrationPeriodUserRule;
use App\Core\Users\Requests\Rules\CheckUsrEmailUniqueUserRule;
use Illuminate\Foundation\Http\FormRequest;

class XmlUserStoreRequest extends FormRequest
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
            'usr_title'            => 'integer|exists:mysql_external.users_title,id',
            'usr_name'             => 'required|string|max:255',
            'usr_email'            => [
                'required',
                'string',
                'max:255',
                new CheckUsrEmailUniqueUserRule(),
                new CheckRegistrationPeriodUserRule(),
                new CheckDuplicateUserRule(),
            ],
            'usr_password'         => 'required|string|min:6',
            'usr_lastname'         => 'required|string|max:255',
            'usr_phone'            => 'required|string|max:150',
            'usr_language'         => 'required|string|max:45',
            'site_id'              => 'required|integer|max:100000',
            'utm_source'           => 'string|max:255',
            'utm_campaign'         => 'string|max:255',
            'country_id'           => 'required|integer|exists:mysql_external.countries',
            'utm_medium'           => 'string|max:255',
            'utm_content'          => 'string|max:255',
            'utm_term'             => 'string|max:255',
            'usr_cookies'          => 'string|max:255',
            'usr_track'            => 'string|max:255',
            'usr_cookies_data4'    => 'string|max:255',
            'usr_cookies_data5'    => 'string|max:255',
            'usr_cookies_data6'    => 'string|max:255',
            'usr_internal_account' => 'integer|max:1|min:0',
        ];
    }
}
