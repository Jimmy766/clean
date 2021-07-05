<?php

	namespace App\Core\Terms\Requests;

	use Illuminate\Foundation\Http\FormRequest;

	class StoreTranslationTermRequest extends FormRequest
	{
		/**
		 * @SWG\Definition(
		 *     definition="StoreTranslationTerm",
		 *     @SWG\Property(
		 *       property="id_term",
		 *       type="integer",
		 *       description="Term ID",
		 *       example="1"
		 *     ),
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
				'id_term'     => 'required|integer|exists:mysql.terms,id_term',
				'id_language' => 'required|integer|exists:languages,id_language',
				'text'        => 'required',
				'status'      => 'required|integer|between:0,3',
				'active'      => 'required|integer|between:0,1'
			];
		}

		public function authorize()
		{
			return true;
		}
	}
