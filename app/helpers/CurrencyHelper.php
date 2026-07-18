<?php

/**
 * MyWisata Application - Currency Helper
 *
 * Handles multi-currency conversion and formatting.
 *
 * @version 1.0.0
 *
 * @since 2026-07-19
 */
class CurrencyHelper
{
    private static $rates = null;
    private static $userCurrency = null;

    /**
     * Initialize - load exchange rates and user preference
     */
    private static function init()
    {
        if (self::$rates !== null) {
            return;
        }

        $db = Database::getInstance();
        $rates = $db->query("SELECT * FROM exchange_rates WHERE is_active = 1")->fetchAll();

        self::$rates = [];
        foreach ($rates as $rate) {
            self::$rates[$rate['currency_code']] = $rate;
        }

        // Get user's preferred currency
        $userCurrency = Session::get('preferred_currency', 'IDR');
        if (!isset(self::$rates[$userCurrency])) {
            $userCurrency = 'IDR';
        }
        self::$userCurrency = $userCurrency;
    }

    /**
     * Convert amount from IDR to target currency
     *
     * @param float $amountIdr Amount in IDR
     * @param string|null $targetCurrency Target currency code (defaults to user's)
     * @return float Converted amount
     */
    public static function convert($amountIdr, $targetCurrency = null)
    {
        self::init();

        $currency = $targetCurrency ?: self::$userCurrency;

        if (!isset(self::$rates[$currency])) {
            return $amountIdr;
        }

        if ($currency === 'IDR') {
            return $amountIdr;
        }

        return $amountIdr / self::$rates[$currency]['rate_to_idr'];
    }

    /**
     * Format amount in target currency
     *
     * @param float $amountIdr Amount in IDR
     * @param string|null $targetCurrency Target currency code
     * @return string Formatted currency string
     */
    public static function format($amountIdr, $targetCurrency = null)
    {
        self::init();

        $currency = $targetCurrency ?: self::$userCurrency;

        if (!isset(self::$rates[$currency])) {
            $currency = 'IDR';
        }

        $rate = self::$rates[$currency];
        $converted = self::convert($amountIdr, $currency);

        if ($currency === 'IDR') {
            return $rate['currency_symbol'] . ' ' . number_format($converted, 0, ',', '.');
        }

        return $rate['currency_symbol'] . ' ' . number_format($converted, 2, '.', ',');
    }

    /**
     * Format with both IDR and converted currency
     *
     * @param float $amountIdr Amount in IDR
     * @return string Dual currency display
     */
    public static function formatDual($amountIdr)
    {
        self::init();

        $idrFormatted = 'Rp ' . number_format($amountIdr, 0, ',', '.');

        if (self::$userCurrency === 'IDR') {
            return $idrFormatted;
        }

        $converted = self::format($amountIdr);
        return $idrFormatted . ' <small class=\"text-muted\">(' . $converted . ')</small>';
    }

    /**
     * Get all active currencies
     *
     * @return array
     */
    public static function getCurrencies()
    {
        self::init();
        return self::$rates;
    }

    /**
     * Get user's preferred currency
     *
     * @return string
     */
    public static function getUserCurrency()
    {
        self::init();
        return self::$userCurrency;
    }

    /**
     * Set user's preferred currency (in session)
     *
     * @param string $currencyCode
     * @return void
     */
    public static function setUserCurrency($currencyCode)
    {
        self::init();

        if (isset(self::$rates[$currencyCode])) {
            Session::set('preferred_currency', $currencyCode);
            self::$userCurrency = $currencyCode;

            // Also update database if user is logged in
            $userId = Session::get('user_id');
            if ($userId) {
                $db = Database::getInstance();
                $db->query("UPDATE users SET preferred_currency = :currency WHERE id = :id", [
                    'currency' => $currencyCode,
                    'id' => $userId,
                ]);
            }
        }
    }

    /**
     * Get currency symbol
     *
     * @param string|null $currencyCode
     * @return string
     */
    public static function getSymbol($currencyCode = null)
    {
        self::init();
        $code = $currencyCode ?: self::$userCurrency;
        return self::$rates[$code]['currency_symbol'] ?? 'Rp';
    }

    /**
     * Convert from one currency to another
     *
     * @param float $amount Amount to convert
     * @param string $fromCurrency Source currency code
     * @param string $toCurrency Target currency code
     * @return float
     */
    public static function convertBetween($amount, $fromCurrency, $toCurrency)
    {
        self::init();

        if (!isset(self::$rates[$fromCurrency]) || !isset(self::$rates[$toCurrency])) {
            return $amount;
        }

        // Convert to IDR first, then to target
        $amountIdr = $amount * self::$rates[$fromCurrency]['rate_to_idr'];
        return self::convert($amountIdr, $toCurrency);
    }
}
