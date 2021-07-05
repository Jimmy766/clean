<?php

	namespace App\Core\Terms\Models;

	use App\Core\Base\Models\CoreModel;
    use App\Core\Terms\Models\Term;
    use Illuminate\Database\Eloquent\Relations\BelongsTo;
	use Illuminate\Database\Eloquent\SoftDeletes;

	class TranslationTerm extends CoreModel
	{
		use SoftDeletes;
		/**
		 * Database table name
		 */
		protected $table = 'translations_terms';



		protected $primaryKey = 'id_term_has_language';

		public const TAG_CACHE_MODEL = 'TAG_CACHE_TRANSLATION_TERM_';

		/**
		 * Mass assignable columns
		 */
		protected $fillable = [
			'id_term',
			'id_language',
			'text',
			'status',
			'active'
		];

		public function term(): BelongsTo
		{
			return $this->belongsTo(Term::class,'id_term');
		}

	}
