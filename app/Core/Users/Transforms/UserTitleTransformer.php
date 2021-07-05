<?php

namespace App\Core\Users\Transforms;

use App\Core\Users\Models\UserTitle;
use League\Fractal\TransformerAbstract;

class UserTitleTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(UserTitle $user_title) {
        return [
            'identifier' => (integer)$user_title->id,
            'name' => (string)$user_title->code,
            'gender' => (string)$user_title->user_gender,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'name' => 'code',
            'gender' => 'gender',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'code' => 'name',
            'gender' => 'gender',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
