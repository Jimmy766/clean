<?php

	namespace App\Core\Terms\Models;

	use App\Core\Base\Models\CoreModel;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class SectionTerm extends CoreModel
	{
		use SoftDeletes;

		protected $table="sections_term";
		public const TAG_CACHE_MODEL = 'TAG_CACHE_SECTION_TERM_';

		protected $primaryKey="id_section";
		protected $fillable = [
			'name',
		];
	}
