import { test, expect } from '@playwright/test';

test.describe('Authentication Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/mywisata/');
  });

  test('should display login page', async ({ page }) => {
    await page.click('text=Masuk');
    await expect(page).toHaveTitle(/Masuk - MyWisata/);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('should display registration page', async ({ page }) => {
    await page.click('text=Daftar');
    await expect(page).toHaveTitle(/Daftar - MyWisata/);
    await expect(page.locator('input[name="name"]')).toBeVisible();
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
  });

  test('should show error for invalid login', async ({ page }) => {
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');
    
    // Wait for error message
    await expect(page.locator('.alert-danger')).toBeVisible({ timeout: 5000 });
  });

  test('should show error for empty fields', async ({ page }) => {
    await page.goto('http://localhost/mywisata/auth/login');
    await page.click('button[type="submit"]');
    
    // Should show validation error
    await expect(page.locator('.alert-danger')).toBeVisible({ timeout: 5000 });
  });

  test('should navigate to forgot password page', async ({ page }) => {
    await page.goto('http://localhost/mywisata/auth/login');
    await page.click('text=Lupa Password?');
    await expect(page).toHaveTitle(/Lupa Password - MyWisata/);
    await expect(page.locator('input[name="email"]')).toBeVisible();
  });
});
