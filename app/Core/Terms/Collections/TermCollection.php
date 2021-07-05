<?php

	namespace App\Core\Terms\Collections;

	use App\Core\Base\Collections\CoreResourceCollection;
	use App\Core\Terms\Resources\TermResource;

	class TermCollection extends CoreResourceCollection
	{
		public $collects=TermResource::class;
	}
