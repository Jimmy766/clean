<?php

	namespace App\Core\Terms\Resources;

	use Illuminate\Http\Resources\Json\JsonResource;

	class SectionTermResource extends JsonResource
	{
		/**
		 * @SWG\Definition(
		 *     definition="SectionTerm",
		 *     required={"name"   },
		 *     @SWG\Property(
		 *       property="identifier",
		 *       type="integer",
		 *       description="Section Term identifier",
		 *       example="3"
		 *     ),
		 *     @SWG\Property(
		 *       property="name",
		 *       type="string",
		 *       description="Name of Section",
		 *       example="Menu"
		 *     ),
		 *  ),
		 */
		public function toArray($request)
		{
			return [
				'id_section' => $this->id_section,
				'name'       => $this->name,
			];
		}
	}
