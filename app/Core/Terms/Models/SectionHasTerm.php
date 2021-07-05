<?php

	namespace App\Core\Terms\Models;

	use App\Core\Base\Models\CoreModel;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class SectionHasTerm extends CoreModel
	{
		use SoftDeletes;
		/**
		 * Database table name
		 */
		protected $table = 'sections_has_terms';


		protected $primaryKey = 'id_section_has_term';

		/**
		 * Mass assignable columns
		 */
		protected $fillable = [
			'id_term',
			'id_section',
		];


	}
