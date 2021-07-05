<?php

	namespace App\Core\Terms\Models;

	use App\Core\Base\Models\CoreModel;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class CategoryHasTerm extends CoreModel
	{
	    use SoftDeletes;

		/**
		 * Database table name
		 */
		protected $table = 'categories_has_terms';


		protected $primaryKey = 'id_category_has_term';

		/**
		 * Mass assignable columns
		 */
		protected $fillable = [
			'id_term',
			'id_category',
		];


	}
