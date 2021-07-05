<?php

	namespace App\Core\Terms\Requests;

	use Illuminate\Foundation\Http\FormRequest;

	class ListTranslationsRequest extends FormRequest
	{
		/**
		 * @SWG\Definition(
		 *     definition="ListTranslationsRequest",
		 *     @SWG\Property(
		 *       property="categories",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/CategoriesRequest"),
		 *          }
		 *        )
		 *     ),
		 *     @SWG\Property(
		 *       property="sections",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/SectionsRequest"),
		 *          }
		 *        )
		 *     ),
		 *  ),
		 */
		/**
		 * @SWG\Definition(
		 *     definition="CategoriesRequest",
		 *     @SWG\Property(
		 *       property="name",
		 *       type="string",
		 *       description="Category Name",
         *       default="HOME"
		 *     ),
		 *  ),
		 */
		/**
		 * @SWG\Definition(
		 *     definition="SectionsRequest",
		 *     @SWG\Property(
		 *       property="name",
		 *       type="string",
		 *       description="Section Name",
         *       default="MENU"
		 *     ),
		 *  ),
		 */
		public function rules()
		{
			return [
				'categories'                    => 'required|array|min:1',
                'categories.*'                  => 'required',
                'categories.*.name'             => 'string|exists:categories_term,name',
                'sections'                      => 'array|min:1',
                'sections.*'                    => 'required',
                'sections.*.name'               => 'required|string|exists:sections_term,name'
			];
		}

		public function authorize()
		{
			return true;
		}
	}
