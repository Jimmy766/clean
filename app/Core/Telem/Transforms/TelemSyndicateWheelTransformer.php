<?php


namespace App\Core\Telem\Transforms;


use App\Core\Syndicates\Models\Syndicate;
use League\Fractal\TransformerAbstract;

class TelemSyndicateWheelTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Syndicate $syndicate) {
        return [
            'identifier' => (integer)$syndicate->id,
            'name' => (string)$syndicate->printable_name
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'id',
            'name' => 'printable_name'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'identifier',
            'printable_name' => 'name'
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

}
