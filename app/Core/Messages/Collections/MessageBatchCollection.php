<?php

    namespace App\Core\Messages\Collections;

    use App\Core\Base\Collections\CoreResourceCollection;
    use App\Core\Messages\Resources\MessageBatchResource;

    /** @see \App\Core\Messages\Models\MessageBatch */
    class MessageBatchCollection extends CoreResourceCollection
    {
        public $collects=MessageBatchResource::class;
    }
