<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Site;
use App\Core\Base\Traits\UtilsFormatText;
use League\Fractal\TransformerAbstract;
use phpDocumentor\Reflection\Types\This;

/**
 *   @SWG\Definition(
 *     definition="Site",
 *     required={"identifier","name","iso"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       format="int32",
 *       description="ID elements identifier",
 *       example="998"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of site",
 *       example="Trillonario"
 *     ),
 *     @SWG\Property(
 *       property="url",
 *       type="string",
 *       description="Url of site",
 *       example="http://www.trillonario.com"
 *     ),
 *     @SWG\Property(
 *       property="url_ssl",
 *       type="string",
 *       description="Security url",
 *       example="http://www.trillonario.com"
 *     ),
 *     @SWG\Property(
 *       property="lang",
 *       type="string",
 *       description="Language of site",
 *       example="es"
 *     ),
 *     @SWG\Property(
 *       property="mail_client_info",
 *       type="string",
 *       description="Email site contact",
 *       example="clientes@trillonario.com",
 *     ),
 *     @SWG\Property(
 *       property="description",
 *       type="string",
 *       description="Site description",
 *       example="Trillonario (998)"
 *     ),
 *     @SWG\Property(
 *       property="sytem",
 *       type="object",
 *       allOf={
 *         @SWG\Schema(ref="#/definitions/System"),
 *       }
 *     ),
 *   ),
 */

class SiteTransformer extends TransformerAbstract
{
	use UtilsFormatText;
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform(Site $site) {
        return [
            'identifier'  => (integer)$site->site_id,
            'name'        => (string)$site->site_name,
            'url'         => (string)$site->site_url,
            'url_ssl'     => (string)$site->site_url_https,
            'lang'        => (string)$site->site_lang,
            'mail_info'   => (new SiteTransformer)->convertTextCharset((string)$site->site_info_mail),
            'description' => (string)$site->description,
            'system'      => $site->system_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'site_id',
            'name' => 'site_name',
            'url' => 'site_url',
            'url_ssl' => 'site_url_https',
            'lang' => 'site_lang',
            'mail_info' => 'site_info_mail',
            'description' => 'description',
            'system' => 'system_attributes',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'site_id' => 'identifier',
            'site_name' => 'name',
            'site_url' => 'url',
            'site_url_https' => 'url_ssl',
            'site_lang' => 'lang',
            'site_info_mail' => 'mail_info',
            'description' => 'description',
            'system_attributes' => 'system',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
