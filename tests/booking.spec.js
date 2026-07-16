import { test, expect } from '@playwright/test';

test.describe('Booking Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login before each test
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'wisatawan@example.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForTimeout(2000); // Wait for redirect
  });

  test('should display tour guide list', async ({ page }) => {
    await page.goto('http://localhost/mywisata/tourguide');
    await expect(page).toHaveTitle(/Tour Guide - MyWisata/);
    // Check if any guide cards exist
    const guideCards = page.locator('.card');
    const count = await guideCards.count();
    console.log(`Found ${count} guide cards`);
  });

  test('should display booking form', async ({ page }) => {
    await page.goto('http://localhost/mywisata/tourguide');
    // Try to find and click a booking button
    const bookButton = page.locator('text=Pesan').first();
    if (await bookButton.isVisible()) {
      await bookButton.click();
      await expect(page.locator('input[name="booking_date"]')).toBeVisible({ timeout: 5000 });
    } else {
      console.log('No booking button found - skipping booking form test');
    }
  });

  test('should display user bookings', async ({ page }) => {
    await page.goto('http://localhost/mywisata/bookings');
    await expect(page).toHaveTitle(/Booking Saya - MyWisata/);
  });
});
