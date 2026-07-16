# MyWisata Playwright Tests

This directory contains end-to-end tests for the MyWisata application using Playwright.

## Setup

1. Install Playwright:
```bash
npm install -D @playwright/test
```

2. Install browsers:
```bash
npx playwright install
```

## Running Tests

Run all tests:
```bash
npx playwright test
```

Run tests in headed mode:
```bash
npx playwright test --headed
```

Run specific test file:
```bash
npx playwright test auth.spec.js
```

Run tests in specific browser:
```bash
npx playwright test --project=chromium
```

View test report:
```bash
npx playwright show-report
```

## Test Structure

- `auth.spec.js` - Authentication tests (login, register, forgot password)
- `booking.spec.js` - Booking tests (tour guide booking, booking management)
- `destination.spec.js` - Destination tests (browsing, filtering, reviews)

## Adding New Tests

1. Create a new `.spec.js` file in the `tests/` directory
2. Use the test.describe and test functions from Playwright
3. Follow the existing test patterns

## Test Data

Tests use the following default test data:
- Email: wisatawan@example.com
- Password: password123

Make sure these exist in your test database before running tests.

## CI/CD Integration

To run tests in CI/CD:
```bash
npx playwright test --reporter=json
```
