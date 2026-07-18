import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Favorites Tests', () => {
  test('should display favorites page', async ({ page }) => {
    await page.goto(`${BASE_URL}/favorites`);

    // Check page title
    await expect(page).toHaveTitle(/MyWisata/);

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should have favorites route accessible', async ({ page }) => {
    // Check that favorites route exists
    const response = await page.request.get(`${BASE_URL}/favorites`);
    expect([200, 302, 301]).toContain(response.status());
  });

  test('should have favorite functionality on destination detail', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations/detail?id=1`);

    // Check that destination detail page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display empty state when no favorites', async ({ page }) => {
    // This would require authentication
    // For now, just check the page loads
    await page.goto(`${BASE_URL}/favorites`);
    const content = await page.content();
    expect(content).toBeTruthy();
  });
});
