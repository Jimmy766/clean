<?php

	namespace App\Core\Terms\Requests;

	use App\Core\Base\Requests\Rules\CheckUniqueAttributeModel;
    use App\Core\Terms\Requests\Rules\CheckUniqueTerms;
    use App\Core\Terms\Models\Term;
    use Illuminate\Foundation\Http\FormRequest;

	class StoreTermRequest extends FormRequest
	{
		/**
		 * @SWG\Definition(
		 *     definition="StoreTerm",
		 *     required={"name","id_term",*  },
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
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/StoreSectionTerm"),
		 *          }
		 *        )
		 *     ),
		 *     @SWG\Property(
		 *       property="categories",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/StoreCategoryTerm"),
		 *          }
		 *        )
		 *     ),
		 *     @SWG\Property(
		 *       property="sites",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/StoreSiteTerm"),
		 *          }
		 *        )
		 *     ),

		 *  ),
		 */
		/**
		 * @SWG\Definition(
		 *     definition="StoreSectionTerm",
		 *     @SWG\Property(
		 *       property="id_section",
		 *       type="integer",
		 *       example=1
		 *     ),
		 *  ),
		 */
		/**
		 * @SWG\Definition(
		 *     definition="StoreCategoryTerm",
		 *     @SWG\Property(
		 *       property="id_category",
		 *       type="integer",
		 *       example=1
		 *     ),
		 *  ),
		 */
		/**
		 * @SWG\Definition(
		 *     definition="StoreSiteTerm",
		 *     @SWG\Property(
		 *       property="id_site",
		 *       type="integer",
		 *       example=1
		 *     ),
		 *  ),
		 */
		public function rules()
		{
            return [

                'name'         => [
                    'required',
                    new CheckUniqueTerms(new Term, request()->term,request()->all('sites'))
                ],
                'example_text' => 'required',

                'categories'               => 'array',
                'categories.*.id_category' => 'exists:mysql.categories_term,id_category',

                'sections'              => 'array',
                'sections.*.id_section' => 'exists:mysql.sections_term,id_section',


                'sites'              => 'array',
                'sites.*.id_site' => 'exists:mysql_external.sites,site_id',


            ];
		}

		public function authorize()
		{
			return true;
		}
	}
