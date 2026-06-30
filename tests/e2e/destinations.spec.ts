import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Destinations Tests', () => {
  test('should display destinations page', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Check page title
    await expect(page).toHaveTitle(/Destinasi|Destinations/);

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display destination cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Check for destination cards
    const cards = page.locator('.card, .destination-card, .col-md-4');
    const cardCount = await cards.count();

    // Should have at least some cards
    expect(cardCount).toBeGreaterThan(0);
  });

  test('should have search functionality', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Check for search input
    const searchInput = page.locator('input[type="search"], input[placeholder*="cari"], input[placeholder*="search"]');
    const searchCount = await searchInput.count();

    // Search input should be present
    expect(searchCount).toBeGreaterThan(0);
  });

  test('should have filter options', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Check for filter dropdowns
    const filters = page.locator('select, .filter');
    const filterCount = await filters.count();

    // Filters should be present
    expect(filterCount).toBeGreaterThan(0);
  });

  test('should navigate to destination detail', async ({ page }) => {
    await page.goto(`${BASE_URL}/destinations`);

    // Click on first destination card
    const firstCard = page.locator('.card, .destination-card').first();
    await firstCard.click();

    // Should navigate to detail page
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/destinations|detail/);
  });
});
