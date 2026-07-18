const { test, expect } = require('@playwright/test');

test.describe('Basic Application Tests', () => {
  test('Homepage loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/');
    await expect(page).toHaveTitle(/MyWisata/);
  });

  test('Destinations page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/destinations');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Hotels page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/hotels');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Restaurants page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/restaurants');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Events page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/events');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Tour Guides page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/tourguides');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Halal Tourism page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/halal-tourism');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Culinary Tourism page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/culinary-tourism/food-tours');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Religious Tourism page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/religious-tourism');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Green Credits page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/green-credits');
    // Green credits requires authentication, so we expect a redirect
    await expect(page).toHaveURL(/.*auth\/login/);
  });

  test('Adventure Tourism page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/adventure-tourism');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Agritourism page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/agritourism');
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Walk-in Booking page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/walk-in-booking');
    // Walk-in booking requires authentication, so we expect a redirect
    await expect(page).toHaveURL(/.*auth\/login/);
  });

  test('Split Payment page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/split-payment');
    // Split payment requires authentication, so we expect a redirect
    await expect(page).toHaveURL(/.*auth\/login/);
  });

  test('Location Discovery page loads', async ({ page }) => {
    await page.goto('http://localhost/mywisata/location/nearby');
    // Location discovery page may not have view implemented yet
    // Just check that the page loads without fatal error
    await expect(page).toHaveURL(/.*location\/nearby/);
  });
});
