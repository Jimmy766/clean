<?php


namespace App\Core\Syndicates\Transforms;


use App\Core\Syndicates\Models\SyndicateWheelsPicks;
use League\Fractal\TransformerAbstract;

class SyndicateWheelsPicksTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(SyndicateWheelsPicks $syndicate_wheels_picks) {
        return [
            'identifier' => (integer)$syndicate_wheels_picks->id,
            'label' => $syndicate_wheels_picks->label,
            'title' => $syndicate_wheels_picks->title,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
