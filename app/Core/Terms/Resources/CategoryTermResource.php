<?php

	namespace App\Core\Terms\Resources;

	use Illuminate\Http\Resources\Json\JsonResource;

	class CategoryTermResource extends JsonResource
	{
		/**
		 * @SWG\Definition(
		 *     definition="CategoryTerm",
		 *     required={"name"   },
		 *     @SWG\Property(
		 *       property="identifier",
		 *       type="integer",
		 *       description="Category Term identifier",
		 *       example="3"
		 *     ),
		 *     @SWG\Property(
		 *       property="name",
		 *       type="string",
		 *       description="Name of Category",
		 *       example="Home"
		 *     ),
		 *  ),
		 */
		public function toArray($request)
		{
			return [
				'id_category' => $this->id_category,
				'name'        => $this->name
			];
		}
	}
