<?php

namespace App\Core\Base\Repositories\UtilsBuilder;

/**
 * Class GetQueryRelationShip
 * @package App\Core\Base\Repositories\UtilsBuilder
 */
class GetQueryRelationShip
{
    /**
     * @param       $query
     * @param array $relationsShip
     * @return string
     */
    public static function execute($query, array $relationsShip)
    {
        $collectionRelationsShip = collect($relationsShip);
        $collectionRelationsShip = $collectionRelationsShip->map(
            self::mapGetParameterAndQueryRelationTransform(
                $query
            )
        );

        return $collectionRelationsShip->toJson();

    }

    /**
     * @param $query
     * @return callable
     */
    private static function mapGetParameterAndQueryRelationTransform($query): callable
    {
        return static function ($item) use ($query) {
            $explodeItem = explode('.', $item);

            if (count($explodeItem) > 1) {
                return GetQuerySubRelationShip::execute($query, $explodeItem);
            }

            $queryRelationsShip = $query->getRelation($item);
            $querySql           = $queryRelationsShip->toSql();
            $parameters         = json_encode($queryRelationsShip->getBindings());
            return "{$querySql}{$parameters}";
        };
    }

}
