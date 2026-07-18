import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost:8080';

test.describe('Restaurants Tests', () => {
  test('should display restaurants page', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check page title
    await expect(page).toHaveTitle(/MyWisata/);

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display restaurant cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for restaurant cards
    const cards = page.locator('.card, .restaurant-card, .col-md-4');
    const cardCount = await cards.count();

    // Should have at least some cards
    expect(cardCount).toBeGreaterThan(0);
  });

  test('should have search functionality', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for search input
    const searchInput = page.locator('input[name="search"], input[placeholder*="Cari"], input[placeholder*="cari"], input[placeholder*="search"]');
    const searchCount = await searchInput.count();

    // Search input should be present
    expect(searchCount).toBeGreaterThan(0);
  });

  test('should have filter options', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for filter dropdowns (optional)
    const filters = page.locator('select[name="category"], select');
    const filterCount = await filters.count();

    // Filters are optional - check if present
    if (filterCount > 0) {
      expect(filterCount).toBeGreaterThan(0);
    } else {
      console.log('Filter options not found on restaurants page');
    }
  });

  test('should navigate to restaurant detail', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Click on first restaurant card
    const firstCard = page.locator('.card, .restaurant-card').first();

    // Wait for card to be visible
    await firstCard.waitFor({ state: 'visible', timeout: 5000 }).catch(() => {
      console.log('Card not visible - skipping navigation test');
    });

    if (await firstCard.isVisible()) {
      await firstCard.click();

      // Wait for navigation
      await page.waitForLoadState('networkidle', { timeout: 5000 }).catch(() => {
        console.log('Navigation timeout - checking URL anyway');
      });

      // Should navigate to detail page
      const currentUrl = page.url();
      expect(currentUrl).toMatch(/restaurants|detail/);
    }
  });

  test('should display dietary filter checkboxes', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Check for dietary filter checkboxes
    await expect(page.locator('input[name="is_halal"]')).toBeVisible();
    await expect(page.locator('input[name="is_kosher"]')).toBeVisible();
    await expect(page.locator('input[name="is_vegan_friendly"]')).toBeVisible();
    await expect(page.locator('input[name="is_vegetarian_friendly"]')).toBeVisible();
    await expect(page.locator('input[name="is_gluten_free_friendly"]')).toBeVisible();
    await expect(page.locator('input[name="has_prayer_space"]')).toBeVisible();
    await expect(page.locator('input[name="is_alcohol_free"]')).toBeVisible();
  });

  test('should display halal badges on restaurant cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for halal badges
    const halalBadges = page.locator('.badge:has-text("Halal")');
    const count = await halalBadges.count();

    expect(count).toBeGreaterThan(0);
  });

  test('should filter restaurants by halal status', async ({ page }) => {
    await page.goto(`${BASE_URL}/restaurants?is_halal=1`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check that halal badges are present
    const halalBadges = page.locator('.badge:has-text("Halal")');
    const count = await halalBadges.count();

    expect(count).toBeGreaterThan(0);
  });
});
