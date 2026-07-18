# AUTOMATION TESTING GUIDE
# Module 33 - Comprehensive Automation Testing for Tour Guide Application

## OVERVIEW

This prompting template guides the AI through implementing comprehensive automation testing for the Tour Guide Application using modern testing frameworks and best practices.

## TESTING FRAMEWORKS

### Recommended Tools
- **Playwright** - End-to-end testing (already configured)
- **PHPUnit** - Unit testing for PHP
- **PHPStan** - Static analysis
- **PHP_CodeSniffer** - Code style checking
- **Psalm** - Additional static analysis (optional)

## TESTING STRATEGY

### Test Pyramid
```
        /\
       /E2E\      - 10% (Playwright)
      /------\
     /Integration\ - 20% (API tests)
    /------------\
   /   Unit Tests  \ - 70% (PHPUnit)
  /________________\
```

### Test Categories
1. **Unit Tests** - Test individual functions and methods
2. **Integration Tests** - Test module interactions
3. **API Tests** - Test REST API endpoints
4. **E2E Tests** - Test complete user flows
5. **Security Tests** - Test security vulnerabilities
6. **Performance Tests** - Test performance under load

## UNIT TESTING (PHPUnit)

### Setup Requirements
```json
{
  "require-dev": {
    "phpunit/phpunit": "^10.0",
    "phpstan/phpstan": "^1.10",
    "squizlabs/php_codesniffer": "^3.7"
  }
}
```

### Test Structure
```
tests/
├── Unit/
│   ├── Models/
│   │   ├── UserTest.php
│   │   ├── TourGuideTest.php
│   │   ├── DestinationTest.php
│   │   └── BookingTest.php
│   ├── Controllers/
│   │   ├── AuthControllerTest.php
│   │   ├── AdminControllerTest.php
│   │   └── BookingControllerTest.php
│   ├── Helpers/
│   │   ├── ValidatorTest.php
│   │   ├── SessionTest.php
│   │   └── CacheTest.php
│   └── Services/
│       └── PaymentServiceTest.php
├── Integration/
│   ├── DatabaseTest.php
│   ├── APITest.php
│   └── AuthFlowTest.php
└── bootstrap.php
```

### Unit Test Template
```php
<?php
namespace Tests\Unit\Models;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    private User $user;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }
    
    public function testUserCreation()
    {
        $this->user->name = 'Test User';
        $this->user->email = 'test@example.com';
        $this->user->password = password_hash('password123', PASSWORD_BCRYPT);
        
        $this->assertEquals('Test User', $this->user->name);
        $this->assertEquals('test@example.com', $this->user->email);
    }
    
    public function testPasswordValidation()
    {
        $this->assertTrue($this->user->validatePassword('password123'));
        $this->assertFalse($this->user->validatePassword('wrongpassword'));
    }
}
```

## INTEGRATION TESTING

### API Integration Tests
```php
<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    private string $baseUrl = 'http://localhost/mywisata/api';
    
    public function testDestinationsEndpoint()
    {
        $response = file_get_contents($this->baseUrl . '/destinations');
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('destinations', $data);
    }
    
    public function testBookingEndpoint()
    {
        $postData = [
            'tour_guide_id' => 1,
            'date' => '2026-07-20',
            'duration' => 4
        ];
        
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($postData)
            ]
        ]);
        
        $response = file_get_contents($this->baseUrl . '/bookings', false, $context);
        $data = json_decode($response, true);
        
        $this->assertEquals('success', $data['status']);
    }
}
```

## E2E TESTING (Playwright)

### Test Structure
```
tests/e2e/
├── auth/
│   ├── login.spec.ts
│   ├── register.spec.ts
│   └── logout.spec.ts
├── admin/
│   ├── dashboard.spec.ts
│   ├── users.spec.ts
│   └── settings.spec.ts
├── tourguide/
│   ├── profile.spec.ts
│   ├── bookings.spec.ts
│   └── earnings.spec.ts
├── booking/
│   ├── create.spec.ts
│   ├── payment.spec.ts
│   └── cancellation.spec.ts
└── common/
    └── helpers.ts
```

### E2E Test Template
```typescript
import { test, expect } from '@playwright/test';

test.describe('User Authentication', () => {
  test('successful login', async ({ page }) => {
    await page.goto('http://localhost/mywisata/auth/login');
    
    await page.fill('input[name="email"]', 'admin@mywisata.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    
    await expect(page).toHaveURL(/.*admin\/dashboard/);
    await expect(page.locator('h1')).toContainText('Dashboard');
  });
  
  test('failed login with invalid credentials', async ({ page }) => {
    await page.goto('http://localhost/mywisata/auth/login');
    
    await page.fill('input[name="email"]', 'admin@mywisata.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    await expect(page.locator('.alert-danger')).toBeVisible();
    await expect(page.locator('.alert-danger')).toContainText('Invalid credentials');
  });
});
```

### Booking Flow E2E Test
```typescript
import { test, expect } from '@playwright/test';

test.describe('Booking Flow', () => {
  test.beforeEach(async ({ page }) => {
    // Login as tourist
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'tourist@mywisata.com');
    await page.fill('input[name="password"]', 'tourist123');
    await page.click('button[type="submit"]');
  });
  
  test('complete booking flow', async ({ page }) => {
    // Navigate to tour guide listing
    await page.goto('http://localhost/mywisata/tourguides');
    
    // Select a tour guide
    await page.click('.tour-guide-card:first-child');
    
    // Click book button
    await page.click('button[data-action="book"]');
    
    // Fill booking form
    await page.fill('input[name="date"]', '2026-07-20');
    await page.fill('input[name="duration"]', '4');
    await page.fill('textarea[name="notes"]', 'Test booking notes');
    
    // Submit booking
    await page.click('button[type="submit"]');
    
    // Verify booking created
    await expect(page.locator('.alert-success')).toBeVisible();
    await expect(page.locator('.alert-success')).toContainText('Booking created successfully');
  });
});
```

