<?php

	namespace App\Core\Terms\Resources;

	use App\Core\Terms\Resources\SectionTermResource;
    use App\Core\Terms\Resources\CategoryTermResource;
    use App\Core\Terms\Resources\SiteHasTermResource;
    use App\Core\Terms\Resources\TranslationTermResource;
    use App\Core\Base\Traits\UtilsFormatText;
    use Illuminate\Http\Resources\Json\JsonResource;

	/** @mixin \App\Core\Terms\Models\Term */
	class TermResource extends JsonResource
	{
        use UtilsFormatText;
		/**
		 * @SWG\Definition(
		 *     definition="Term",
		 *     required={"name","id_term",*  },
		 *     @SWG\Property(
		 *       property="identifier",
		 *       type="integer",
		 *       description="Term identifier",
		 *       example="3"
		 *     ),
		 *     @SWG\Property(
		 *       property="name",
		 *       type="string",
		 *       description="Name of Term",
		 *       example="Nav 1"
		 *     ),
		 *     @SWG\Property(
		 *       property="example_text",
		 *       type="string",
		 *       description="Example Text of Term",
		 *       example="Translations it!"
		 *     ),

		 *     @SWG\Property(
		 *       property="sections",
		 *       description="Sections Term",
		 *       type="array",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/SectionTerm"),
		 *          }
		 *        )
		 *     ),
		 *     @SWG\Property(
		 *       property="categories",
		 *       description="Categories Term",
		 *       type="array",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/CategoryTerm"),
		 *          }
		 *        )
		 *     ),
		 *     @SWG\Property(
		 *       property="sites",
		 *       description="Categories Term",
		 *       type="array",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/SiteHasTerm"),
		 *          }
		 *        )
		 *     ),
		 *     @SWG\Property(
		 *       property="translations",
		 *       description="Translations",
		 *       type="array",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/TranslationTerm"),
		 *          }
		 *        )
		 *     ),
		 *  ),
		 */
		public function toArray($request)
		{
			return [
				'id_term'      => $this->id_term,
				'name'         => $this->name,
                'example_text' => $this->example_text,
				'sections'   => SectionTermResource::collection($this->whenLoaded('sections')),
				'categories' => CategoryTermResource::collection($this->whenLoaded('categories')),
				'sites'      => SiteHasTermResource::collection($this->whenLoaded('sites')),
				'translations'=>TranslationTermResource::collection($this->whenLoaded('translations')),
				'translation'=>new TranslationTermResource($this->whenLoaded('translationsByLanguage'))
			];
		}
	}
