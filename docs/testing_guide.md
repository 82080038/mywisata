# TESTING GUIDE
# Tour Guide Application

## OVERVIEW

This guide provides comprehensive instructions for running, writing, and maintaining tests for the Tour Guide Application.

## TESTING FRAMEWORKS

### PHPUnit
- **Purpose:** Unit and integration testing
- **Version:** 10.5.64
- **Configuration:** phpunit.xml
- **Documentation:** https://phpunit.de/

### PHPStan
- **Purpose:** Static analysis
- **Version:** 1.12.33
- **Configuration:** phpstan.neon
- **Level:** 8
- **Documentation:** https://phpstan.org/

### PHP_CodeSniffer
- **Purpose:** Code style checking
- **Version:** 3.13.5
- **Configuration:** phpcs.xml
- **Standard:** PSR-12
- **Documentation:** https://github.com/squizlabs/PHP_CodeSniffer

### Playwright
- **Purpose:** End-to-end testing
- **Location:** tests/e2e/
- **Documentation:** https://playwright.dev/

## RUNNING TESTS

### Run All Tests
```bash
# Using the test script
./scripts/run_tests.sh

# Or run PHPUnit directly
vendor/bin/phpunit

# Run specific test suite
vendor/bin/phpunit --testsuite "Unit Tests"
vendor/bin/phpunit --testsuite "Integration Tests"
vendor/bin/phpunit --testsuite "Security Tests"
vendor/bin/phpunit --testsuite "Performance Tests"
```

### Run Specific Test File
```bash
vendor/bin/phpunit tests/Unit/Helpers/ValidatorTest.php
vendor/bin/phpunit tests/Unit/Helpers/SecurityTest.php
```

### Run Specific Test Method
```bash
vendor/bin/phpunit --filter testRequiredValidation tests/Unit/Helpers/ValidatorTest.php
```

### Run Static Analysis
```bash
# PHPStan
vendor/bin/phpstan analyse app --level=8

# PHP_CodeSniffer
vendor/bin/phpcs --standard=PSR12 app

# Auto-fix code style issues
vendor/bin/phpcbf --standard=PSR12 app
```

### Run E2E Tests
```bash
# Run all Playwright tests
npx playwright test

# Run specific test file
npx playwright test auth.spec.js

# Run in headed mode (show browser)
npx playwright test --headed

# Run in debug mode
npx playwright test --debug
```

## TEST STRUCTURE

```
tests/
├── bootstrap.php              # PHPUnit bootstrap
├── Unit/                      # Unit tests
│   ├── Models/               # Model tests
│   ├── Controllers/          # Controller tests
│   ├── Helpers/              # Helper tests
│   └── Services/             # Service tests
├── Integration/               # Integration tests
│   ├── APITest.php          # API endpoint tests
│   ├── DatabaseTest.php     # Database tests
│   └── AuthFlowTest.php     # Authentication flow tests
├── Security/                 # Security tests
│   └── SecurityTest.php     # Security vulnerability tests
├── Performance/              # Performance tests
│   └── PerformanceTest.php # Performance benchmarks
└── e2e/                     # E2E tests (Playwright)
    ├── auth/               # Authentication tests
    ├── admin/              # Admin panel tests
    ├── booking/            # Booking flow tests
    └── common/             # Common helpers
```

## WRITING TESTS

### Unit Test Template
```php
<?php
namespace Tests\Unit\Helpers;

use PHPUnit\Framework\TestCase;
use ClassName;

class ClassNameTest extends TestCase
{
    private $instance;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->instance = new ClassName();
    }
    
    public function testMethodName()
    {
        // Arrange
        $input = 'test input';
        
        // Act
        $result = $this->instance->method($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }
    
    protected function tearDown(): void
    {
        parent::tearDown();
        // Cleanup
    }
}
```

### Integration Test Template
```php
<?php
namespace Tests\Integration;

use PHPUnit\Framework\TestCase;

class APITest extends TestCase
{
    private $baseUrl = 'http://localhost/mywisata/api';
    
    public function testEndpoint()
    {
        $response = file_get_contents($this->baseUrl . '/endpoint');
        $data = json_decode($response, true);
        
        $this->assertIsArray($data);
        $this->assertArrayHasKey('key', $data);
    }
}
```

