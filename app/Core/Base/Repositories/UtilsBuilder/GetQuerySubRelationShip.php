<?php

namespace App\Core\Base\Repositories\UtilsBuilder;

/**
 * Class GetQuerySubRelationShip
 * @package App\Core\Base\Repositories\UtilsBuilder
 */
class GetQuerySubRelationShip
{
    public static function execute($query, array $relationsShip)
    {
        $collectionRelationsShip = collect($relationsShip);
        $actuallyRelationShip    = [];
        $collectionRelationsShip = $collectionRelationsShip->map(
            self::mapGetParameterAndQueryRelationTransform(
                $query, $actuallyRelationShip
            )
        );

        return $collectionRelationsShip->toJson();
    }

    /**
     * @param $query
     * @param $actuallyRelationShip
     * @return callable
     */
    private static function mapGetParameterAndQueryRelationTransform($query, &$actuallyRelationShip): callable
    {
        return static function ($item, $key) use (&$actuallyRelationShip, $query) {
            // set variable persistent query from relation
            if ($actuallyRelationShip !== []) {
                $query = $actuallyRelationShip->getQuery();
            }

            $queryRelationsShip = $query->getRelation($item);
            $querySql           = $queryRelationsShip->toSql();
            $parameters         = json_encode($queryRelationsShip->getBindings());
            $actuallyRelationShip = $queryRelationsShip;
            return "{$querySql}{$parameters}";
        };
    }

}
