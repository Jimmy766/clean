<?php

namespace App\Core\Users\Transforms;

use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="User",
 *     required={"name","lastname","email","country", "site"},
 *     @SWG\Property(
 *       property="identifier",
 *       description="User identifier",
 *       type="integer",
 *       example="1234"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       description="Name of user",
 *       type="string",
 *       example="Pepe"
 *     ),
 *     @SWG\Property(
 *       property="last_name",
 *       description="Lastname of user",
 *       type="string",
 *       example="Perez"
 *     ),
 *     @SWG\Property(
 *       property="email",
 *       description="User Email",
 *       type="string",
 *       example="pp1234@something.com"
 *     ),
 *     @SWG\Property(
 *       property="phone",
 *       description="User phone",
 *       type="string",
 *       example="98765432"
 *     ),
 *     @SWG\Property(
 *       property="mobile",
 *       description="User mobile",
 *       type="string",
 *       example="98765432"
 *     ),
 *     @SWG\Property(
 *       property="address1",
 *       description="User address 1",
 *       type="string",
 *       example="123 Street"
 *     ),
 *     @SWG\Property(
 *       property="address2",
 *       description="User address 2",
 *       type="string",
 *       example="456 Street"
 *     ),
 *     @SWG\Property(
 *       property="city",
 *       description="City",
 *       type="string",
 *       example="Montevideo"
 *     ),
 *     @SWG\Property(
 *       property="zipcode",
 *       description="Zipcode",
 *       type="string",
 *       example="11200"
 *     ),
 *     @SWG\Property(
 *       property="usr_ssn",
 *       description="User ssn",
 *       type="string",
 *       example="1234567"
 *     ),
 *     @SWG\Property(
 *       property="ssn_type",
 *       description="User ssn type",
 *       type="string",
 *       example="7"
 *     ),
 *     @SWG\Property(
 *       property="language",
 *       description="User language",
 *       type="string",
 *       example="en-us"
 *     ),
 *     @SWG\Property(
 *       property="altEmail",
 *       description="Alternative user email",
 *       type="string",
 *       example="lolo@lolo.com"
 *     ),
 *     @SWG\Property(
 *       property="currency",
 *       description="currency user",
 *       type="string",
 *       example="USD"
 *     ),
 *     @SWG\Property(
 *       property="quick_deposit",
 *       description="Quick deposit chance",
 *       type="boolean",
 *       example="true"
 *     ),
 *     @SWG\Property(
 *       property="state",
 *       description="State of user",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/State"),
 *       }
 *     ),
 *     @SWG\Property(
 *       property="site",
 *       description="",
 *      type="object",
 *      allOf={
 *        @SWG\Schema(ref="#/definitions/Site"),
 *      }
 *     ),@SWG\Property(
 *       property="pixels",
 *       type="array",
 *       description="Pixels",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="tag",
 *           type="string",
 *           description="Pixel Tag",
 *           example="&lt;img src=&quot;https://thopeciveers.org/p.ashx?a=161&amp;f=pb&amp;t=123&quot; width= &quot;1&quot; height= &quot;1&quot; border=&quot;0&quot;&gt;",
 *         ),
 *       ),
 *     ),
 *  ),
 */

class UserTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform($user) {
        return [
            'identifier' => $user->usr_id,
            'title' => $user->title,
            'name' => (string)$user->usr_name,
            'last_name' => (string)$user->usr_lastname,
            'email' => (string)$user->usr_email,
            'birthdate' => $user->usr_birthdate,
            'phone' => (string)$user->usr_phone,
            'mobile' => (string)$user->usr_mobile,
            'address1' => (string)$user->usr_address1,
            'address2' => (string)$user->usr_address2,
            'city' => (string)$user->usr_city,
            'zipcode' => (string)$user->usr_zipcode,
            'ssn' => (string)$user->usr_ssn,
            'ssn_type' => (string)$user->usr_ssn_type,
            'language' => (string)$user->usr_language,
            'altEmail' => (string)$user->usr_altEmail,
            'currency' => $user->curr_code,
            'quick_deposit' => $user->quick_deposit,
            'state' => $user->state_attributes,
            'site' => $user->site_attributes,
            'pixels' => $user->pixels,
            'promo_code' => $user->promo_code,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'title' => 'usr_title',
            'name' => 'usr_name',
            'last_name' => 'usr_lastname',
            'email' => 'usr_email',
            'password' => 'usr_password',
            'password_confirmation' => 'usr_password_confirmation',
            'phone' => 'usr_phone',
            'mobile' => 'usr_mobile',
            'address1' => 'usr_address1',
            'address2' => 'usr_address2',
            'city' => 'usr_city',
            'zipcode' => 'usr_zipcode',
            'ssn' => 'usr_ssn',
            'ssn_type' => 'usr_ssn_type',
            'language' => 'usr_language',
            'altEmail' => 'usr_altEmail',
            'utm_source'=> 'utm_source',
            'utm_campaign'=> 'utm_campaign',
            'utm_medium'=> 'utm_medium',
            'utm_content'=> 'utm_content',
            'utm_term'=> 'utm_term',
            'state' => 'usr_state',
            'country' => 'country_id',
            'birthdate' => 'usr_birthdate',
            'cookies' => 'usr_cookies',
            'track' => 'usr_track',
            'cookies_data1' => 'usr_cookies_data4',
            'cookies_data2' => 'usr_cookies_data5',
            'cookies_data3' => 'usr_cookies_data6',
            'usr_internal_account' => 'usr_internal_account',
            'amount' => 'amount'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'usr_title' => 'title',
            'usr_name' => 'name',
            'usr_lastname' => 'last_name',
            'usr_email' => 'email',
            'usr_password' => 'password',
            'usr_password_confirmation' => 'password_confirmation',
            'usr_phone' => 'phone',
            'usr_mobile' => 'mobile',
            'usr_address1' => 'address1',
            'usr_address2' => 'address2',
            'usr_city' => 'city',
            'usr_zipcode' => 'zipcode',
            'usr_ssn' => 'ssn',
            'usr_ssn_type' => 'ssn_type',
            'usr_language' => 'language',
            'usr_altEmail' => 'altEmail',
            'utm_source'=> 'utm_source',
            'utm_campaign'=> 'utm_campaign',
            'utm_medium'=> 'utm_medium',
            'utm_content'=> 'utm_content',
            'utm_term'=> 'utm_term',
            'usr_state' => 'state',
            'country_id' => 'country',
            'usr_birthdate' => 'birthdate',
            'usr_cookies' => 'cookies',
            'usr_track' => 'track',
            'usr_cookies_data4' => 'cookies_data1',
            'usr_cookies_data5' => 'cookies_data2',
            'usr_cookies_data6' => 'cookies_data3',
            'usr_internal_account' => 'usr_internal_account',
            'amount' => 'amount'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
