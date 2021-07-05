<?php

namespace App\Core\Users\Services;

use App\Core\Rapi\Models\State;
use App\Core\Base\Traits\ApiResponser;
use App\Core\Base\Traits\Encoding;
use App\Core\Base\Traits\Utils;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ValidationCountyWithStateService
{

    use ApiResponser, Encoding, Utils, ValidatesRequests;

    public function execute($request)
    {
        //Validate State for USA, BRA, CAN
        if ($request->country_id == 272 || $request->country_id == 281 || $request->country_id == 305) {
            if ($request->usr_state && ( !is_numeric($request->usr_state) || !$this->validate_state(
                        $request->country_id, $request->usr_state
                    ) )) {
                throw new UnprocessableEntityHttpException(
                    trans('lang.invalid_state'), null, Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        //Validation for BRA
        $usr_state = "";
        if ($request->country_id == 272) {
            $rules = [
                'usr_ssn_type' => 'integer|min:7|max:7',
                'usr_ssn'      => 'string|max:50',
            ];

            $this->validate($request, $rules);

            if ($request->usr_ssn && !$this->validateCPF($request->usr_ssn)) {
                throw new UnprocessableEntityHttpException(
                    trans('lang.invalid_ssn'), null, Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
            if ($request->usr_mobile && $request->usr_mobile && !$this->validate_movil(
                    $request->usr_mobile
                )) {
                throw new UnprocessableEntityHttpException(
                    trans('lang.invalid_mobile'), null, Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

        }

        //canada
        if($request->country_id == 281){
            if ($request->usr_state) {
                $state = State::where('state_id', $request->usr_state)
                    ->where('country_id', $request->country_id)
                    ->first();
                if(!is_null($state)){
                    $usr_state = $state->state_iso;
                }
            }
        }


        if ($request->usr_state) {
            $state = State::where('state_id', $request->usr_state)
                ->where('country_id', $request->country_id)
                ->first();
            if(!is_null($state)){
                $usr_state = $state->state_iso;
            }
        }

        //Validation for COL
        if ($request->country_id == 287) {
            $rules = [
                'usr_ssn_type' => 'integer|min:1|max:6',
                'usr_ssn'      => 'string|max:50',
            ];
            $this->validate($request, $rules);
            if ($request->usr_ssn_type && $request->usr_ssn && !$this->validate_doc_colombia(
                    $request->usr_ssn_type, $request->usr_ssn
                )) {
                throw new UnprocessableEntityHttpException(
                    trans('lang.invalid_doc'), null, Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        if ($usr_state === "") {
            $usr_state = $request->usr_state ? $request->usr_state : '';
        }

        return [ $usr_state ];
    }
}
