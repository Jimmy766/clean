<?php

namespace App\Core\Reports\Transforms;

use App\Core\Reports\Models\Report;
use League\Fractal\TransformerAbstract;

/**    @SWG\Definition(
 *     definition="Report",
 *     required={"start", "end", "type"},
 *     @SWG\Property(
 *       property="report_id",
 *       type="integer",
 *       format="int32",
 *       description="Report identifier",
 *       example="111111"
 *     ),
 *     @SWG\Property(
 *       property="start",
 *       type="string",
 *       format="date",
 *       description="Report start date",
 *       example="2018-06-11"
 *     ),
 *     @SWG\Property(
 *       property="end",
 *       type="string",
 *       format="date",
 *       description="Report end date",
 *       example="2018-06-11"
 *     ),
 *     @SWG\Property(
 *       property="status",
 *       type="string",
 *       description="Report status",
 *       example="processing"
 *     ),
 *     @SWG\Property(
 *       property="download_url",
 *       type="string",
 *       description="Report download url",
 *       example="https://rapi-stage.trillonario.com/fliAAQWDcopaq23aD1245zx.csv"
 *     ),
 *     @SWG\Property(
 *       property="report_type",
 *       type="object",
 *       description="Report type",
 *       allOf={ @SWG\Schema(ref="#/definitions/ReportsType"  )}
 *     )
 *   ),
 **/

class ReportTransformer extends TransformerAbstract
{
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public static function transform(Report $report)
    {
        return [
            'report_id' => $report->id,
            'start' => $report->start,
            'end' => $report->end,
            'status' => $report->status,
            'download_url' => $report->url,
            //'token' => $report->token,
            'created' => $report->created_at->format('Y-m-d H:i:s'),
            'updated' => $report->updated_at->format('Y-m-d H:i:s'),
            'tag' => $report->tag,
        ];
    }


    public static function originalAttribute($index) {
        $attributes = [
            'report_id' => 'id',
            'start' => 'start',
            'end' => 'end',
            'tag' => 'tag',
            'status' => 'status',
            'created' => 'created_at',
            'updated' => 'updated_at',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }

    public static function transformedAttribute($index) {
        $attributes = [
            'id' => 'report_id',
            'start' => 'start',
            'end' => 'end',
            'tag' => 'tag',
            'status' => 'status',
            'created_at' => 'created',
            'updated_at' => 'updated',
        ];
        return isset($attributes[$index]) ? $attributes[$index] : null;
    }
}
