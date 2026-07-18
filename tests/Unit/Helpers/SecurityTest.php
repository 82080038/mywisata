<?php
namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use Security;

class SecurityTest extends TestCase
{
    public function testPasswordComplexityValidation()
    {
        // Test weak password
        $result = Security::validatePasswordComplexity('simple');
        $this->assertFalse($result['valid']);
        
        // Test strong password
        $result = Security::validatePasswordComplexity('Complex123!');
        $this->assertTrue($result['valid']);
    }
    
    public function testPasswordComplexityRequirements()
    {
        // Test missing uppercase
        $result = Security::validatePasswordComplexity('complex123!');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('uppercase', $result['message']);
        
        // Test missing lowercase
        $result = Security::validatePasswordComplexity('COMPLEX123!');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('lowercase', $result['message']);
        
        // Test missing number
        $result = Security::validatePasswordComplexity('ComplexPass!');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('number', $result['message']);
        
        // Test missing special character
        $result = Security::validatePasswordComplexity('Complex123');
        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('special', $result['message']);
    }
    
    public function testAccountLockout()
    {
        $identifier = 'test@example.com';
        
        // Initial state - not locked
        $result = Security::checkAccountLockout($identifier);
        $this->assertFalse($result['locked']);
        $this->assertEquals(5, $result['remaining_attempts']);
        
        // Record failed attempts
        for ($i = 0; $i < 5; $i++) {
            Security::recordFailedAttempt($identifier);
        }
        
        // Should be locked now
        $result = Security::checkAccountLockout($identifier);
        $this->assertTrue($result['locked']);
        
        // Clear attempts
        Security::clearFailedAttempts($identifier);
        
        // Should be unlocked
        $result = Security::checkAccountLockout($identifier);
        $this->assertFalse($result['locked']);
    }
    
    public function testRateLimiting()
    {
        $identifier = 'test_ip_' . time();
        
        // First request should be allowed
        $result = Security::checkRateLimit($identifier, 5, 60);
        $this->assertTrue($result['allowed']);
        $this->assertArrayHasKey('remaining', $result);
        $this->assertArrayHasKey('reset_time', $result);
    }
    
    public function testEncryptionDecryption()
    {
        $originalData = 'Sensitive information';
        
        // Encrypt
        $encrypted = Security::encrypt($originalData);
        $this->assertNotEquals($originalData, $encrypted);
        
        // Decrypt
        $decrypted = Security::decrypt($encrypted);
        $this->assertEquals($originalData, $decrypted);
    }
    
    public function testXSSSanitization()
    {
        $maliciousInput = '<script>alert("XSS")</script>';
        $sanitized = Security::sanitizeXSS($maliciousInput);
        
        $this->assertNotEquals($maliciousInput, $sanitized);
        $this->assertStringNotContainsString('<script>', $sanitized);
    }
    
    public function testTokenGeneration()
    {
        $token1 = Security::generateToken(32);
        $token2 = Security::generateToken(32);
        
        $this->assertNotEquals($token1, $token2);
        $this->assertEquals(32, strlen($token1)); // 32 hex chars
    }
    
    public function testTokenGeneration16Bytes()
    {
        $token = Security::generateToken(16);
        $this->assertEquals(16, strlen($token)); // 16 hex chars
    }
}
