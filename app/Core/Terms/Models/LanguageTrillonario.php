<?php

namespace App\Core\Terms\Models;

use App\Core\Base\Models\CoreModel;

class LanguageTrillonario extends CoreModel
{

	public $connection='mysql_external';
	protected $table='languages';
	public const TAG_CACHE_MODEL = 'TAG_CACHE_LANGUAGE_TRI_';

	protected $primaryKey="languages_id";
	protected $fillable = [
		'codigo','nombre','codigo_largo','activo'
	];
}
