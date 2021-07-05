<?php

namespace App\Core\Messages\Services;

use Illuminate\Http\Response;

class GenerateValidationMessageBlockService
{

    /**
     * @param $validationsCollect
     */
    public function execute($validationsCollect): void
    {
        $validationsCollect->map($this->mapCheckValidationExceptionTransform());
    }

    private function mapCheckValidationExceptionTransform(): callable
    {
        return static function ($item, $key) {
            if ( !array_key_exists('value', $item)) {
                return $item;
            }
            if ( !array_key_exists('message', $item)) {
                return $item;
            }

            if ($item[ 'value' ] !== null) {
                abort(Response::HTTP_FORBIDDEN, $item[ 'message' ]);
            }
            return $item;
        };
    }
}
