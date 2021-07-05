<?php

	namespace App\Core\Terms\Models;

	use App\Core\Base\Models\CoreModel;
    use Illuminate\Database\Eloquent\SoftDeletes;

    class SiteHasTerm extends CoreModel
	{
	    use SoftDeletes;
		/**
		 * Database table name
		 */
		protected $table = 'sites_has_terms';


		protected $primaryKey = 'id_site_has_term';

		/**
		 * Mass assignable columns
		 */
		protected $fillable = [
			'id_term',
			'id_site',
		];


	}
