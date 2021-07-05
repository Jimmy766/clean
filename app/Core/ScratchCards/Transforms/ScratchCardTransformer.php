<?php

    namespace App\Core\ScratchCards\Transforms;

    use App\Core\ScratchCards\Models\ScratchCard;
    use League\Fractal\TransformerAbstract;

    /**
     * @SWG\Definition(
     *     definition="ScratchCard",
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       format="int32",
     *       description="ID elements identifier",
     *       example="305"
     *     ),
     *     @SWG\Property(
     *       property="tag_name",
     *       type="string",
     *       description="Name of scratch card",
     *       example="#COPS_ROBBERS_NAME#"
     *     ),
     *     @SWG\Property(
     *       property="tag_info",
     *       type="string",
     *       description="Infomation of scratch card",
     *       example="#COPS_ROBBERS_INFO#"
     *     ),
     *     @SWG\Property(
     *       property="odds",
     *       type="number",
     *       format="float",
     *       description="Odds",
     *       example="1.0"
     *     ),
     *     @SWG\Property(
     *       property="cards_quantity",
     *       type="integer",
     *       description="Scratch card quantity",
     *       example="1"
     *     ),
     *     @SWG\Property(
     *       property="demo_url",
     *       type="string",
     *       description="Scratch card url to play demo game",
     *       example="https://www.example.com/scratch_cards?id=1&game_mode=demo"
     *     ),
     *     @SWG\Property(
     *       property="prices",
     *       description="Scratch card prices list",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/ScratchCardPrice")
     *     ),
     *     @SWG\Property(
     *       property="pay_table",
     *       description="Scratch card pay table",
     *       type="array",
     *       @SWG\Items(ref="#/definitions/ScratchCardPayTable")
     *     )
     *   ),
     */

    class ScratchCardTransformer extends TransformerAbstract
    {
        /**
         * A Fractal transformer.
         *
         * @return array
         */
        public static function transform(ScratchCard $scratch) {
            return [
                'identifier' => (integer)$scratch->id,
                'name' => $scratch->name,
                'tag_name' => $scratch->name_tag,
                'tag_info' => $scratch->info_tag,
                'odds' => $scratch->odds,
                'max_winn' => $scratch->max_win,
                'cards_quantity' => $scratch->cards_quantity,
                'demo_url' => $scratch->demo_url,
                'prices' => $scratch->prices_list,
                'pay_table' => $scratch->paytable_attribute,
            ];
        }

        /**
         * @param $index
         *
         * @return mixed|null
         */
        public static function originalAttribute($index) {
            $attributes = [
                'identifier' => 'prc_id',
                'name' => 'name',
                'tag_name' => 'tag_name',
                'code_desktop' => 'gamecode_desktop',
                'code_mobile' => 'gamecode_mobile',
                'odds' => 'odds',
                'cards_quantity' => 'cards_quantity',
                'branding_type' => 'branding_type',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }

        public static function transformedAttribute($index) {
            $attributes = [
                'prc_id' => 'identifier',
                'name' => 'name',
                'tag_name' => 'tag_name',
                'gamecode_desktop' => 'code_desktop',
                'gamecode_mobile' => 'code_mobile',
                'odds' => 'odds',
                'cards_quantity' => 'cards_quantity',
                'branding_type' => 'branding_type',
            ];
            return isset($attributes[ $index ]) ? $attributes[ $index ] : null;
        }
    }
