<?php

namespace App\Service\CommissionCalculator\HttpClient;

class ExchangeRateClient
{
    public function call()
    {
        return file_get_contents('https://api.exchangeratesapi.io/latest');
    }
}