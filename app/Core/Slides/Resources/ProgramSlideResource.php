<?php

namespace App\Core\Slides\Resources;

use App\Core\Slides\Resources\DateProgramResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="ProgramSlide",
 *     required={"identifier", "type_range_program", "type_current_program", "period_current_program", "datePrograms"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="ProgramSlide identifier",
 *       example="25"
 *     ),
 *     @SWG\Property(
 *       property="type_range_program",
 *       type="integer",
 *       description="Type Range Program",
 *       example="2"
 *     ),
 *     @SWG\Property(
 *       property="type_current_program",
 *       type="integer",
 *       description="Type Current Program",
 *       example="1"
 *     ),
 *     @SWG\Property(
 *       property="period_current_program",
 *       type="integer",
 *       description="Period Current Program",
 *       example="0"
 *     ),
 *     @SWG\Property(
 *       property="datePrograms",
 *       description="datePrograms",
 *       type="array",
 *       @SWG\Items(
 *          type="object",
 *          allOf={
 *              @SWG\Schema(ref="#/definitions/DateProgram"),
 *          }
 *        )
 *     ),
 *  ),
 */
class ProgramSlideResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'type_range_program' => $this->type_range_program,
            'type_current_program' => $this->type_current_program,
            'period_current_program' => $this->period_current_program,
            'datePrograms' => DateProgramResource::collection($this->whenLoaded('datePrograms')),
        ];
    }
}
