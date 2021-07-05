<?php

    namespace App\Core\Reports\Transforms;

    use App\Core\Reports\Models\ReportType;
    use League\Fractal\TransformerAbstract;

    /**
     * @SWG\Definition(
     *     definition="ReportsType",
     *     required={"name"},
     *     @SWG\Property(
     *       property="identifier",
     *       type="integer",
     *       format="int32",
     *       description="Type report identifier",
     *       example="111111"
     *     ),
     *     @SWG\Property(
     *       property="name",
     *       type="string",
     *       description="Name report type",
     *       example="Customer Information"
     *     )
     *   ),
     */

    class ReportTypeTransformer extends TransformerAbstract {
        /**
         * A Fractal transformer.
         *
         * @return array
         */
        public static function transform(ReportType $report_type) {
            return [
                'report_type_id' => $report_type->id,
                "name" => $report_type->name,
            ];
        }
    }
