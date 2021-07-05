<?php

namespace App\Core\Clients\Transforms;

use App\Core\Clients\Models\Partner;
use League\Fractal\TransformerAbstract;

class PartnerTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Partner $partner) {
        return [
            'identifier' => (integer)$partner->_id,
            'name' => (string)$partner->name,
            'invalidate' => (bool)$partner->revoked,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => '_id',
            'name' => 'name',
            'invalidate' => 'revoked',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'name' => 'name',
            'revoked' => 'invalidate',
            ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
