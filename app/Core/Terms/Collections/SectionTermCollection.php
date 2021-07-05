<?php

	namespace App\Core\Terms\Collections;

	use App\Core\Base\Collections\CoreResourceCollection;
	use App\Core\Terms\Resources\SectionTermResource;

	class SectionTermCollection extends CoreResourceCollection
	{
		public $collects=SectionTermResource::class;
	}
