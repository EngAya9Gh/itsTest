<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Currency;
use Illuminate\Support\Facades\Http;

class UpdateExchangeRates extends Command
{
    protected $signature = 'currencies:update';
    protected $description = 'Update currency exchange rates from ExchangeRate-API.com';

    public function handle()
    {
      $baseCurrency = Currency::getBaseCurrency();

    if (!$baseCurrency) {
        $this->error('Base currency not set!');
        return;
    }

    $apiKey ="2ae6ba4b45bdc5e750ac7312";
    $baseCode = $baseCurrency->code;

    $url = "https://v6.exchangerate-api.com/v6/{$apiKey}/latest/{$baseCode}";

    $response = Http::get($url);

        if ($response->failed()) {
            $this->error('Failed to fetch rates.');
            return;
        }

        $data = $response->json();

        if (($data['result'] ?? '') !== 'success') {
            $this->error('API error: ' . ($data['error-type'] ?? 'unknown'));
            return;
        }

        $rates = $data['conversion_rates'] ?? null;

        if (!$rates) {
            $this->error('No conversion rates found.');
            return;
        }

        foreach (Currency::all() as $currency) {
            if ($currency->code === $baseCurrency) {
                $currency->rate = 1;
                $currency->is_base = true;
            } else {
                $currency->rate = $rates[$currency->code] ?? $currency->rate;
                $currency->is_base = false;
            }
            $currency->save();
            $this->info("Updated rate for {$currency->code} to {$currency->rate}");
        }

        $this->info('Exchange rates updated successfully.');
    }
}
