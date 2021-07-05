<?php

    namespace App\Core\Memberships\Transforms;

    use App\Core\Memberships\Models\Membership;
    use League\Fractal\TransformerAbstract;

    /**
     * @SWG\Definition(
     *     definition="MembershipUser",
     *     required={"identifier","name","date","description", "level"},
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       description="ID Draw identifier",
     *       example="2"
     *     ),
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       description="Name of Membership",
     *       example="GOLD"
     *     ),
     *     @SWG\Property(
     *       property="description",
     *       type="string",
     *       description="Membership Description",
     *       example="#GOLDMEMBERSHIP#"
     *     ),
     *     @SWG\Property(
     *       property="level",
     *       type="integer",
     *       description="Level of Membership",
     *       example="2"
     *     ),
     *     @SWG\Property(
     *       property="price",
     *       description="Price of Membership",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/MembershipPrice"),
     *     ),
     *  )
     */
    class MembershipUserTransformer extends TransformerAbstract {
        /**
         * A Fractal transformer.
         *
         * @return array
         */
        public static function transform(Membership $membership) {
            return [
                'identifier' => (integer)$membership->id,
                'name' => (string)$membership->name,
                'description' => (string)$membership->tag_name,
                'level' => $membership->level,
                'price' => $membership->price,
            ];
        }

        public static function originalAttribute($index) {
            $attributes = [
                'identifier' => 'id',
                'name' => 'name',
                'description' => 'description',
                'level' => 'level',
                'active' => 'active',

            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }

        public static function transformedAttribute($index) {
            $attributes = [
                'id' => 'identifier',
                'name' => 'name',
                'description' => 'description',
                'level' => 'level',
                'active' => 'active',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }
    }
