<?php

	namespace App\Core\Terms\Resources;

	use App\Core\Terms\Resources\TermResource;
    use App\Core\Base\Traits\UtilsFormatText;
    use Illuminate\Http\Resources\Json\JsonResource;

	/** @mixin \App\Core\Terms\Models\TranslationTerm */
	class TranslationTermResource extends JsonResource
	{
	    use UtilsFormatText;
		/**
		 * @SWG\Definition(
		 *     definition="TranslationTerm",
		 *     @SWG\Property(
		 *       property="identifier",
		 *       type="integer",
		 *       description="Translation Term identifier",
		 *       example="3"
		 *     ),
		 *     @SWG\Property(
		 *       property="id_term",
		 *       type="integer",
		 *       description="Term identifier",
		 *       example="3"
		 *     ),
		 *     @SWG\Property(
		 *       property="id_language",
		 *       type="integer",
		 *       description="Language identifier",
		 *       example="3"
		 *     ),
		 *     @SWG\Property(
		 *       property="text",
		 *       type="string",
		 *       description="Text of Translation",
		 *       example="Translations it!"
		 *     ),
		 *     @SWG\Property(
		 *       property="status",
		 *       type="interger",
		 *       description="0: editing, 1: translated, 2: approved, 3: denied",
		 *       example="0"
		 *     ),
		 *     @SWG\Property(
		 *       property="active",
		 *       type="integer",
		 *       description="1: active , 0: inactive",
		 *       example="1"
		 *     ),
		 *  ),
		 */
		public function toArray($request)
		{
			return [
				'id_term_has_language' => $this->id_term_has_language,
				'term'                 => new TermResource($this->whenLoaded('term')),
				'id_language'          => $this->id_language,
				'status'               => $this->status,
				'active'               => $this->active,
				'text'                 =>  $this->text,
			];
		}
	}
