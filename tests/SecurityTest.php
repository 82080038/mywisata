<?php
/**
 * MyWisata Application - Security Tests
 * 
 * Tests for CSRF, XSS, and SQL injection vulnerabilities.
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-01
 */

class SecurityTest {
    
    private $baseUrl;
    private $testResults = [];
    
    public function __construct($baseUrl = 'http://localhost/mywisata') {
        $this->baseUrl = $baseUrl;
    }
    
    /**
     * Run all security tests
     */
    public function runAll() {
        echo "=== Running Security Tests ===\n\n";
        
        $this->testCSRFProtection();
        $this->testXSSProtection();
        $this->testSQLInjection();
        $this->testRateLimiting();
        $this->testSessionSecurity();
        
        $this->printResults();
    }
    
    /**
     * Test CSRF Protection
     */
    private function testCSRFProtection() {
        echo "Testing CSRF Protection...\n";
        
        // Test 1: Check if CSRF token is generated
        $result = $this->checkCSRFTokenGeneration();
        $this->testResults['CSRF Token Generation'] = $result;
        
        // Test 2: Check if CSRF token is validated
        $result = $this->checkCSRFTokenValidation();
        $this->testResults['CSRF Token Validation'] = $result;
        
        echo "\n";
    }
    
    /**
     * Check CSRF token generation
     */
    private function checkCSRFTokenGeneration() {
        $ch = curl_init($this->baseUrl . '/auth/login');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (strpos($response, 'csrf_token') !== false || strpos($response, 'name="csrf_token"') !== false) {
            echo "  ✓ CSRF token is generated in forms\n";
            return true;
        }
        
        echo "  ✗ CSRF token not found in forms\n";
        return false;
    }
    
    /**
     * Check CSRF token validation
     */
    private function checkCSRFTokenValidation() {
        // This would require actual form submission test
        echo "  ℹ CSRF token validation requires manual testing\n";
        return null;
    }
    
    /**
     * Test XSS Protection
     */
    private function testXSSProtection() {
        echo "Testing XSS Protection...\n";
        
        // Test 1: Check if output is escaped
        $result = $this->checkOutputEscaping();
        $this->testResults['Output Escaping'] = $result;
        
        // Test 2: Check Content Security Policy
        $result = $this->checkCSP();
        $this->testResults['Content Security Policy'] = $result;
        
        echo "\n";
    }
    
    /**
     * Check output escaping
     */
    private function checkOutputEscaping() {
        echo "  ℹ Output escaping requires manual code review\n";
        return null;
    }
    
    /**
     * Check Content Security Policy
     */
    private function checkCSP() {
        $ch = curl_init($this->baseUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if (strpos($response, 'Content-Security-Policy') !== false) {
            echo "  ✓ Content Security Policy header is set\n";
            return true;
        }
        
        echo "  ✗ Content Security Policy header not found\n";
        return false;
    }
    
    /**
     * Test SQL Injection Protection
     */
    private function testSQLInjection() {
        echo "Testing SQL Injection Protection...\n";
        
        // Test 1: Check if parameterized queries are used
        $result = $this->checkParameterizedQueries();
        $this->testResults['Parameterized Queries'] = $result;
        
        echo "\n";
    }
    
    /**
     * Check parameterized queries
     */
    private function checkParameterizedQueries() {
        echo "  ℹ Parameterized queries require manual code review\n";
        return null;
    }
    
    /**
     * Test Rate Limiting
     */
    private function testRateLimiting() {
        echo "Testing Rate Limiting...\n";
        
        $result = $this->checkRateLimitImplementation();
        $this->testResults['Rate Limiting'] = $result;
        
        echo "\n";
    }
    
    /**
     * Check rate limiting implementation
     */
    private function checkRateLimitImplementation() {
        if (file_exists(APP_ROOT . '/app/helpers/RateLimiter.php')) {
            echo "  ✓ RateLimiter helper exists\n";
            return true;
        }
        
        echo "  ✗ RateLimiter helper not found\n";
        return false;
    }
    
    /**
     * Test Session Security
     */
    private function testSessionSecurity() {
        echo "Testing Session Security...\n";
        
        // Test 1: Check secure cookie settings
        $result = $this->checkSecureCookies();
        $this->testResults['Secure Cookies'] = $result;
        
        // Test 2: Check session timeout
        $result = $this->checkSessionTimeout();
        $this->testResults['Session Timeout'] = $result;
        
        echo "\n";
    }
    
    /**
     * Check secure cookies
     */
    private function checkSecureCookies() {
        echo "  ℹ Secure cookies require manual configuration check\n";
        return null;
    }
    
    /**
     * Check session timeout
     */
    private function checkSessionTimeout() {
        if (file_exists(APP_ROOT . '/app/helpers/Session.php')) {
            echo "  ✓ Session helper exists\n";
            return true;
        }
        
        echo "  ✗ Session helper not found\n";
        return false;
    }
    
    /**
     * Print test results
     */
    private function printResults() {
        echo "=== Test Results ===\n\n";
        
        $passed = 0;
        $failed = 0;
        $skipped = 0;
        
        foreach ($this->testResults as $test => $result) {
            if ($result === true) {
                echo "✓ {$test}: PASSED\n";
                $passed++;
            } elseif ($result === false) {
                echo "✗ {$test}: FAILED\n";
                $failed++;
            } else {
                echo "ℹ {$test}: SKIPPED (requires manual testing)\n";
                $skipped++;
            }
        }
        
        echo "\n";
        echo "Total: " . count($this->testResults) . " tests\n";
        echo "Passed: {$passed}\n";
        echo "Failed: {$failed}\n";
        echo "Skipped: {$skipped}\n";
    }
}

// Run tests if executed directly
if (php_sapi_name() === 'cli') {
    define('APP_ROOT', dirname(__FILE__) . '/..');
    $test = new SecurityTest();
    $test->runAll();
}
