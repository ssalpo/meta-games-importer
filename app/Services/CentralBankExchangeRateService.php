<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

final class CentralBankExchangeRateService
{
    public function usdToRubRate(): float
    {
        return (float) Cache::remember(
            'currency_rate:official:USD:RUB',
            now()->addHours(6),
            fn (): float => $this->fetchRate('USD'),
        );
    }

    public function usdToRub(float $amount): float
    {
        return round($amount * $this->usdToRubRate(), 2);
    }

    private function fetchRate(string $currencyCode): float
    {
        $response = Http::timeout(15)
            ->retry(2, 500)
            ->get((string) config('services.cbr.daily_xml_url'));

        if (! $response->successful() || $response->body() === '') {
            throw new RuntimeException('Не удалось получить официальный курс валют ЦБ РФ.');
        }

        $xml = simplexml_load_string($response->body());

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('ЦБ РФ вернул неожиданный формат курса валют.');
        }

        foreach ($xml->Valute as $currency) {
            if ((string) $currency->CharCode !== $currencyCode) {
                continue;
            }

            $nominal = (float) str_replace(',', '.', (string) $currency->Nominal);
            $value = (float) str_replace(',', '.', (string) $currency->Value);

            if ($nominal <= 0 || $value <= 0) {
                throw new RuntimeException('ЦБ РФ вернул некорректный курс USD.');
            }

            return $value / $nominal;
        }

        throw new RuntimeException('Курс USD не найден в ответе ЦБ РФ.');
    }
}
