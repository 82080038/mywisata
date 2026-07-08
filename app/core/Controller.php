<?php

/**
 * MyWisata Application - Controller Class
 *
 * Base controller class that all controllers extend.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class Controller
{
    protected $db;

    /**
     * Constructor - Initialize database connection
     */
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Load a model
     *
     * @param string $model Model name
     *
     * @return Model
     */
    public function model($model)
    {
        require_once APP_ROOT . '/app/models/' . $model . '.php';

        return new $model();
    }

    /**
     * Load a view
     *
     * @param string $view View file path
     * @param array $data Data to pass to view
     */
    public function view($view, $data = [])
    {
        if (file_exists(APP_ROOT . '/app/views/' . $view . '.php')) {
            extract($data);
            require_once APP_ROOT . '/app/views/' . $view . '.php';
        } else {
            die('View not found: ' . $view);
        }
    }

    /**
     * Load a service
     *
     * @param string $service Service name
     *
     * @return Service
     */
    public function service($service)
    {
        require_once APP_ROOT . '/app/services/' . $service . '.php';

        return new $service();
    }

    /**
     * Return JSON response
     *
     * @param array $data Response data
     * @param int $statusCode HTTP status code
     */
    public function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to URL
     *
     * @param string $url URL to redirect to
     */
    public function redirect($url)
    {
        header('Location: ' . BASE_URL . $url);
        exit;
    }

    /**
     * Check if request is AJAX
     *
     * @return bool
     */
    protected function isAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    /**
     * Get POST data
     *
     * @param string $key Key to retrieve
     * @param mixed $default Default value
     *
     * @return mixed
     */
    protected function post($key = null, $default = null)
    {
        if ($key === null) {
            return $_POST;
        }

        return isset($_POST[$key]) ? $_POST[$key] : $default;
    }

    /**
     * Get GET data
     *
     * @param string $key Key to retrieve
     * @param mixed $default Default value
     *
     * @return mixed
     */
    protected function get($key = null, $default = null)
    {
        if ($key === null) {
            return $_GET;
        }

        return isset($_GET[$key]) ? $_GET[$key] : $default;
    }

    /**
     * Validate CSRF token
     *
     * @return bool
     */
    protected function validateCsrf()
    {
        $token = $this->post('csrf_token');

        return $token === CSRF_TOKEN;
    }

    /**
     * Execute callback with error handling
     *
     * @param callable $callback Callback to execute
     * @param string $errorMessage Custom error message
     * @param int $errorCode Error code
     *
     * @return mixed
     */
    protected function withErrorHandling($callback, $errorMessage = 'Terjadi kesalahan', $errorCode = 500)
    {
        try {
            return $callback();
        } catch (PDOException $e) {
            Logger::error('Database Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($this->isAjax()) {
                $this->json([
                    'status' => 'error',
                    'message' => APP_DEBUG ? $e->getMessage() : 'Terjadi kesalahan database',
                ], $errorCode);
            }

            Session::flash('error', APP_DEBUG ? $e->getMessage() : $errorMessage);
            $this->redirect('home');
        } catch (Exception $e) {
            Logger::error('Application Error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if ($this->isAjax()) {
                $this->json([
                    'status' => 'error',
                    'message' => APP_DEBUG ? $e->getMessage() : $errorMessage,
                ], $errorCode);
            }

            Session::flash('error', APP_DEBUG ? $e->getMessage() : $errorMessage);
            $this->redirect('home');
        }
    }

    /**
     * Validate required fields
     *
     * @param array $fields Fields to validate
     * @param array $data Data to validate
     *
     * @return array Validation errors
     */
    protected function validateRequired($fields, $data)
    {
        $errors = [];

        foreach ($fields as $field) {
            if (!isset($data[$field]) || empty(trim($data[$field]))) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' wajib diisi';
            }
        }

        return $errors;
    }

    /**
     * Send validation error response
     *
     * @param array $errors Validation errors
     * @param int $statusCode HTTP status code
     *
     * @return void
     */
    protected function validationError($errors, $statusCode = 400)
    {
        if ($this->isAjax()) {
            $this->json([
                'status' => 'error',
                'message' => is_array($errors) ? implode(', ', $errors) : $errors,
                'errors' => is_array($errors) ? $errors : [$errors],
            ], $statusCode);
        }

        Session::flash('error', is_array($errors) ? implode(', ', $errors) : $errors);
        $this->redirect($_SERVER['HTTP_REFERER'] ?? 'home');
    }

    /**
     * Send success response
     *
     * @param string $message Success message
     * @param array $data Additional data
     * @param string $redirectUrl Redirect URL
     *
     * @return void
     */
    protected function successResponse($message, $data = [], $redirectUrl = null)
    {
        if ($this->isAjax()) {
            $this->json([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
                'redirect' => $redirectUrl ? BASE_URL . $redirectUrl : null,
            ]);
        }

        Session::flash('success', $message);

        if ($redirectUrl) {
            $this->redirect($redirectUrl);
        }
    }

    /**
     * Send error response
     *
     * @param string $message Error message
     * @param int $statusCode HTTP status code
     * @param string $redirectUrl Redirect URL
     *
     * @return void
     */
    protected function errorResponse($message, $statusCode = 400, $redirectUrl = null)
    {
        if ($this->isAjax()) {
            $this->json([
                'status' => 'error',
                'message' => $message,
            ], $statusCode);
        }

        Session::flash('error', $message);

        if ($redirectUrl) {
            $this->redirect($redirectUrl);
        } else {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? 'home');
        }
    }

    /**
     * Check if user is authenticated
     *
     * @return bool
     */
    protected function isAuthenticated()
    {
        return Session::get('user_id') !== null;
    }

    /**
     * Get current user ID
     *
     * @return int|null
     */
    protected function currentUserId()
    {
        return Session::get('user_id');
    }

    /**
     * Get current user role
     *
     * @return string|null
     */
    protected function currentUserRole()
    {
        return Session::get('role');
    }
}
