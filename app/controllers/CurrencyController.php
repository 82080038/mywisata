<?php

/**
 * MyWisata Application - Currency Controller
 *
 * Handles currency switching and price scanning.
 *
 * @version 1.0.0
 *
 * @since 2026-07-19
 */
class CurrencyController extends Controller
{
    /**
     * Switch user's preferred currency
     */
    public function switch()
    {
        $currency = $this->post('currency');

        if (empty($currency)) {
            $currency = $this->get('currency');
        }

        if (empty($currency)) {
            if ($this->isAjax()) {
                $this->json(['status' => 'error', 'message' => 'Currency code required'], 400);
            }
            $this->redirectBack();
        }

        CurrencyHelper::setUserCurrency($currency);

        if ($this->isAjax()) {
            $this->json([
                'status' => 'success',
                'message' => 'Currency switched to ' . $currency,
                'currency' => $currency,
                'symbol' => CurrencyHelper::getSymbol($currency),
            ]);
        }

        $this->redirectBack();
    }

    /**
     * Get current exchange rates
     */
    public function rates()
    {
        $currencies = CurrencyHelper::getCurrencies();
        $userCurrency = CurrencyHelper::getUserCurrency();

        $this->json([
            'status' => 'success',
            'user_currency' => $userCurrency,
            'currencies' => array_values($currencies),
        ]);
    }

    /**
     * Convert amount - AJAX endpoint
     */
    public function convert()
    {
        if (!$this->isAjax()) {
            $this->redirect('home');
        }

        $amount = (float) $this->post('amount', 0);
        $from = $this->post('from', 'IDR');
        $to = $this->post('to');

        if (empty($to)) {
            $to = CurrencyHelper::getUserCurrency();
        }

        $converted = CurrencyHelper::convertBetween($amount, $from, $to);
        $formatted = CurrencyHelper::format($amount, $to);

        $this->json([
            'status' => 'success',
            'original_amount' => $amount,
            'original_currency' => $from,
            'converted_amount' => round($converted, 2),
            'target_currency' => $to,
            'formatted' => $formatted,
        ]);
    }

    /**
     * Price scanner page - camera + OCR
     */
    public function scanner()
    {
        $currencies = CurrencyHelper::getCurrencies();
        $userCurrency = CurrencyHelper::getUserCurrency();

        $data = [
            'title' => 'Scan Harga - MyWisata',
            'currencies' => $currencies,
            'user_currency' => $userCurrency,
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('currency/scanner', $data);
    }
}
