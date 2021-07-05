<?php

	namespace App\Core\Terms\Requests;

	use Illuminate\Foundation\Http\FormRequest;

	class StoreTranslationsTermRequest extends FormRequest
	{
		/**
		 * @SWG\Definition(
		 *     definition="StoreTranslationsTerm",
		 *     @SWG\Property(
		 *       property="translations",
		 *       @SWG\Items(
		 *          type="object",
		 *          allOf={
		 *              @SWG\Schema(ref="#/definitions/StoreTranslations"),
		 *          }
		 *        )
		 *     ),
		 *  ),
		 */
		/**
		 * @SWG\Definition(
		 *     definition="StoreTranslations",
		 *     required="id_language,text",
		 *     @SWG\Property(
		 *       property="id_language",
		 *       type="integer",
		 *       description="Language ID",
		 *       example="1"
		 *     ),
		 *     @SWG\Property(
		 *       property="status",
		 *       type="integer",
		 *       description="Translation Status: 0: editing, 1: translated, 2: approved, 3: denied",
		 *       example="1"
		 *     ),
		 *     @SWG\Property(
		 *       property="active",
		 *       type="integer",
		 *       description="Translation active",
		 *       example="1"
		 *     ),
		 *     @SWG\Property(
		 *       property="text",
		 *       type="string",
		 *       description="Text of Translation",
		 *       example="translated"
		 *     ),
		 *  ),
		 */
		public function rules()
		{
			return [
				'translations'               => 'required|array|min:1',
				'translations.*'             => 'required',
				'translations.*.id_language' => 'required|integer|exists:languages,id_language',
				'translations.*.text'        => 'required',
				'translations.*.status'      => 'required|integer|between:0,3',
				'translations.*.active'      => 'required|integer|between:0,1'
			];
		}

		public function authorize()
		{
			return true;
		}
	}
