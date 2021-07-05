<?php

namespace App\Core\Languages\Resources;

use App\Core\Base\Traits\UtilsFormatText;
use Illuminate\Http\Resources\Json\JsonResource;

class LanguageTrillonarioResource extends JsonResource
{
	use UtilsFormatText;
	public function toArray($request)
	{
		return [
            'languages_id'=> $this->languages_id,
            'codigo'=>$this->codigo,
            'nombre'=>$this->convertTextCharset($this->nombre),
            'codigo_largo'=>$this->codigo_largo,
            'activo'=>$this->activo
		];
	}
}
