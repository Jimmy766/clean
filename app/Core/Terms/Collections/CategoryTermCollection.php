<?php

	namespace App\Core\Terms\Collections;

	use App\Core\Base\Collections\CoreResourceCollection;
	use App\Core\Terms\Resources\CategoryTermResource;


	class CategoryTermCollection extends CoreResourceCollection
	{
		public $collects = CategoryTermResource::class;
	}
