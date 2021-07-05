<?php

namespace App\Core\Slides\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="DateProgram",
 *     required={"identifier", "date_init", "date_end", "day_init", "day_end"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="DateProgram identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="date_init",
 *       type="date-time",
 *       description="Date Init",
 *       example="2020-08-05 00:00:00"
 *     ),
 *     @SWG\Property(
 *       property="date_end",
 *       type="date-time",
 *       description="Date End",
 *       example="2020-08-06 00:00:00"
 *     ),
 *     @SWG\Property(
 *       property="day_init",
 *       type="date-time",
 *       description="Day Init",
 *       example="null"
 *     ),
 *     @SWG\Property(
 *       property="day_end",
 *       type="date-time",
 *       description="Day End",
 *       example="null"
 *     ),
 *  ),
 */
class DateProgramResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'date_init' => $this->date_init,
            'date_end' => $this->date_end,
            'day_init' => $this->day_init,
            'day_end' => $this->day_end,
        ];
    }
}
