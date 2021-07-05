<?php

namespace App\Core\Languages\Resources;

use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;
use Swagger\Annotations as SWG;

/**
 * @SWG\Definition(
 *     definition="Language",
 *     required={"identifier","name", "code",*    },
 *     @SWG\Property(
 *       property="identifier",
 *       type="integer",
 *       description="Language identifier",
 *       example="3"
 *     ),
 *     @SWG\Property(
 *       property="name",
 *       type="string",
 *       description="Name of language",
 *       example="Spanish"
 *     ),
 *     @SWG\Property(
 *       property="code",
 *       type="string",
 *       description="Code of language",
 *       example="es-ES"
 *     ),
 *  ),
 */
class LanguageResource extends JsonResource
{
    use UtilsFormatText;
    public function toArray($request)
    {
        return [
            'id_language' => $this->id_language,
            'name' => $this->name,
            'code' => $this->code,
        ];
    }
}
