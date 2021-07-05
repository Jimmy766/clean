<?php

	namespace App\Core\Rapi\Collections;

	use App\Core\Base\Collections\CoreResourceCollection;
    use App\Core\Languages\Resources\LanguageTrillonarioResource;

    class LanguageTrillonarioCollection extends CoreResourceCollection
	{
		public $collects=LanguageTrillonarioResource::class;
	}
