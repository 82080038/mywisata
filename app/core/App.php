<?php

/**
 * MyWisata Application - App Class
 *
 * Main application class that handles routing and request processing.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class App
{
    private $controller = 'Home';
    private $method = 'index';
    private $params = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // Constructor is now empty; routing is done in route()
    }

    /**
     * Parse URL and route request
     */
    public function route()
    {
        $url = $this->parseUrl();

        // Check if controller exists
        if (!empty($url) && file_exists(APP_ROOT . '/app/controllers/' . ucfirst($url[0]) . 'Controller.php')) {
            $this->controller = ucfirst($url[0]);
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'api') {
            // API routes
            $this->controller = 'Api';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'destinations' || $url[0] === 'destination')) {
            $this->controller = 'Destination';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'tourguides' || $url[0] === 'tourguide')) {
            $this->controller = 'TourGuide';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'hotels' || $url[0] === 'hotel')) {
            $this->controller = 'Hotel';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'restaurants' || $url[0] === 'restaurant')) {
            $this->controller = 'Restaurant';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'events' || $url[0] === 'event')) {
            $this->controller = 'Event';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'bookings' || $url[0] === 'booking')) {
            $this->controller = 'Booking';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'tickets' || $url[0] === 'ticket')) {
            $this->controller = 'Ticket';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'notifications' || $url[0] === 'notification')) {
            $this->controller = 'Notification';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'payments' || $url[0] === 'payment')) {
            $this->controller = 'Payment';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'favorites' || $url[0] === 'favorite')) {
            $this->controller = 'Favorite';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'search') {
            $this->controller = 'Search';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'cart') {
            $this->controller = 'Cart';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'dashboard') {
            $this->controller = 'Dashboard';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'map') {
            $this->controller = 'Map';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'report' || $url[0] === 'reports')) {
            $this->controller = 'Report';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'aitourguide') {
            $this->controller = 'AITourGuide';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'audioguide') {
            $this->controller = 'AudioGuide';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'currency') {
            $this->controller = 'Currency';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'itinerary' || $url[0] === 'itineraries')) {
            $this->controller = 'Itinerary';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'desawisata' || $url[0] === 'village')) {
            $this->controller = 'DesaWisata';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'package' || $url[0] === 'packages')) {
            $this->controller = 'Package';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'mastertable') {
            $this->controller = 'MasterTable';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'translation' || $url[0] === 'translate')) {
            $this->controller = 'Translation';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'products' || $url[0] === 'product')) {
            $this->controller = 'Product';
            unset($url[0]);
        } elseif (!empty($url) && $url[0] === 'merchant') {
            $this->controller = 'Merchant';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'messages' || $url[0] === 'message')) {
            $this->controller = 'Message';
            unset($url[0]);
        } elseif (!empty($url) && ($url[0] === 'video' || $url[0] === 'videos')) {
            $this->controller = 'Video';
            unset($url[0]);
        } elseif (!empty($url)) {
            // Non-existent route - return 404
            $this->handle404();
            return;
        } else {
            // Default to Home controller
            $this->controller = 'Home';
        }

        // Require controller file
        $controllerFile = APP_ROOT . '/app/controllers/' . $this->controller . 'Controller.php';

        if (file_exists($controllerFile)) {
            require_once $controllerFile;
            // Instantiate controller with full class name
            $controllerClass = $this->controller . 'Controller';
            $this->controller = new $controllerClass();
        } else {
            die('Controller not found: ' . $this->controller . 'Controller.php');
        }

        // Check if method exists
        if (!empty($url) && isset($url[1]) && method_exists($this->controller, $url[1])) {
            $this->method = $url[1];
            unset($url[1]);
        }

        // Get parameters
        $this->params = !empty($url) ? array_values($url) : [];

        // Call controller method with parameters
        call_user_func_array([$this->controller, $this->method], $this->params);
    }

    /**
     * Parse URL from GET parameter
     *
     * @return array URL segments
     */
    private function parseUrl()
    {
        if (isset($_GET['url'])) {
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);

            return $url;
        }

        return [];
    }

    /**
     * Run the application
     */
    public function run()
    {
        try {
            // Wrap with access logging middleware
            AccessLogMiddleware::handle(function () {
                $this->route();
            });
        } catch (Exception $e) {
            $this->handleError($e);
        }
    }

    /**
     * Handle errors
     *
     * @param Exception $e Exception object
     */
    private function handleError($e)
    {
        if (APP_DEBUG) {
            echo '<h1>Error</h1>';
            echo '<p>' . $e->getMessage() . '</p>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        } else {
            require_once APP_ROOT . '/app/views/errors/500.php';
        }
    }

    /**
     * Handle 404 errors
     */
    private function handle404()
    {
        http_response_code(404);
        if (APP_DEBUG) {
            echo '<h1>404 Not Found</h1>';
            echo '<p>The page you requested could not be found.</p>';
        } else {
            require_once APP_ROOT . '/app/views/errors/404.php';
        }
    }
}
