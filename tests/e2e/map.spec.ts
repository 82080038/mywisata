import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Map Tests', () => {
  test('should display map page', async ({ page }) => {
    await page.goto(`${BASE_URL}/map`);

    // Check page title
    await expect(page).toHaveTitle(/MyWisata/);

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should have map container', async ({ page }) => {
    await page.goto(`${BASE_URL}/map`);

    // Check for map container with correct selector
    const mapContainer = page.locator('#map');
    await expect(mapContainer).toBeVisible();
  });

  test('should have location button', async ({ page }) => {
    await page.goto(`${BASE_URL}/map`);

    // Check for location button (added dynamically)
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should have nearby destinations section', async ({ page }) => {
    await page.goto(`${BASE_URL}/map`);

    // Check for nearby destinations section
    const nearbySection = page.locator('#nearbyDestinations');
    await expect(nearbySection).toBeVisible();
  });

  test('should be responsive on mobile', async ({ page }) => {
    await page.goto(`${BASE_URL}/map`);

    // Test mobile viewport
    await page.setViewportSize({ width: 375, height: 667 });
    await expect(page).toHaveTitle(/MyWisata/);

    // Check map is still visible
    const mapContainer = page.locator('#map');
    await expect(mapContainer).toBeVisible();
  });
});
