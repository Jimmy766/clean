<?php

namespace App\Core\Base\Services;

class GetAllValuesFromHeaderService
{

    public static function execute($request): \Illuminate\Support\Collection
    {
        $headers = $request->headers->all();
        $headers = collect($headers);

        $headers = $headers->map(self::mapGetValuesHeadersTransform());

        return $headers;
    }

    private static function mapGetValuesHeadersTransform(): callable
    {
        return static function ($item, $key) {
            return collect($item)->first();
        };
    }
}
