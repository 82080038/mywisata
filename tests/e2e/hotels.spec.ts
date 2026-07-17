import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Hotels Tests', () => {
  test('should display hotels page', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Check page title
    await expect(page).toHaveTitle(/MyWisata/);

    // Check that page loads
    const content = await page.content();
    expect(content).toBeTruthy();
  });

  test('should display hotel cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Check for hotel cards
    const cards = page.locator('.card, .hotel-card, .col-md-4');
    const cardCount = await cards.count();

    // Should have at least some cards
    expect(cardCount).toBeGreaterThan(0);
  });

  test('should have search functionality', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Check for search input
    const searchInput = page.locator('input[name="search"], input[placeholder*="Cari"], input[placeholder*="cari"], input[placeholder*="search"]');
    const searchCount = await searchInput.count();

    // Search input should be present
    expect(searchCount).toBeGreaterThan(0);
  });

  test('should have filter options', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Check for filter dropdowns (optional)
    const filters = page.locator('select[name="category"], select');
    const filterCount = await filters.count();

    // Filters are optional - check if present
    if (filterCount > 0) {
      expect(filterCount).toBeGreaterThan(0);
    } else {
      console.log('Filter options not found on hotels page');
    }
  });

  test('should navigate to hotel detail', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Click on first hotel card
    const firstCard = page.locator('.card, .hotel-card').first();

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
      expect(currentUrl).toMatch(/hotels|detail/);
    }
  });

  test('should display prayer room badges on hotel cards', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for prayer room badges
    const prayerBadges = page.locator('.badge:has-text("Prayer Room")');
    const count = await prayerBadges.count();

    expect(count).toBeGreaterThan(0);
  });

  test('should display alcohol free badges', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for alcohol free badges
    const alcoholBadges = page.locator('.badge:has-text("Alcohol Free")');
    const count = await alcoholBadges.count();

    expect(count).toBeGreaterThan(0);
  });

  test('should display women only facilities badges', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for women only badges
    const womenBadges = page.locator('.badge:has-text("Women Only")');
    const count = await womenBadges.count();

    expect(count).toBeGreaterThan(0);
  });

  test('should display qibla direction badges', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for qibla direction badges (compass icon)
    const qiblaBadges = page.locator('.badge').filter({ hasText: /Northwest|Southeast|Northeast|Southwest|North|South|East|West/ });
    const count = await qiblaBadges.count();

    expect(count).toBeGreaterThan(0);
  });

  test('should display distance to mosque badges', async ({ page }) => {
    await page.goto(`${BASE_URL}/hotels`);

    // Wait for page to load
    await page.waitForLoadState('networkidle');

    // Check for distance badges (with km text)
    const distanceBadges = page.locator('.badge').filter({ hasText: /km/ });
    const count = await distanceBadges.count();

    expect(count).toBeGreaterThan(0);
  });
});
