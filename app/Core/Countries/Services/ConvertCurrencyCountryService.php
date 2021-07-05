<?php

namespace App\Core\Countries\Services;

use App\Core\Countries\Models\Country;

/**
 * Class ConvertCurrencyCountry
 * @package App\Services
 */
class ConvertCurrencyCountryService
{

    /**
     * @param        $slides
     * @param        $codeCountry
     * @param string $fromCurrency
     * @return mixed
     */
    public function execute($slides, $codeCountry, $fromCurrency = 'USD')
    {
        return $slides->map($this->mapLoadJackPot($codeCountry));
    }

    private function convert($codeCountry, $amount, $fromCurrency = 'USD')
    {
        $exchange = Country::query()
            ->where('country_Iso', $codeCountry)
            ->join('countries_info as ci', 'ci.country_id', 'countries.country_id')
            ->join('currency_exchange as ce', 'ce.curr_code_to', 'ci.country_currency')
            ->join('currencies as cu', 'cu.curr_code', 'ce.curr_code_to')
            ->where('ce.curr_code_from', $fromCurrency)
            ->where('ce.active', 1)
            ->orderBy('ce.exch_regdate', 'desc')
            ->firstFromCache([ 'ce.exch_factor', 'cu.curr_symbol' ]);

        if (null === $exchange) {
            return [
                'amount'           => $amount,
                'symbol'           => '$',
                'amountWithSymbol' => $amount . ' $',
            ];
        }

        $finalAmount = $amount * $exchange->exch_factor;

        return [
            'amount'           => $finalAmount,
            'symbol'           => $exchange->curr_symbol,
            'amountWithSymbol' => $finalAmount . ' ' . $exchange->curr_symbol,
        ];
    }

    private function mapLoadJackPot($codeCountry): callable
    {
        return function ($item, $key) use ($codeCountry) {
            $exchange = $this->convert($codeCountry, floatval($item->jack_pot));

            $item[ 'jackpot_convert_amount' ]        = $exchange[ 'amount' ];
            $item[ 'jackpot_convert_amount_symbol' ] = $exchange[ 'amountWithSymbol' ];
            $item[ 'jackpot_convert_symbol' ]        = $exchange[ 'symbol' ];

            return $item;
        };
    }

}
