<?php

	namespace App\Core\Terms\Models;

	use App\Core\Base\Models\CoreModel;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class CategoryTerm extends CoreModel
	{
		use SoftDeletes;

		protected $table="categories_term";
		public const TAG_CACHE_MODEL = 'TAG_CACHE_CATEGORY_TERM_';

		protected $primaryKey="id_category";
		protected $fillable = [
			'name',
		];
	}
