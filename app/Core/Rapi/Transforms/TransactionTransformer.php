<?php

namespace App\Core\Rapi\Transforms;

use App\Core\Rapi\Models\Transaction;
use League\Fractal\TransformerAbstract;

/**
 * @SWG\Definition(
 *     definition="Transaction",
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Transaction identifier",
 *       example="1",
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Transaction name",
 *       example="New Order",
 *     ),
 *     @SWG\Property(
 *       property="type",
 *       type="string",
 *       description="Transaction type",
 *       example="order",
 *     ),
 *     @SWG\Property(
 *       property="attribute_list",
 *       type="array",
 *       description="Transactions list",
 *       @SWG\Items(
 *         @SWG\Property(
 *           property="order",
 *           type="integer",
 *           description="Order Id",
 *           example="1",
 *         ),
 *         @SWG\Property(
 *           property="pay_method",
 *           type="integer",
 *           description="Payment Method",
 *           example="Visa",
 *         ),
 *         @SWG\Property(
 *           property="date",
 *           type="string",
 *           format="date-time",
 *           description="Order date",
 *           example="2013-12-06 09:36:50",
 *         ),
 *         @SWG\Property(
 *           property="currency",
 *           type="string",
 *           description="Order currency",
 *           example="USD",
 *         ),
 *         @SWG\Property(
 *           property="amount",
 *           type="number",
 *           format="float",
 *           description="Order amount",
 *           example="15.2",
 *         ),
 *       ),
 *     ),
 *  ),
 */

class TransactionTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Transaction $transaction) {
        return [
            'identifier' => (integer)$transaction->type_id,
            'name' => (string)$transaction->name,
            'type' => (string)$transaction->type,
            'attributes_list' => $transaction->attributes_list,
        ];
    }

    public static function originalAttribute($index) {
        $attributes = [
            'identifier' => 'type_id',
            'name' => 'name',
            'type' => 'type',
            'attributes_list' => 'attributes_list',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'type_id' => 'identifier',
            'name' => 'name',
            'type' => 'type',
            'attributes_list' => 'attributes_list',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
