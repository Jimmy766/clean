<?php

namespace App\Core\Base\Services;

class TranslateArrayService
{

    /**
     * @param array  $array
     * @param string $attribute
     * @return array
     */
    public static function execute(array $array, $attribute = 'name')
    {
        $collection =collect($array);

        $collection = $collection->map( self::mapSetTranslationTransform($attribute) );

        return $collection->toArray();
    }

    private static function mapSetTranslationTransform($attribute): callable
    {
        return static function ($item, $key) use ($attribute) {
            if (array_key_exists($attribute, $item)) {
                $item[ $attribute ] = TranslateTextService::execute($item[ $attribute ]);
            }
            return $item;
        };
    }

}