### Security Test Template
```php
<?php
namespace Tests\Security;

use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
{
    public function testVulnerability()
    {
        $maliciousInput = "'; DROP TABLE users; --";
        $sanitized = Security::sanitizeXSS($maliciousInput);
        
        $this->assertNotEquals($maliciousInput, $sanitized);
    }
}
```

### Performance Test Template
```php
<?php
namespace Tests\Performance;

use PHPUnit\Framework\TestCase;

class PerformanceTest extends TestCase
{
    public function testPerformance()
    {
        $startTime = microtime(true);
        
        // Execute operation
        $result = performOperation();
        
        $executionTime = (microtime(true) - $startTime) * 1000;
        
        $this->assertLessThan(500, $executionTime, 'Operation should complete in < 500ms');
    }
}
```

## TEST COVERAGE

### Generate Coverage Report
```bash
# Generate HTML coverage report
vendor/bin/phpunit --coverage-html coverage/html

# Generate Clover XML coverage report
vendor/bin/phpunit --coverage-clover coverage/clover.xml

# Generate both
vendor/bin/phpunit --coverage-html coverage/html --coverage-clover coverage/clover.xml
```

### Coverage Goals
- **Overall Coverage:** 80%+
- **Models Coverage:** 90%+
- **Controllers Coverage:** 75%+
- **Helpers Coverage:** 85%+
- **Critical Paths:** 100%

### View Coverage Report
Open `coverage/html/index.html` in a browser to view the coverage report.

## CI/CD

### GitHub Actions
Tests run automatically on:
- Push to main branch
- Push to develop branch
- Pull requests to main branch

### Workflow Steps
1. Checkout code
2. Setup PHP 8.1
3. Install Composer dependencies
4. Run PHPUnit tests
5. Run PHPStan analysis
6. Run PHP_CodeSniffer
7. Setup Node.js
8. Install Playwright
9. Run Playwright tests
10. Upload coverage to Codecov

### Manual Trigger
Go to GitHub Actions tab and select "Tests" workflow to run manually.

## BEST PRACTICES

### Writing Tests
1. **Test one thing per test** - Each test should verify a single behavior
2. **Use descriptive names** - Test names should describe what they test
3. **Arrange-Act-Assert** - Structure tests clearly
4. **Keep tests independent** - Tests should not depend on each other
5. **Use test data factories** - Create reusable test data
6. **Mock external dependencies** - Don't test external services
7. **Test edge cases** - Test boundary conditions and error cases

### Test Maintenance
1. **Keep tests updated** - Update tests when code changes
2. **Remove obsolete tests** - Delete tests for removed features
3. **Refactor tests** - Improve test code quality
4. **Review test coverage** - Regularly check coverage reports
5. **Fix failing tests quickly** - Don't let tests fail

### CI/CD
1. **Run tests locally first** - Don't push failing tests
2. **Keep build times short** - Optimize test execution
3. **Use caching** - Cache dependencies in CI
4. **Parallelize tests** - Run tests in parallel when possible

## TROUBLESHOOTING

### PHPUnit Errors
- **Class not found:** Check autoloader and namespace
- **Configuration error:** Check phpunit.xml syntax
- **Bootstrap error:** Check tests/bootstrap.php

### Test Failures
- **Flaky tests:** Tests that sometimes fail - investigate timing issues
- **Environment issues:** Check test database configuration
- **Dependency issues:** Run composer install

### Coverage Issues
- **No coverage driver:** Install Xdebug
- **Low coverage:** Add more tests for uncovered code
- **Exclude files:** Update phpunit.xml coverage exclude

## RESOURCES

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [Playwright Documentation](https://playwright.dev/docs/intro)
- [PSR-12 Coding Standard](https://www.php-fig.org/psr/psr-12/)

---

**Version:** 1.0  
**Last Updated:** 2026-07-18  
**Status:** Active
