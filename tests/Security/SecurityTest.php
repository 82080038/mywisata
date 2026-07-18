<?php
namespace Tests\Security;

use PHPUnit\Framework\TestCase;
use Security;

class SecurityTest extends TestCase
{
    public function testSQLInjectionPrevention()
    {
        $maliciousInputs = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users--",
            "1' AND 1=1--",
            "admin'--"
        ];
        
        foreach ($maliciousInputs as $input) {
            // Test that malicious input is sanitized
            $sanitized = Security::sanitizeXSS($input);
            $this->assertNotEquals($input, $sanitized);
        }
    }
    
    public function testXSSPrevention()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")',
            '<svg onload=alert("XSS")>',
            '<body onload=alert("XSS")>'
        ];
        
        foreach ($xssPayloads as $payload) {
            $sanitized = Security::sanitizeXSS($payload);
            $this->assertStringNotContainsString('<script', $sanitized);
            $this->assertStringNotContainsString('javascript:', $sanitized);
            $this->assertStringNotContainsString('onerror=', $sanitized);
            $this->assertStringNotContainsString('onload=', $sanitized);
        }
    }
    
    public function testCSRFTokenGeneration()
    {
        $token1 = Security::generateToken(32);
        $token2 = Security::generateToken(32);
        
        // Tokens should be different
        $this->assertNotEquals($token1, $token2);
        
        // Tokens should be hex string
        $this->assertMatchesRegularExpression('/^[a-f0-9]+$/', $token1);
    }
    
    public function testCSRFTokenLength()
    {
        $token = Security::generateToken(32);
        $this->assertEquals(64, strlen($token)); // 32 bytes = 64 hex chars
    }
    
    public function testPathTraversalPrevention()
    {
        $maliciousPaths = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32',
            '/etc/passwd',
            'C:\\Windows\\System32'
        ];
        
        foreach ($maliciousPaths as $path) {
            $sanitized = Security::sanitizeXSS($path);
            // Should be sanitized or rejected
            $this->assertNotEquals($path, $sanitized);
        }
    }
    
    public function testCommandInjectionPrevention()
    {
        $maliciousCommands = [
            '; rm -rf /',
            '| cat /etc/passwd',
            '&& whoami',
            '`whoami`',
            '$(whoami)'
        ];
        
        foreach ($maliciousCommands as $command) {
            $sanitized = Security::sanitizeXSS($command);
            $this->assertStringNotContainsString(';', $sanitized);
            $this->assertStringNotContainsString('|', $sanitized);
            $this->assertStringNotContainsString('&&', $sanitized);
            $this->assertStringNotContainsString('`', $sanitized);
            $this->assertStringNotContainsString('$(', $sanitized);
        }
    }
}
