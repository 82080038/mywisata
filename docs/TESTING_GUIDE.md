# TESTING GUIDE
# MyWisata Application
# Version: 1.0.0
# Last Updated: 2026-07-18

## TABLE OF CONTENTS

1. [Overview](#overview)
2. [Testing Strategy](#testing-strategy)
3. [Playwright E2E Testing](#playwright-e2e-testing)
4. [Manual Testing](#manual-testing)
5. [Test Cases](#test-cases)
6. [Load Testing](#load-testing)
7. [Security Testing](#security-testing)
8. [Performance Testing](#performance-testing)
9. [Test Reporting](#test-reporting)
10. [Troubleshooting](#troubleshooting)

---

## OVERVIEW

This guide covers all testing aspects of the MyWisata Application, including automated E2E testing with Playwright, manual testing procedures, load testing, security testing, and performance testing.

### Current Test Status
- **Total Playwright Tests**: 100
- **Passed**: 57 (57%)
- **Failed**: 43 (43%)
- **Duration**: ~4.5 minutes
- **Core Features**: All passing
- **Advanced Features**: Some failing due to missing configuration

---

## TESTING STRATEGY

### Testing Pyramid

```
        /\
       /  \      E2E Tests (Playwright)
      /____\
     /      \    Integration Tests
    /________\
   /          \  Unit Tests
  /____________\
```

### Types of Testing

| Type | Scope | Tools | Status |
|------|-------|-------|--------|
| **E2E Testing** | Complete user flows | Playwright | ✅ Implemented |
| **Unit Testing** | Individual functions | PHPUnit (optional) | ⚠️ Manual only |
| **Integration Testing** | Multi-module flows | Manual/Scripts | ⚠️ Manual only |
| **API Testing** | Endpoint testing | Postman/curl | ⚠️ Manual only |
| **Security Testing** | Vulnerabilities | OWASP checklist | ⚠️ Manual only |
| **Performance Testing** | Load & stress | Apache Bench/JMeter | ⚠️ Manual only |
| **Load Testing** | High traffic simulation | Apache Bench/JMeter | ⚠️ Manual only |

---

## PLAYWRIGHT E2E TESTING

### Installation

```bash
# Install dependencies
npm install

# Install Playwright browsers
npx playwright install chromium
```

### Running Tests

```bash
# Run all tests
npx playwright test

# Run specific test file
npx playwright test tests/e2e/homepage.spec.ts

# Run with browser visible (headed mode)
npx playwright test --headed

# Run with HTML report
npx playwright test --reporter=html

# View HTML report
npx playwright show-report

# Run specific browser
npx playwright test --project=chromium
```

### Test Configuration

Configuration in `playwright.config.ts`:

```typescript
import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests/e2e',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 2 : 0,
  workers: process.env.CI ? 1 : 2,
  reporter: 'html',
  use: {
    baseURL: 'http://localhost:8080',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
  },
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
});
```

### Test Structure

```typescript
import { test, expect } from '@playwright/test';

test.describe('Feature Name', () => {
  test('should do something', async ({ page }) => {
    await page.goto('http://localhost:8080');
    // Test logic
    expect(something).toBe(expected);
  });
});
```

### Test Categories

#### 1. Homepage Tests (5/5 passed)
- should load homepage successfully
- should display navigation menu
- should display hero section
- should have working links
- should be responsive

#### 2. Authentication Tests (5/5 passed)
- should display login page
- should display register page
- should have CSRF token in login form
- should redirect to login when accessing protected route
- should have forgot password link

#### 3. Destinations Tests (5/5 passed)
- should display destinations page
- should display destination cards
- should have search functionality
- should have filter options
- should navigate to destination detail

#### 4. Hotels Tests (9/9 passed)
- should display hotels page
- should display hotel cards
- should have search functionality
- should have filter options
- should navigate to hotel detail
- should display prayer room badges
- should display alcohol free badges
- should display women only facilities badges
- should display qibla direction badges

#### 5. Restaurants Tests (9/9 passed)
- should display restaurants page
- should display restaurant cards
- should have search functionality
- should have filter options
- should navigate to restaurant detail
- should display dietary filter checkboxes
- should display halal badges
- should filter restaurants by halal status

#### 6. Events Tests (5/5 passed)
- should display events page
- should display event cards
- should have search functionality
- should have filter options
- should navigate to event detail

#### 7. Booking Tests (4/4 passed)
- should display booking page
- should redirect to login when accessing booking without auth
- should have booking form when authenticated
- should have date picker in booking form

#### 8. Payment Tests (4/4 passed)
- should display payment page
- should redirect to login when accessing payment without auth
- should have payment form
- should have payment method selection

#### 9. Map Tests (4/4 passed)
- should display map page
- should have map container
- should have location button
- should have nearby destinations section

#### 10. Favorites Tests (4/4 passed)
- should display favorites page
- should have favorites route accessible
- should have favorite functionality on destination detail
- should display empty state when no favorites

#### 11. Role-Based Access Tests (8/8 passed)
- guest should access homepage
- guest should access destinations page
- guest should be redirected from admin dashboard
- guest should be redirected from user dashboard
- guest can access about page
- guest can access contact page
- guest can access registration page
- guest can access login page

#### 12. Tour Guides Tests (2/2 passed)
- should display tour guides page for guests
- tour guide dashboard requires authentication

#### 13. API Tests (6/6 passed)
- should get destinations API
- should get tour guides API
- should get hotels API
- should get restaurants API
- should get events API
- should search API

#### 14. Admin Tests (2/2 passed)
- should redirect to login when accessing admin without auth
- should redirect to login when accessing admin users without auth

#### 15. Address API Tests (10/10 passed)
- should load provinces API endpoint
- should load regencies by province ID
- should load districts by regency ID
- should load villages by district ID
- should handle missing parameters gracefully
- should return correct province data structure
- should return correct regency data structure
- should return correct district data structure
- should return correct village data structure
- should include count in response

#### 16. Address UI Tests (0/33 failed)
- JavaScript frontend for dropdown interaction not implemented

#### 17. AI Tour Guide Tests (0/1 failed)
- Requires OpenAI API key configuration

---

## MANUAL TESTING

### Authentication Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| AUTH-001 | Valid login | Enter valid email and password | Login successful, redirect to dashboard |
| AUTH-002 | Invalid email | Enter invalid email | Error message "Invalid credentials" |
| AUTH-003 | Invalid password | Enter valid email, invalid password | Error message "Invalid credentials" |
| AUTH-004 | Empty fields | Submit empty form | Validation error for both fields |
| AUTH-005 | Register user | Fill valid registration form | Account created, redirect to login |
| AUTH-006 | Duplicate email | Register with existing email | Error: email already registered |
| AUTH-007 | Logout | Click logout button | Session destroyed, redirect to login |
| AUTH-008 | Protected route | Access protected page without login | Redirect to login page |
| AUTH-009 | Admin access | Access admin as regular user | 403 Forbidden |
| AUTH-010 | Session timeout | Idle for 30 minutes | Auto logout |

### Booking Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| BOOK-001 | Valid booking | Select guide, date, participants | Booking created, payment pending |
| BOOK-002 | Invalid date | Select past date | Error: date must be in future |
| BOOK-003 | Unavailable guide | Book unavailable guide | Error: guide not available |
| BOOK-004 | Duplicate booking | Book same date twice | Error: date already booked |
| BOOK-005 | Cancel booking | Cancel pending booking | Booking cancelled, status updated |
| BOOK-006 | View booking | View booking details | Show all booking information |

### Payment Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| PAY-001 | Valid payment | Complete payment flow | Payment successful, booking confirmed |
| PAY-002 | Invalid card | Use invalid card details | Payment failed, error message |
| PAY-003 | Payment timeout | Payment takes too long | Payment cancelled, booking pending |
| PAY-004 | Refund request | Request refund for completed booking | Refund processed if eligible |
| PAY-005 | Payment callback | Simulate Midtrans callback | Transaction status updated |

### Destination Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| DEST-001 | View destinations | Navigate to destinations page | Show all destinations |
| DEST-002 | Search destination | Enter search query | Show matching destinations |
| DEST-003 | Filter by category | Select category filter | Show filtered destinations |
| DEST-004 | View destination detail | Click destination card | Show destination details |
| DEST-005 | Add to favorites | Click favorite button | Destination added to favorites |
| DEST-006 | Remove from favorites | Click unfavorite button | Destination removed from favorites |

---

## LOAD TESTING

### Apache Bench

```bash
# Install Apache Bench
sudo apt install apache2-utils -y

# Test homepage
ab -n 1000 -c 10 http://localhost:8080/

# Test API endpoint
ab -n 1000 -c 10 http://localhost:8080/?url=api/destinations

# Test with POST data
ab -n 1000 -c 10 -p post_data.txt -T application/x-www-form-urlencoded http://localhost:8080/?url=auth/login
```

### JMeter

1. Download and install JMeter
2. Create test plan
3. Add HTTP Request samplers
4. Configure thread groups
5. Run test
6. Analyze results

### Load Test Scenarios

| Scenario | Concurrent Users | Duration | Target |
|----------|------------------|----------|--------|
| Light Load | 10 users | 5 minutes | < 1s response time |
| Medium Load | 50 users | 10 minutes | < 2s response time |
| Heavy Load | 100 users | 15 minutes | < 3s response time |
| Stress Test | 200 users | 5 minutes | System stability |

---

## SECURITY TESTING

### OWASP Top 10 Checklist

- [ ] **Injection**: SQL injection prevention (PDO prepared statements)
- [ ] **Broken Authentication**: Secure session management
- [ ] **Sensitive Data Exposure**: HTTPS, encrypted passwords
- [ ] **XML External Entities (XXE)**: Not applicable (no XML)
- [ ] **Broken Access Control**: RBAC implementation
- [ ] **Security Misconfiguration**: Secure defaults, no debug in production
- [ ] **Cross-Site Scripting (XSS)**: Output escaping
- [ ] **Insecure Deserialization**: Not applicable
- [ ] **Using Components with Known Vulnerabilities**: Keep dependencies updated
- [ ] **Insufficient Logging & Monitoring**: Audit logging

### Security Test Cases

| ID | Test Case | Steps | Expected Result |
|----|-----------|-------|-----------------|
| SEC-001 | SQL Injection | Inject SQL in form fields | Query sanitized, no error |
| SEC-002 | XSS Attack | Inject script in form fields | Script escaped, not executed |
| SEC-003 | CSRF Protection | Submit form without token | Request rejected |
| SEC-004 | Session Hijacking | Attempt session hijacking | Secure session management |
| SEC-005 | Brute Force | Multiple login attempts | Rate limiting active |
| SEC-006 | Directory Traversal | Try to access /app/ | Access denied |
| SEC-007 | File Upload | Upload malicious file | File type validation |
| SEC-008 | Privilege Escalation | Access admin as user | Access denied |

---

## PERFORMANCE TESTING

### Performance Metrics

| Metric | Target | Measurement Tool |
|--------|--------|------------------|
| Page Load Time | < 2s | Lighthouse, WebPageTest |
| Time to First Byte (TTFB) | < 200ms | curl, WebPageTest |
| First Contentful Paint (FCP) | < 1s | Lighthouse |
| Largest Contentful Paint (LCP) | < 2.5s | Lighthouse |
| Cumulative Layout Shift (CLS) | < 0.1 | Lighthouse |
| First Input Delay (FID) | < 100ms | Lighthouse |

### Performance Optimization Checklist

- [ ] Enable OPcache
- [ ] Enable Gzip compression
- [ ] Configure browser caching
- [ ] Optimize images
- [ ] Minify CSS/JS
- [ ] Use CDN for static assets
- [ ] Enable Redis caching
- [ ] Optimize database queries
- [ ] Use database indexes
- [ ] Enable HTTP/2

---

## TEST REPORTING

### Playwright HTML Report

```bash
# Generate HTML report
npx playwright test --reporter=html

# View report
npx playwright show-report
```

### Test Report Template

```markdown
# Test Report - [Date]

## Summary
- Total Tests: X
- Passed: Y
- Failed: Z
- Success Rate: N%

## Test Results
### Passed Tests
- Test 1
- Test 2

### Failed Tests
- Test 1 - Reason
- Test 2 - Reason

## Issues Found
1. Issue description
2. Issue description

## Recommendations
1. Recommendation 1
2. Recommendation 2
```

---

## TROUBLESHOOTING

### Playwright Tests Failing

**Problem**: Tests fail to run

**Solutions**:
1. Ensure PHP server is running:
   ```bash
   php -S localhost:8080
   ```

2. Reinstall Playwright browsers:
   ```bash
   npx playwright install chromium
   ```

3. Check test URLs are correct

4. Run tests in headed mode:
   ```bash
   npx playwright test --headed
   ```

### Database Connection Failed

**Problem**: Cannot connect to database

**Solutions**:
1. Check MySQL is running:
   ```bash
   sudo systemctl status mysql
   ```

2. Verify database exists:
   ```bash
   mysql -u root -p -e "SHOW DATABASES;"
   ```

3. Check credentials in config

### Timeouts in Tests

**Problem**: Tests timeout

**Solutions**:
1. Increase timeout in playwright.config.ts:
   ```typescript
   use: {
     actionTimeout: 30000,
     navigationTimeout: 30000,
   }
   ```

2. Check server performance

3. Optimize database queries

---

## BEST PRACTICES

### Test Writing

1. **Keep tests independent** - Each test should run independently
2. **Use descriptive names** - Test names should describe what they test
3. **Use page object model** - Reusable page objects for common actions
4. **Wait for elements** - Use proper wait strategies
5. **Clean up test data** - Clean up after tests

### Test Maintenance

1. **Update tests regularly** - Keep tests in sync with application changes
2. **Review test failures** - Investigate and fix failing tests
3. **Refactor tests** - Improve test quality over time
4. **Add new tests** - Cover new features with tests
5. **Remove obsolete tests** - Delete tests for removed features

---

## ADDITIONAL RESOURCES

### Documentation
- [Playwright Documentation](https://playwright.dev/)
- [OWASP Testing Guide](https://owasp.org/www-project-web-security-testing-guide/)
- [Performance Testing Guide](https://web.dev/performance/)

### Tools
- [Playwright](https://playwright.dev/)
- [Apache Bench](https://httpd.apache.org/docs/2.4/programs/ab.html)
- [JMeter](https://jmeter.apache.org/)
- [Lighthouse](https://developers.google.com/web/tools/lighthouse)
- [OWASP ZAP](https://www.zaproxy.org/)

---

**Version**: 1.0.0  
**Last Updated**: 2026-07-18  
**Test Framework**: Playwright 1.61.1
