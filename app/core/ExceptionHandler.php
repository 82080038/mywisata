<?php

/**
 * MyWisata Application - Exception Handler
 *
 * Centralized exception handling for the application.
 *
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class ExceptionHandler
{
    /**
     * Handle exception
     *
     * @param Throwable $exception Exception to handle
     * @param bool $returnJson Whether to return JSON response
     * @return void
     */
    public static function handle(Throwable $exception, $returnJson = false)
    {
        // Log the exception
        self::logException($exception);

        // Determine response type based on request
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
                   strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        $wantsJson = isset($_GET['format']) && $_GET['format'] === 'json';

        if ($returnJson || $isAjax || $wantsJson) {
            self::handleJsonException($exception);
        } else {
            self::handleHtmlException($exception);
        }
    }

    /**
     * Log exception
     *
     * @param Throwable $exception Exception to log
     * @return void
     */
    private static function logException(Throwable $exception)
    {
        $logFile = defined('ERROR_LOG_FILE') ? ERROR_LOG_FILE : APP_ROOT . '/logs/error.log';
        $logDir = dirname($logFile);

        if (!is_dir($logDir)) {
            mkdir($logDir, 0777, true);
        }

        $timestamp = date('Y-m-d H:i:s');
        $message = sprintf(
            "[%s] %s: %s in %s on line %d\nStack trace:\n%s",
            $timestamp,
            get_class($exception),
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        );

        error_log($message, 3, $logFile);
    }

    /**
     * Handle exception for JSON responses
     *
     * @param Throwable $exception Exception to handle
     * @return void
     */
    private static function handleJsonException(Throwable $exception)
    {
        http_response_code(self::getHttpStatusCode($exception));

        $response = [
            'status' => 'error',
            'message' => self::getUserMessage($exception),
        ];

        // Add debug information in development
        if (APP_DEBUG) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Handle exception for HTML responses
     *
     * @param Throwable $exception Exception to handle
     * @return void
     */
    private static function handleHtmlException(Throwable $exception)
    {
        http_response_code(self::getHttpStatusCode($exception));

        if (APP_DEBUG) {
            // Show detailed error page in development
            self::showDetailedError($exception);
        } else {
            // Show generic error page in production
            self::showGenericError();
        }
    }

    /**
     * Get HTTP status code for exception
     *
     * @param Throwable $exception Exception
     * @return int
     */
    private static function getHttpStatusCode(Throwable $exception): int
    {
        if ($exception instanceof InvalidArgumentException) {
            return 400;
        }

        if ($exception instanceof RuntimeException) {
            return 500;
        }

        // Check for specific exception types
        $class = get_class($exception);

        if (strpos($class, 'NotFoundException') !== false ||
            strpos($class, 'NotFound') !== false) {
            return 404;
        }

        if (strpos($class, 'Auth') !== false ||
            strpos($class, 'Unauthorized') !== false) {
            return 401;
        }

        if (strpos($class, 'Forbidden') !== false) {
            return 403;
        }

        if (strpos($class, 'Validation') !== false) {
            return 422;
        }

        // Default to 500
        return 500;
    }

    /**
     * Get user-friendly message for exception
     *
     * @param Throwable $exception Exception
     * @return string
     */
    private static function getUserMessage(Throwable $exception): string
    {
        if (!APP_DEBUG) {
            return 'Terjadi kesalahan. Silakan coba lagi nanti.';
        }

        return $exception->getMessage();
    }

    /**
     * Show detailed error page (development only)
     *
     * @param Throwable $exception Exception
     * @return void
     */
    private static function showDetailedError(Throwable $exception)
    {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Error - MyWisata</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
                .error-container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h1 { color: #e74c3c; }
                .exception { background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0; }
                .trace { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 12px; }
                .file-line { color: #e74c3c; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class="error-container">
                <h1>Error</h1>
                <div class="exception">
                    <strong><?php echo htmlspecialchars(get_class($exception)); ?></strong><br>
                    <?php echo htmlspecialchars($exception->getMessage()); ?>
                </div>
                <p>
                    <strong>File:</strong> <span class="file-line"><?php echo htmlspecialchars($exception->getFile()); ?></span><br>
                    <strong>Line:</strong> <?php echo $exception->getLine(); ?>
                </p>
                <h3>Stack Trace</h3>
                <div class="trace"><?php echo htmlspecialchars($exception->getTraceAsString()); ?></div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Show generic error page (production)
     *
     * @return void
     */
    private static function showGenericError()
    {
        $errorPage = APP_ROOT . '/app/views/errors/500.php';

        if (file_exists($errorPage)) {
            require_once $errorPage;
        } else {
            http_response_code(500);
            echo '<!DOCTYPE html>
<html>
<head>
    <title>Error - MyWisata</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; text-align: center; }
        .error-container { background: white; padding: 60px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #e74c3c; font-size: 48px; margin: 0; }
        p { color: #666; font-size: 18px; }
        a { color: #3498db; text-decoration: none; }
    </style>
</head>
<body>
    <div class="error-container">
        <h1>Oops!</h1>
        <p>Terjadi kesalahan yang tidak terduga.</p>
        <p><a href="' . BASE_URL . '">Kembali ke Beranda</a></p>
    </div>
</body>
</html>';
        }
        exit;
    }

    /**
     * Register global exception handler
     *
     * @return void
     */
    public static function register()
    {
        set_exception_handler([self::class, 'handle']);
        set_error_handler([self::class, 'handleError']);
        register_shutdown_function([self::class, 'handleShutdown']);
    }

    /**
     * Handle PHP errors
     *
     * @param int $errno Error number
     * @param string $errstr Error message
     * @param string $errfile Error file
     * @param int $errline Error line
     * @return bool
     */
    public static function handleError($errno, $errstr, $errfile, $errline)
    {
        if (!(error_reporting() & $errno)) {
            return false;
        }

        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    }

    /**
     * Handle fatal errors
     *
     * @return void
     */
    public static function handleShutdown()
    {
        $error = error_get_last();

        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            self::handle(new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']));
        }
    }
}
