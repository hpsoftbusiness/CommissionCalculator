<?php

namespace App\Service\CommissionCalculator;

use App\Service\CommissionCalculator\HttpClient\BinClient;
use App\Service\CommissionCalculator\HttpClient\ExchangeRateClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CommissionCalculator
{
    CONST EURO_CURRENCY = 'EUR';
    /**
     * @var BinClient
     */
    private BinClient $binClient;

    /**
     * @var ExchangeRateClient
     */
    private ExchangeRateClient $exchangeRateClient;

    /**
     * @param BinClient $binClient
     * @param ExchangeRateClient $exchangeRateClient
     */
    public function __construct(BinClient $binClient, ExchangeRateClient $exchangeRateClient)
    {
        $this->binClient = $binClient;
        $this->exchangeRateClient = $exchangeRateClient;
    }

    /**
     * @param string $countryCode
     *
     * @return bool
     */
    private function isEu(string $countryCode): bool
    {
        $euCountries = [
            'AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'ES', 'FI', 'FR', 'GR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU',
            'LV', 'MT', 'NL', 'PO', 'PT', 'RO', 'SE', 'SI', 'SK'
        ];

        if (in_array($countryCode, $euCountries, true)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $transactionFileContent
     *
     * @return array
     */
    public function calculate(string $transactionFileContent): array
    {
        $result = [];

        foreach (explode("\n", $transactionFileContent) as $trans) {
            if (empty($trans)) break;

            $extractValue = function(string $item): string {
                return trim(explode(':', $item)[1], '"}');
            };

            $parts = explode(",", $trans);
            $transaction = array_map($extractValue, $parts);

            $transactionBin = $transaction[0];
            $transactionAmount = $transaction[1];
            $transactionCurrency = $transaction[2];

            $binResults = $this->binClient->call($transactionBin);

            if (!$binResults) {
                //let's assume we have no api connection or token is wrong
                throw new AccessDeniedHttpException('Bin client has returned no results.');
            }

            $decodedBinResults = json_decode($binResults);
            $isEu = $this->isEu($decodedBinResults->country->alpha2);
            $rate = json_decode($this->exchangeRateClient->call(), true)['rates'][$transactionCurrency];

            if ($transactionCurrency == self::EURO_CURRENCY || $rate == 0) {
                $amountFixed = $transactionAmount;
            }

            if ($transactionCurrency != self::EURO_CURRENCY || $rate > 0) {
                if ($rate != 0) {
                    $amountFixed = $transactionAmount / $rate;
                }
                else {
                    $amountFixed = $transactionAmount;
                }
            }

            $result[] = ceil($amountFixed) * ($isEu ? 0.01 : 0.02);
        }

        return $result;
    }
}