## SECURITY TESTING

### Security Test Suite
```php
<?php
namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    public function testSQLInjectionPrevention()
    {
        // Test SQL injection attempts
        $maliciousInputs = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users--"
        ];
        
        foreach ($maliciousInputs as $input) {
            $result = $this->testInput($input);
            $this->assertNotContains('error', $result);
        }
    }
    
    public function testXSSPrevention()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror=alert("XSS")>',
            'javascript:alert("XSS")'
        ];
        
        foreach ($xssPayloads as $payload) {
            $sanitized = $this->sanitizeInput($payload);
            $this->assertNotContains('<script', $sanitized);
            $this->assertNotContains('javascript:', $sanitized);
        }
    }
    
    public function testCSRFProtection()
    {
        // Test CSRF token validation
        $response = $this->submitFormWithoutToken();
        $this->assertEquals('CSRF token mismatch', $response['error']);
    }
}
```

## PERFORMANCE TESTING

### Performance Test Scenarios
```php
<?php
namespace Tests\Performance;

use PHPUnit\Framework\TestCase;

class PerformanceTest extends TestCase
{
    public function testPageLoadTime()
    {
        $startTime = microtime(true);
        
        $response = $this->loadPage('/destinations');
        
        $loadTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(500, $loadTime, 'Page load time should be less than 500ms');
    }
    
    public function testDatabaseQueryPerformance()
    {
        $startTime = microtime(true);
        
        $results = $this->executeQuery('SELECT * FROM destinations LIMIT 100');
        
        $queryTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(100, $queryTime, 'Query time should be less than 100ms');
    }
    
    public function testAPIResponseTime()
    {
        $startTime = microtime(true);
        
        $response = $this->callAPI('/api/destinations');
        
        $responseTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(200, $responseTime, 'API response time should be less than 200ms');
    }
}
```

## TEST COVERAGE

### Coverage Goals
- **Overall Coverage**: 80%+
- **Models Coverage**: 90%+
- **Controllers Coverage**: 75%+
- **Helpers Coverage**: 85%+
- **Critical Paths**: 100%

### Coverage Report Generation
```bash
# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

## CONTINUOUS INTEGRATION

### CI/CD Pipeline
```yaml
# .github/workflows/tests.yml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        extensions: mbstring, mysql
        coverage: xdebug
    
    - name: Install dependencies
      run: composer install --dev
    
    - name: Run PHPUnit
      run: vendor/bin/phpunit --coverage-clover coverage.xml
    
    - name: Run PHPStan
      run: vendor/bin/phpstan analyse app --level=8
    
    - name: Run CodeSniffer
      run: vendor/bin/phpcs --standard=PSR12 app
    
    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
    
    - name: Install Playwright
      run: npm ci && npx playwright install --with-deps
    
    - name: Run Playwright tests
      run: npx playwright test
    
    - name: Upload coverage
      uses: codecov/codecov-action@v3
```

## IMPLEMENTATION TASKS

### Phase 1: Setup
1. Install PHPUnit
2. Configure phpunit.xml
3. Install PHPStan
4. Configure PHPStan
5. Install PHP_CodeSniffer
6. Configure coding standards

### Phase 2: Unit Tests
1. Create test structure
2. Write model tests
3. Write controller tests
4. Write helper tests
5. Write service tests

### Phase 3: Integration Tests
1. Create API tests
2. Create database tests
3. Create auth flow tests
4. Create booking flow tests

### Phase 4: E2E Tests
1. Create auth E2E tests
2. Create admin E2E tests
3. Create booking E2E tests
4. Create payment E2E tests

### Phase 5: Security Tests
1. Create SQL injection tests
2. Create XSS tests
3. Create CSRF tests
4. Create authentication tests

### Phase 6: Performance Tests
1. Create load time tests
2. Create query performance tests
3. Create API response tests

### Phase 7: CI/CD
1. Create GitHub Actions workflow
2. Configure test reporting
3. Configure coverage reporting
4. Configure notifications

## DELIVERABLES

1. PHPUnit configuration (phpunit.xml)
2. Unit test suite for all models
3. Unit test suite for all controllers
4. Integration test suite
5. E2E test suite (Playwright)
6. Security test suite
7. Performance test suite
8. CI/CD pipeline configuration
9. Test coverage report
10. Testing documentation

## ACCEPTANCE CRITERIA

- Unit tests for all critical functions
- Integration tests for all API endpoints
- E2E tests for all major user flows
- Security tests for common vulnerabilities
- Performance tests meeting targets
- Test coverage ≥ 80%
- All tests passing
- CI/CD pipeline configured
- Automated test execution on commits

## NOTES

- Write tests before fixing bugs (TDD approach)
- Keep tests independent and isolated
- Use test data factories
- Mock external dependencies
- Run tests frequently during development
- Maintain test documentation
- Review and update tests regularly

---

**Module:** 33_AUTOMATION_TESTING_GUIDE  
**Priority:** MEDIUM  
**Status:** READY FOR DEVELOPMENT  
**Last Updated:** 2026-07-18
