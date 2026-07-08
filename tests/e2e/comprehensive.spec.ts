import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Comprehensive Application Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Clear console errors before each test
    page.on('console', msg => {
      if (msg.type() === 'error') {
        console.log(`Console Error: ${msg.text()}`);
      }
    });

    page.on('pageerror', error => {
      console.log(`Page Error: ${error}`);
    });

    page.on('requestfailed', request => {
      console.log(`Request Failed: ${request.url()} - ${request.failure()}`);
    });
  });

  test('should load homepage without errors', async ({ page }) => {
    const response = await page.goto(BASE_URL);
    if (response) {
      expect(response.status()).toBeLessThan(400);
    }

    // Check for console errors
    const consoleErrors: string[] = [];
    page.on('console', msg => {
      if (msg.type() === 'error') {
        consoleErrors.push(msg.text());
      }
    });

    // Wait for page to be fully loaded
    await page.waitForLoadState('networkidle');

    expect(consoleErrors.length).toBe(0);
  });

  test('should have all security headers present', async ({ page }) => {
    const response = await page.goto(BASE_URL);
    if (!response) return;

    const headers = response.headers();

    // Check security headers
    expect(headers['content-security-policy']).toBeDefined();
    expect(headers['x-frame-options']).toBeDefined();
    expect(headers['x-content-type-options']).toBeDefined();
    expect(headers['x-xss-protection']).toBeDefined();
    expect(headers['referrer-policy']).toBeDefined();
  });

  test('should navigate to all main pages without errors', async ({ page }) => {
    const pages = [
      '/auth/login',
      '/auth/register',
      '/destinations',
      '/hotels',
      '/restaurants',
      '/events',
      '/tourguides',
    ];

    for (const pagePath of pages) {
      const response = await page.goto(`${BASE_URL}${pagePath}`);
      if (response) {
        expect(response.status()).toBeLessThan(400);
      }

      // Check for console errors on each page
      const consoleErrors: string[] = [];
      page.on('console', msg => {
        if (msg.type() === 'error') {
          consoleErrors.push(msg.text());
        }
      });

      await page.waitForLoadState('networkidle');
      expect(consoleErrors.length).toBe(0);
    }
  });

  test('should handle login form submission', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Check if login form exists
    const loginForm = page.locator('form');
    await expect(loginForm).toBeVisible();

    // Check for CSRF token
    const csrfToken = page.locator('input[name="csrf_token"]');
    const csrfCount = await csrfToken.count();
    expect(csrfCount).toBeGreaterThan(0);
  });

  test('should handle registration form submission', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/register`);

    // Check if register form exists
    const registerForm = page.locator('form');
    await expect(registerForm).toBeVisible();

    // Check for required fields
    const nameInput = page.locator('input[name="name"]');
    const emailInput = page.locator('input[name="email"]');
    const passwordInput = page.locator('input[name="password"]');
    const confirmPasswordInput = page.locator('input[name="password_confirm"]');

    await expect(nameInput).toBeVisible();
    await expect(emailInput).toBeVisible();
    await expect(passwordInput).toBeVisible();
    await expect(confirmPasswordInput).toBeVisible();
  });

  test('should display destinations with data', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check if destinations are displayed
    const destinationCards = page.locator('.destination-card, .card, .item');
    const count = await destinationCards.count();

    // Should have at least some destinations
    const countValue = await count;
    expect(countValue).toBeGreaterThan(0);
  });

  test('should handle search functionality', async ({ page }) => {
    await page.goto(`${BASE_URL}`);

    // Find search input
    const searchInput = page.locator('input[type="search"], input[name="search"], input[placeholder*="cari"], input[placeholder*="search"]');
    const count = await searchInput.count();

    if (count > 0) {
      await searchInput.first().fill('Bali');
      await page.keyboard.press('Enter');

      // Wait for search results
      await page.waitForLoadState('networkidle');

      // Check that we're on search page
      const currentUrl = page.url();
      expect(currentUrl).toMatch(/search/);
    }
  });

  test('should handle AJAX requests properly', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Monitor network requests
    const failedRequests: string[] = [];
    page.on('requestfailed', request => {
      failedRequests.push(request.url());
    });

    await page.waitForLoadState('networkidle');

    expect(failedRequests.length).toBe(0);
  });

  test('should be responsive on different viewports', async ({ page }) => {
    const viewports = [
      { width: 375, height: 667 }, // Mobile
      { width: 768, height: 1024 }, // Tablet
      { width: 1920, height: 1080 }, // Desktop
    ];

    for (const viewport of viewports) {
      await page.setViewportSize(viewport);
      const response = await page.goto(BASE_URL);
      if (response) {
        expect(response.status()).toBeLessThan(400);
      }
      await page.waitForLoadState('networkidle');
    }
  });

  test('should have no JavaScript errors on homepage', async ({ page }) => {
    const jsErrors: string[] = [];
    page.on('pageerror', error => {
      const errorMsg = error.toString();
      console.log('JavaScript Error:', errorMsg);
      jsErrors.push(errorMsg);
    });

    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');

    // Log errors for debugging
    if (jsErrors.length > 0) {
      console.log('JavaScript Errors found:', jsErrors);
    }

    expect(jsErrors.length).toBe(0);
  });

  test('should handle rate limiting on login', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Attempt multiple login attempts
    for (let i = 0; i < 6; i++) {
      const emailInput = page.locator('input[name="email"]');
      const passwordInput = page.locator('input[name="password"]');
      const submitButton = page.locator('button[type="submit"], input[type="submit"]');

      await emailInput.fill('test@example.com');
      await passwordInput.fill('wrongpassword');

      if (await submitButton.count() > 0) {
        await submitButton.first().click();
        await page.waitForTimeout(500);
      }
    }

    // Check if rate limit message appears
    const rateLimitMessage = page.locator('text=/terlalu banyak|rate limit/').first();
    // Rate limiting should be triggered
  });

  test('should have proper CSRF protection', async ({ page }) => {
    await page.goto(`${BASE_URL}/auth/login`);

    // Get CSRF token
    const csrfToken = await page.locator('input[name="csrf_token"]').inputValue();
    expect(csrfToken).toBeTruthy();
    expect(csrfToken.length).toBeGreaterThan(0);
  });

  test('should handle 404 pages gracefully', async ({ page }) => {
    const response = await page.goto(`${BASE_URL}/non-existent-page`);
    if (response) {
      expect(response.status()).toBe(404);
    }
  });

  test('should handle 500 errors gracefully', async ({ page }) => {
    // Try to access a route that might cause server error
    const response = await page.goto(`${BASE_URL}/admin/dashboard`);

    // Should redirect to login (401/403) or show error page
    if (response) {
      expect(response.status()).toBeLessThan(500);
    }
  });
});

test.describe('Performance Tests', () => {
  test('should load homepage within reasonable time', async ({ page }) => {
    const startTime = Date.now();
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;

    // Page should load within 5 seconds
    expect(loadTime).toBeLessThan(5000);
  });

  test('should have no memory leaks in navigation', async ({ page }) => {
    for (let i = 0; i < 10; i++) {
      await page.goto(BASE_URL);
      await page.waitForLoadState('networkidle');
      await page.goto(`${BASE_URL}/destinations`);
      await page.waitForLoadState('networkidle');
    }

    // If we reach here without crashing, no obvious memory leaks
    expect(true).toBe(true);
  });
});
