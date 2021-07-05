<?php

    namespace App\Core\Base\Traits;


    use App\Core\Base\Classes\ModelConst;
    use App\Core\Rapi\Models\PricePoint;
    use App\Core\Rapi\Models\State;
    use Detection\MobileDetect;

    trait Utils
    {

        public function getLanguage() {
            return request('client_lang') ? substr(request('client_lang'), 0, 2) : 'en';
        }

        public function getLanguageCode() {
            return request('client_lang') ? request('client_lang') : 'en-us';
        }

        public function getClientLanguage() {
            return request('client_lang') ? substr(request('client_lang'), 0, 2) : 'en';
        }

        /**
         * Detects is mobile from request user_agent
         * @return bool
         */
        public function isMobile(){
            $detect = new MobileDetect();
            return request()->user_agent ?  $detect->isMobile(request()->user_agent): false;
        }

        public function validate_state($country_id, $state_id) {
           $state = State::where('state_id', $state_id)->where('country_id', $country_id)->get();
           return $state->isEmpty() ? false : true;
        }

        public function validateCPF($cpf) {
            // Verifica se um n�mero foi informado
            if (empty($cpf)) {
                return false;
            }

            // Elimina possivel mascara
            $cpf = preg_replace("/[^0-9]/", "", $cpf);
            $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);

            // Verifica se o numero de digitos informados � igual a 11
            if (strlen($cpf) != 11) {
                return false;
            }
            // Verifica se nenhuma das sequ�ncias invalidas abaixo
            // foi digitada. Caso afirmativo, retorna falso
            else {
                if ($cpf == '00000000000' ||
                    $cpf == '11111111111' ||
                    $cpf == '22222222222' ||
                    $cpf == '33333333333' ||
                    $cpf == '44444444444' ||
                    $cpf == '55555555555' ||
                    $cpf == '66666666666' ||
                    $cpf == '77777777777' ||
                    $cpf == '88888888888' ||
                    $cpf == '99999999999') {
                    return false;
                    // Calcula os digitos verificadores para verificar se o
                    // CPF � v�lido
                } else {

                    for ($t = 9; $t < 11; $t++) {

                        for ($d = 0, $c = 0; $c < $t; $c++) {
                            $d += $cpf{$c} * (($t + 1) - $c);
                        }
                        $d = ((10 * $d) % 11) % 10;
                        if ($cpf{$c} != $d) {
                            return false;
                        }
                    }

                    return true;
                }
            }
        }

     /**
         * @param $number
         * @return false|int
         *
         * Dos n�meros, cualquier cantidad de espacios, del 6 al 9 (para tel�fonos m�viles en Br) m�s 3 d�gitos, cualquier cantidad de espacios y finalmente 5 d�gitos.
         *
         * First country code, then the geographic area code, mobile phone numbers use the digits 6, 7, 8 or 9.
         */
        public function validate_movil($number) {
            $number = str_replace("-", "", $number);
            return preg_match("/^(\d{1,2})? *\d{2} *[6-9]\d{3} *\d{5}$/", $number);
        }

        public function validate_doc_colombia($ssn_type, $ssn) {
            switch ($ssn_type) {
                case ModelConst::DOCUMENT_CC_COLOMBIAN_IDENTIFICATION_CARD:
                case ModelConst::DOCUMENT_CE_IMMIGRATION_CARD:
                case ModelConst::DOCUMENT_TI_IDENTITY_CARD:
                    return preg_match('/^\w{4,12}$/', $ssn);       // palabra entre 4 y 12 chars , ej: abcd12345
                    break;
                case ModelConst::DOCUMENT_NIT_TAX_IDENTIFICATION_NUMBER:
                    return preg_match('/^\d{3}-?\d{2}-?\d{4}$/', $ssn);  // 3 digitos + "-" + 2 digitos + "-" + 4 digitos , ej: 123-45-6789
                    break;
                case ModelConst::DOCUMENT_PPN_PASSPORT:
                    return preg_match('/^\w{4,12}$/', $ssn);      // palabra entre 4 y 12 chars , ej: abcd12345
                    break;
                case ModelConst::DOCUMENT_SSN_SOCIAL_SECURITY_NUMBER:
                    return preg_match('/^\w{4,12}$/', $ssn);      // palabra entre 4 y 12 chars , ej: abcd12345
                    break;
                case 1:
                    break;

            }
            return true;
        }

        public function trade_points($points) {
            $exchange_points = PricePoint::where('curr_code', '=', request('country_currency'))->first()->exchange_points;
            $amount = round($points/$exchange_points,2);
            return $amount;
        }
    }
