<?php

/**
 * MyWisata Application - Auth Controller
 *
 * Handles authentication: login, register, logout.
 *
 * @version 1.0.0
 *
 * @since 2026-06-30
 */
class AuthController extends Controller
{
    private $userModel;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->userModel = $this->model('User');
    }

    /**
     * Login page
     */
    public function login()
    {
        // Redirect if already logged in
        if (Middleware::isAuthenticated()) {
            $this->redirect('dashboard');
        }

        $data = [
            'title' => 'Masuk - MyWisata',
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('auth/login', $data);
    }

    /**
     * Handle login submission
     */
    public function doLogin()
    {
        if (!$this->isAjax()) {
            $this->redirect('auth/login');
        }

        // Apply rate limiting: 5 login attempts per minute per IP
        $ip = RateLimiter::getClientIP();
        if (!RateLimiter::allow('login:' . $ip, 5, 60)) {
            $remaining = RateLimiter::getRemaining('login:' . $ip);
            $this->json([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan login. Silakan tunggu ' . ceil(($remaining['reset_time'] - time()) / 60) . ' menit.',
            ], 429);
        }

        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $email = $this->post('email');
        $password = $this->post('password');
        $remember = $this->post('remember') === 'true';

        // Validate input
        if (empty($email) || empty($password)) {
            $this->json(['status' => 'error', 'message' => 'Email dan password wajib diisi'], 400);
        }

        // Check account lockout
        $lockoutKey = 'lockout:' . $email;
        $lockoutData = self::getLockoutData($lockoutKey);
        
        if ($lockoutData && $lockoutData['locked_until'] > time()) {
            $remainingMinutes = ceil(($lockoutData['locked_until'] - time()) / 60);
            $this->json([
                'status' => 'error',
                'message' => "Akun dikunci karena terlalu banyak percobaan gagal. Silakan tunggu {$remainingMinutes} menit.",
            ], 423);
        }

        // Verify credentials
        $user = $this->userModel->verify($email, $password);

        if (!$user) {
            // Increment failed login attempts
            $this->incrementFailedAttempts($email);
            
            $attempts = $this->getFailedAttempts($email);
            $remainingAttempts = 5 - $attempts;
            
            if ($remainingAttempts > 0) {
                $this->json([
                    'status' => 'error',
                    'message' => "Email atau password salah. {$remainingAttempts} percobaan tersisa.",
                ], 401);
            } else {
                $this->json([
                    'status' => 'error',
                    'message' => 'Akun dikunci karena terlalu banyak percobaan gagal. Silakan tunggu 15 menit.',
                ], 423);
            }
        }

        // Clear failed attempts and lockout on successful login
        $this->clearFailedAttempts($email);
        RateLimiter::clear('login:' . $ip);

        // Set session
        Session::set('user_id', $user['id']);
        Session::set('user_name', $user['name']);
        Session::set('user_email', $user['email']);
        Session::set('role', $user['role']);

        // Update last login
        $this->userModel->updateLastLogin($user['id']);

        // Set remember token if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            Session::set('remember_token', $token);
            // In production, store token in database and set cookie
        }

        // Redirect based on role
        $redirect = 'dashboard';

        if ($user['role'] === 'admin') {
            $redirect = 'admin/dashboard';
        } elseif ($user['role'] === 'tour_guide') {
            $redirect = 'tourguide/dashboard';
        }

        $this->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'redirect' => BASE_URL . $redirect,
        ]);
    }

    /**
     * Get failed login attempts for email
     *
     * @param string $email
     * @return int
     */
    private function getFailedAttempts($email)
    {
        $cacheFile = APP_ROOT . '/cache/login_attempts/' . md5($email) . '.json';
        
        if (!is_dir(APP_ROOT . '/cache/login_attempts')) {
            mkdir(APP_ROOT . '/cache/login_attempts', 0777, true);
        }

        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true);
            
            // Reset if expired (15 minutes)
            if ($data['last_attempt'] < time() - 900) {
                return 0;
            }
            
            return $data['attempts'];
        }

        return 0;
    }

    /**
     * Increment failed login attempts
     *
     * @param string $email
     * @return void
     */
    private function incrementFailedAttempts($email)
    {
        $cacheFile = APP_ROOT . '/cache/login_attempts/' . md5($email) . '.json';
        
        if (!is_dir(APP_ROOT . '/cache/login_attempts')) {
            mkdir(APP_ROOT . '/cache/login_attempts', 0777, true);
        }

        $attempts = $this->getFailedAttempts($email) + 1;
        
        // Lock account after 5 failed attempts for 15 minutes
        if ($attempts >= 5) {
            $lockoutKey = 'lockout:' . $email;
            $lockoutData = [
                'locked_until' => time() + 900, // 15 minutes
                'attempts' => $attempts,
                'last_attempt' => time(),
            ];
            
            $lockoutFile = APP_ROOT . '/cache/login_attempts/' . md5($lockoutKey) . '.json';
            file_put_contents($lockoutFile, json_encode($lockoutData));
            
            Logger::warning('Account locked due to failed login attempts', ['email' => $email, 'attempts' => $attempts]);
        }

        $data = [
            'attempts' => $attempts,
            'last_attempt' => time(),
        ];

        file_put_contents($cacheFile, json_encode($data));
    }

    /**
     * Clear failed login attempts
     *
     * @param string $email
     * @return void
     */
    private function clearFailedAttempts($email)
    {
        $cacheFile = APP_ROOT . '/cache/login_attempts/' . md5($email) . '.json';
        $lockoutFile = APP_ROOT . '/cache/login_attempts/' . md5('lockout:' . $email) . '.json';

        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        if (file_exists($lockoutFile)) {
            unlink($lockoutFile);
        }
    }

    /**
     * Get lockout data for email
     *
     * @param string $lockoutKey
     * @return array|null
     */
    private function getLockoutData($lockoutKey)
    {
        $lockoutFile = APP_ROOT . '/cache/login_attempts/' . md5($lockoutKey) . '.json';

        if (file_exists($lockoutFile)) {
            $data = json_decode(file_get_contents($lockoutFile), true);
            
            // Clear if expired
            if ($data['locked_until'] < time()) {
                unlink($lockoutFile);
                return null;
            }
            
            return $data;
        }

        return null;
    }

    /**
     * Register page
     */
    public function register()
    {
        // Redirect if already logged in
        if (Middleware::isAuthenticated()) {
            $this->redirect('dashboard');
        }

        $data = [
            'title' => 'Daftar - MyWisata',
            'csrf_token' => Middleware::csrfToken(),
            'role' => $this->get('role', 'wisatawan'), // wisatawan or tour_guide
        ];

        $this->view('auth/register', $data);
    }

    /**
     * Handle registration submission
     */
    public function doRegister()
    {
        if (!$this->isAjax()) {
            $this->redirect('auth/register');
        }

        // Apply rate limiting: 3 registration attempts per minute per IP
        $ip = RateLimiter::getClientIP();
        if (!RateLimiter::allow('register:' . $ip, 3, 60)) {
            $remaining = RateLimiter::getRemaining('register:' . $ip);
            $this->json([
                'status' => 'error',
                'message' => 'Terlalu banyak percobaan registrasi. Silakan tunggu ' . ceil(($remaining['reset_time'] - time()) / 60) . ' menit.',
            ], 429);
        }

        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $name = $this->post('name');
        $email = $this->post('email');
        $password = $this->post('password');
        $passwordConfirm = $this->post('password_confirm');
        $phone = $this->post('phone');
        $role = $this->post('role', 'wisatawan');

        // Validate input
        $errors = [];

        if (empty($name)) {
            $errors[] = 'Nama wajib diisi';
        }

        if (empty($email)) {
            $errors[] = 'Email wajib diisi';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        }

        if (empty($password)) {
            $errors[] = 'Password wajib diisi';
        } elseif (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }

        if ($password !== $passwordConfirm) {
            $errors[] = 'Konfirmasi password tidak cocok';
        }

        if (!empty($errors)) {
            $this->json(['status' => 'error', 'message' => implode(', ', $errors)], 400);
        }

        // Check if email already exists
        if ($this->userModel->findByEmail($email)) {
            $this->json(['status' => 'error', 'message' => 'Email sudah terdaftar'], 409);
        }

        // Register user
        $userId = $this->userModel->register([
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'password' => $password,
            'role' => $role,
            'status' => 'active',
            'email_verified' => 0,
        ]);

        if ($userId) {
            // Save food preferences if provided
            $allergies = $this->post('allergies', []);
            $dietaryPrefs = $this->post('dietary_prefs', []);
            if (!empty($allergies) || !empty($dietaryPrefs)) {
                if (!is_array($allergies)) $allergies = [];
                if (!is_array($dietaryPrefs)) $dietaryPrefs = [];
                $this->userModel->updateFoodPreferences($userId, $allergies, $dietaryPrefs, '');
            }

            // Clear rate limit on successful registration
            RateLimiter::clear('register:' . $ip);

            // Send welcome email
            try {
                Email::sendWelcome($email, $name);
            } catch (Exception $e) {
                Logger::error('Welcome email failed', ['error' => $e->getMessage(), 'email' => $email]);
            }

            $this->json([
                'status' => 'success',
                'message' => 'Registrasi berhasil. Silakan login.',
                'redirect' => BASE_URL . 'auth/login',
            ]);
        } else {
            $this->json(['status' => 'error', 'message' => 'Gagal mendaftar. Silakan coba lagi.'], 500);
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        Session::destroy();
        $this->redirect('auth/login');
    }

    /**
     * Forgot password page
     */
    public function forgotPassword()
    {
        $data = [
            'title' => 'Lupa Password - MyWisata',
            'csrf_token' => Middleware::csrfToken(),
        ];

        $this->view('auth/forgot-password', $data);
    }

    /**
     * Handle forgot password
     */
    public function doForgotPassword()
    {
        if (!$this->isAjax()) {
            $this->redirect('auth/forgot-password');
        }

        // Apply rate limiting: 3 attempts per minute per IP
        $ip = RateLimiter::getClientIP();
        if (!RateLimiter::allow('forgot_password:' . $ip, 3, 60)) {
            $remaining = RateLimiter::getRemaining('forgot_password:' . $ip);
            $this->json([
                'status' => 'error',
                'message' => 'Terlalu banyak permintaan reset password. Silakan tunggu ' . ceil(($remaining['reset_time'] - time()) / 60) . ' menit.',
            ], 429);
        }

        // Verify CSRF
        if (!$this->validateCsrf()) {
            $this->json(['status' => 'error', 'message' => 'CSRF token mismatch'], 419);
        }

        $email = $this->post('email');

        if (empty($email)) {
            $this->json(['status' => 'error', 'message' => 'Email wajib diisi'], 400);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            // Don't reveal if email exists or not
            $this->json([
                'status' => 'success',
                'message' => 'Jika email terdaftar, link reset password akan dikirim.',
            ]);
        }

        // TODO: Send reset email with token
        // For now, just return success
        $this->json([
            'status' => 'success',
            'message' => 'Jika email terdaftar, link reset password akan dikirim.',
        ]);
    }
}
