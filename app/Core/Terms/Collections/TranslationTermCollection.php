<?php

	namespace App\Core\Terms\Collections;

	use App\Core\Base\Collections\CoreResourceCollection;
	use App\Core\Terms\Resources\TranslationTermResource;

	class TranslationTermCollection extends CoreResourceCollection
	{
		public $collects=TranslationTermResource::class;
	}
