<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Currency;
use Illuminate\Support\Facades\Log;

class ApiCurrencyExchangeController extends Controller
{
    /**
     * Get all currencies with their current rates
     */
    public function index()
    {
        try {
            $currencies = Currency::all();

            return response()->json([
                'success' => true,
                'data' => $currencies,
                'message' => 'تم جلب العملات بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching currencies: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب العملات'
            ], 500);
        }
    }

    /**
     * Get base currency
     */
    public function getBaseCurrency()
    {
        try {
            $baseCurrency = Currency::getBaseCurrency();

            if (!$baseCurrency) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تعيين عملة أساسية'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $baseCurrency,
                'message' => 'تم جلب العملة الأساسية بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching base currency: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب العملة الأساسية'
            ], 500);
        }
    }

    /**
     * Set base currency
     */
    public function setBaseCurrency(Request $request)
    {
        try {
            $request->validate([
                'currency_code' => 'required|string|exists:currencies,code'
            ]);

            // Reset all currencies to not base
            Currency::query()->update(['is_base' => false]);

            // Set the selected currency as base
            $currency = Currency::where('code', $request->currency_code)->first();
            $currency->is_base = true;
            $currency->rate = 1; // Base currency always has rate of 1
            $currency->save();

            return response()->json([
                'success' => true,
                'data' => $currency,
                'message' => 'تم تعيين العملة الأساسية بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Error setting base currency: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تعيين العملة الأساسية'
            ], 500);
        }
    }

    /**
     * Manually trigger exchange rates update
     */
    public function updateRates()
    {
        try {
            // Run the artisan command
            Artisan::call('currencies:update');
            $output = Artisan::output();

            // Check if command was successful
            if (strpos($output, 'Exchange rates updated successfully') !== false) {
                return response()->json([
                    'success' => true,
                    'message' => 'تم تحديث أسعار الصرف بنجاح',
                    'output' => $output
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'فشل في تحديث أسعار الصرف',
                    'output' => $output
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('Error updating exchange rates: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث أسعار الصرف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get exchange rate history (if you have a history table)
     */
    public function getHistory(Request $request)
    {
        try {
            $request->validate([
                'currency_code' => 'required|string|exists:currencies,code',
                'days' => 'integer|min:1|max:365'
            ]);

            $days = $request->get('days', 30);
            $currencyCode = $request->get('currency_code');

            // This would require a currency_history table
            // For now, just return current rate
            $currency = Currency::where('code', $currencyCode)->first();

            return response()->json([
                'success' => true,
                'data' => [
                    'currency' => $currency,
                    'history' => [
                        [
                            'date' => now()->format('Y-m-d'),
                            'rate' => $currency->rate
                        ]
                    ]
                ],
                'message' => 'تم جلب تاريخ أسعار الصرف بنجاح'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching exchange rate history: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب تاريخ أسعار الصرف'
            ], 500);
        }
    }

    /**
     * Run artisan command via API (admin only)
     */
    public function runArtisanCommand(Request $request)
    {
        try {
            $request->validate([
                'command' => 'required|string'
            ]);

            $command = $request->get('command');

            // Security: Only allow specific commands
            $allowedCommands = [
                'currencies:update',
                'cache:clear',
                'config:cache'
            ];

            if (!in_array($command, $allowedCommands)) {
                return response()->json([
                    'success' => false,
                    'message' => 'الأمر غير مسموح'
                ], 403);
            }

            Artisan::call($command);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'تم تنفيذ الأمر بنجاح',
                'command' => $command,
                'output' => $output
            ]);
        } catch (\Exception $e) {
            Log::error('Error running artisan command: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تنفيذ الأمر',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
