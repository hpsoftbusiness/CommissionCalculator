<?php

namespace App\Tests;

use App\Service\CommissionCalculator\CommissionCalculator;
use App\Service\CommissionCalculator\HttpClient\BinClient;
use App\Service\CommissionCalculator\HttpClient\ExchangeRateClient;
use PHPUnit\Framework\TestCase;

class CommissionCalculatorTest extends TestCase
{
    private $binClientMock;
    private $exchangeRateClientMock;
    private $commissionCalculator;

    protected function setUp(): void
    {
        $this->binClientMock = $this->createMock(BinClient::class);
        $this->exchangeRateClientMock = $this->createMock(ExchangeRateClient::class);
        $this->commissionCalculator = new CommissionCalculator($this->binClientMock, $this->exchangeRateClientMock);
    }

    public function testCalculateWithEuCurrency()
    {
        $transactionFileContent = '{"bin":"45717360","amount":"100.00","currency":"EUR"}
        {"bin":"516793","amount":"50.00","currency":"USD"}
        {"bin":"45417360","amount":"10000.00","currency":"JPY"}
        {"bin":"41417360","amount":"130.00","currency":"USD"}
        {"bin":"4745030","amount":"2000.00","currency":"GBP"}';
        $this->binClientMock->method('call')
            ->willReturn('{"number":{},"scheme":"visa","type":"debit","brand":"Visa Classic/Dankort","country":{"numeric":"208","alpha2":"DK","name":"Denmark","emoji":"🇩🇰","currency":"DKK","latitude":56,"longitude":10},"bank":{"name":"Jyske Bank A/S"}}');
        $this->exchangeRateClientMock->method('call')
            ->willReturn('{"success":true,"base":"EUR","date":"2024-09-02","rates":{"AUD":1.6322,"BGN":1.9558,"BRL":6.2185,"CAD":1.4932,"CHF":0.9415,"CNY":7.8677,"CZK":25.045,"DKK":7.4587,"GBP":0.84218,"HKD":8.6239,"HUF":392.55,"IDR":17189.79,"ILS":4.0415,"INR":92.8075,"ISK":153.1,"JPY":162.56,"KRW":1481.32,"MXN":21.7618,"MYR":4.8171,"NOK":11.73,"NZD":1.7767,"PHP":62.513,"PLN":4.275,"RON":4.9753,"SEK":11.351,"SGD":1.4464,"THB":37.834,"TRY":37.5814,"USD":1.1061,"ZAR":19.8166,"EUR":1}}');
        $result = $this->commissionCalculator->calculate($transactionFileContent);
        $resultTest = [1, 0.45203869451225026, 0.6151574803149606, 1.1753006057318505, 23.747892374551757];
        $this->assertEquals($resultTest, $result);
    }

    public function testCalculateWithNonEuCurrency()
    {
        $transactionFileContent = '{"bin":"45717360","amount":"100.00","currency":"EUR"}
        {"bin":"516793","amount":"50.00","currency":"USD"}
        {"bin":"45417360","amount":"10000.00","currency":"JPY"}
        {"bin":"41417360","amount":"130.00","currency":"USD"}
        {"bin":"4745030","amount":"2000.00","currency":"GBP"}';
        $this->binClientMock->method('call')
            ->willReturn('{"number":{},"scheme":"visa","type":"debit","brand":"Visa Classic/Dankort","country":{"numeric":"208","alpha2":"US","name":"USA","emoji":"🇩🇰","currency":"USD","latitude":56,"longitude":10},"bank":{"name":"Jyske Bank A/S"}}');
        $this->exchangeRateClientMock->method('call')
            ->willReturn('{"success":true,"base":"EUR","date":"2024-09-02","rates":{"AUD":1.6322,"BGN":1.9558,"BRL":6.2185,"CAD":1.4932,"CHF":0.9415,"CNY":7.8677,"CZK":25.045,"DKK":7.4587,"GBP":0.84218,"HKD":8.6239,"HUF":392.55,"IDR":17189.79,"ILS":4.0415,"INR":92.8075,"ISK":153.1,"JPY":162.56,"KRW":1481.32,"MXN":21.7618,"MYR":4.8171,"NOK":11.73,"NZD":1.7767,"PHP":62.513,"PLN":4.275,"RON":4.9753,"SEK":11.351,"SGD":1.4464,"THB":37.834,"TRY":37.5814,"USD":1.1061,"ZAR":19.8166,"EUR":1}}');
        $result = $this->commissionCalculator->calculate($transactionFileContent);
        $resultTest = [2.0, 0.9040773890245005, 1.2303149606299213, 2.350601211463701, 47.495784749103514];
        $this->assertEquals($resultTest, $result);
    }
}