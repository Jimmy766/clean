<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Base\Traits\UtilsFormatText;
use League\Fractal\TransformerAbstract;

class RoutingFriendlyTransformer extends TransformerAbstract
{
    use UtilsFormatText;
    /**
     * A Fractal transformer.
     *
     * @return array
     */

    public static function transform($routingFriendly)
    {
        return [
            'id'           => $routingFriendly->id,
            'id_product'   => $routingFriendly->element_id,
            'type_product' => $routingFriendly->element_type,
            'partial_path' => (new self())->convertTextCharset($routingFriendly->element_name),
            'language'     => $routingFriendly->lang,
        ];
    }
}
