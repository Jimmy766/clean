<?php

	namespace App\Core\Terms\Models;

	use App\Core\Terms\Models\CategoryHasTerm;
    use App\Core\Terms\Models\CategoryTerm;
    use App\Core\Base\Classes\ModelConst;
    use App\Core\Base\Models\CoreModel;
    use App\Core\Terms\Models\Language;
    use App\Core\Terms\Models\SectionHasTerm;
    use App\Core\Terms\Models\SectionTerm;
    use App\Core\Terms\Models\SiteHasTerm;
    use App\Core\Terms\Models\TranslationTerm;
    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
	use Illuminate\Database\Eloquent\Relations\HasMany;
    use Illuminate\Database\Eloquent\Relations\HasOne;
    use Illuminate\Database\Eloquent\SoftDeletes;

	class Term extends CoreModel
	{
		use SoftDeletes;

		public const TAG_CACHE_MODEL = 'TAG_CACHE_TERM_';

        public const TIME_CACHE_MODEL = ModelConst::CACHE_TIME_DAY;

		protected $primaryKey="id_term";
		protected $fillable = [
			'name','example_text'
		];

		public function categories()
		{

			$databaseName = CategoryHasTerm::getModel()->getConnection()->getDatabaseName();
			return $this->belongsToMany(CategoryTerm::class, $databaseName .'.categories_has_terms','id_term','id_category')
				->whereNull('categories_has_terms.deleted_at');
		}

		public function sections()
		{
			$databaseName = SectionHasTerm::getModel()->getConnection()->getDatabaseName();
			return $this->belongsToMany(SectionTerm::class,$databaseName.'.sections_has_terms','id_term','id_section')
				->whereNull('sections_has_terms.deleted_at');
		}

		public function sites(): HasMany
		{
			return $this->hasMany(SiteHasTerm::class,'id_term','id_term');
		}

		public function translations(): HasMany
		{
			return $this->hasMany(TranslationTerm::class,'id_term','id_term');
		}
		public function translationsByLanguage(): ?HasOne
        {
            $language = app()->getLocale();
            $language = Language::where('code', $language)->firstFromCache(['*'], Language::TAG_CACHE_MODEL);
            $language = $language ?? Language::where('code', 'en')->firstFromCache(['*'], Language::TAG_CACHE_MODEL);
            if($language!==null){
                return $this->hasOne(TranslationTerm::class,'id_term','id_term')
                    ->where('id_language',$language->id_language);
            }
            return null;
		}
	}
