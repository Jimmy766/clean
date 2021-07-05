<?php

namespace App\Core\Skins\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="FileSkin",
 *     required={"identifier", "tag", "file"},
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="FileSkin identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="tag",
 *       type="string",
 *       description="tag File",
 *       example="file banner"
 *     ),
 *     @SWG\Property(
 *       property="file",
 *       type="string",
 *       description="File url",
 *       example="https://rapi-reports-stage-public.s3.eu-central-1.amazonaws.com/slides/dashboard1599062424.png"
 *     ),
 *  ),
 */
class FileSkinResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id_file' => $this->id_file,
            'tag'     => $this->tag,
            'file'    => $this->file,
        ];
    }
}
