<?php

namespace App\Core\Clients\Transforms;

use App\Core\Clients\Models\Client;
use League\Fractal\TransformerAbstract;

class ClientTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Client $client) {
        return [
            'identifier' => (integer)$client->id,
            'name' => (string)$client->name,
            'user' => $client->user_attributes,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'name' => 'name',
            'user' => 'user_id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'name' => 'name',
            'user_id' => 'user',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
