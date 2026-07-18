<?php

/**
 * MyWisata Application - Email Helper
 *
 * Handles email sending functionality.
 *
 * @version 1.0.0
 *
 * @since 2026-07-01
 */
class Email
{
    /**
     * Send email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $body Email body
     * @param array $headers Optional headers
     *
     * @return bool
     */
    public static function send($to, $subject, $body, $headers = [])
    {
        $defaultHeaders = [
            'From' => defined('EMAIL_FROM') ? EMAIL_FROM : 'noreply@mywisata.com',
            'MIME-Version' => '1.0',
            'Content-Type' => 'text/html; charset=UTF-8',
        ];

        $headers = array_merge($defaultHeaders, $headers);

        $headerString = '';

        foreach ($headers as $key => $value) {
            $headerString .= $key . ': ' . $value . "\r\n";
        }

        $result = mail($to, $subject, $body, $headerString);

        if ($result) {
            Logger::info('Email sent', ['to' => $to, 'subject' => $subject]);
        } else {
            Logger::error('Failed to send email', ['to' => $to, 'subject' => $subject]);
        }

        return $result;
    }

    /**
     * Send booking confirmation email
     *
     * @param string $to Recipient email
     * @param array $bookingData Booking data
     *
     * @return bool
     */
    public static function sendBookingConfirmation($to, $bookingData)
    {
        $subject = 'Konfirmasi Booking - MyWisata';
        $body = self::renderTemplate('booking_confirmation', $bookingData);

        return self::send($to, $subject, $body);
    }

    /**
     * Send password reset email
     *
     * @param string $to Recipient email
     * @param string $resetLink Password reset link
     *
     * @return bool
     */
    public static function sendPasswordReset($to, $resetLink)
    {
        $subject = 'Reset Password - MyWisata';
        $body = self::renderTemplate('password_reset', ['reset_link' => $resetLink]);

        return self::send($to, $subject, $body);
    }

    /**
     * Send payment success email
     *
     * @param string $to Recipient email
     * @param array $paymentData Payment data
     *
     * @return bool
     */
    public static function sendPaymentSuccess($to, $paymentData)
    {
        $subject = 'Pembayaran Berhasil - MyWisata';
        $body = self::renderTemplate('payment_success', $paymentData);

        return self::send($to, $subject, $body);
    }

    /**
     * Send welcome email to new user
     *
     * @param string $to Recipient email
     * @param string $name User name
     *
     * @return bool
     */
    public static function sendWelcome($to, $name)
    {
        $subject = 'Selamat Datang di MyWisata';
        $body = '<!DOCTYPE html><html><body style="font-family:Arial,sans-serif;background:#f8f9fa;padding:20px;">';
        $body .= '<div style="max-width:600px;margin:0 auto;background:#fff;padding:30px;border-radius:8px;">';
        $body .= '<h2 style="color:#0d6efd;text-align:center;">Selamat Datang!</h2>';
        $body .= '<p>Halo ' . htmlspecialchars($name) . ',</p>';
        $body .= '<p>Selamat datang di MyWisata! Akun Anda telah berhasil dibuat.</p>';
        $body .= '<p>Sekarang Anda dapat:</p>';
        $body .= '<ul><li>Membooking destinasi wisata</li><li>Menyewa tour guide</li><li>Membeli tiket</li><li>Memberikan review</li></ul>';
        $body .= '<p>Terima kasih,<br>Tim MyWisata</p>';
        $body .= '</div></body></html>';

        return self::send($to, $subject, $body);
    }

    /**
     * Render email template
     *
     * @param string $template Template name
     * @param array $data Template data
     *
     * @return string
     */
    private static function renderTemplate($template, $data)
    {
        $templatePath = APP_ROOT . '/app/views/emails/' . $template . '.php';

        if (!file_exists($templatePath)) {
            return '<p>Email template not found.</p>';
        }

        extract($data);
        ob_start();
        include $templatePath;

        return ob_get_clean();
    }
}
