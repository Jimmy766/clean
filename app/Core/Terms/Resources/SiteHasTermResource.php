<?php

	namespace App\Core\Terms\Resources;

	use Illuminate\Http\Resources\Json\JsonResource;

	/** @mixin \App\Core\Terms\Models\SiteHasTerm */
	class SiteHasTermResource extends JsonResource
	{
		/**
		 * @SWG\Definition(
		 *     definition="SiteHasTerm",
		 *     @SWG\Property(
		 *       property="id_site",
		 *       type="integer",
		 *       description="Site identifier",
		 *       example="3"
		 *     ),
		 *  ),
		 */
		public function toArray($request)
		{
			return [
				'id_site_has_term' => $this->id_site_has_term,
				'id_term'          => $this->id_term,
				'id_site'          => $this->id_site,
			];
		}
	}
