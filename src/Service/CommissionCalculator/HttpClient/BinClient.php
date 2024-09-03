<?php

namespace App\Service\CommissionCalculator\HttpClient;

class BinClient
{
    public function call(string $bin): string
    {
        return file_get_contents('https://lookup.binlist.net/' . $bin);
    }
}