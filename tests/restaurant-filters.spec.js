import { test, expect } from '@playwright/test';

test.describe('Restaurant Dietary Filters', () => {
  test('should display restaurant page with dietary filter checkboxes', async ({ page }) => {
    await page.goto('http://localhost:8080/restaurants');
    
    // Check if page loads
    await expect(page.locator('h1')).toContainText('Restoran & UMKM');
    
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
    await page.goto('http://localhost:8080/restaurants');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for halal badges
    const halalBadges = page.locator('.badge:has-text("Halal")');
    const count = await halalBadges.count();
    
    expect(count).toBeGreaterThan(0);
    
    // Check first halal badge
    const firstBadge = halalBadges.first();
    await expect(firstBadge).toBeVisible();
    await expect(firstBadge).toContainText('Halal');
  });

  test('should display MUI certification badges', async ({ page }) => {
    await page.goto('http://localhost:8080/restaurants');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for MUI badges
    const muiBadges = page.locator('.badge:has-text("MUI")');
    const count = await muiBadges.count();
    
    expect(count).toBeGreaterThan(0);
    
    // Check first MUI badge
    const firstBadge = muiBadges.first();
    await expect(firstBadge).toBeVisible();
    await expect(firstBadge).toContainText('MUI');
  });

  test('should filter restaurants by halal status', async ({ page }) => {
    await page.goto('http://localhost:8080/restaurants?is_halal=1');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check that halal badges are present
    const halalBadges = page.locator('.badge:has-text("Halal")');
    const count = await halalBadges.count();
    
    expect(count).toBeGreaterThan(0);
    
    // All visible restaurants should have halal badges
    const restaurantCards = page.locator('.card');
    const cardCount = await restaurantCards.count();
    
    for (let i = 0; i < cardCount; i++) {
      const card = restaurantCards.nth(i);
      const hasHalalBadge = await card.locator('.badge:has-text("Halal")').count();
      expect(hasHalalBadge).toBeGreaterThan(0);
    }
  });

  test('should filter restaurants by vegetarian-friendly', async ({ page }) => {
    await page.goto('http://localhost:8080/restaurants?is_vegetarian_friendly=1');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check that vegetarian badges are present
    const vegBadges = page.locator('.badge:has-text("Vegetarian")');
    const count = await vegBadges.count();
    
    expect(count).toBeGreaterThan(0);
  });

  test('should filter restaurants by alcohol-free', async ({ page }) => {
    await page.goto('http://localhost:8080/restaurants?is_alcohol_free=1');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check that alcohol-free badges are present
    const alcoholFreeBadges = page.locator('.badge:has-text("Alcohol Free")');
    const count = await alcoholFreeBadges.count();
    
    expect(count).toBeGreaterThan(0);
  });

  test('should apply multiple dietary filters', async ({ page }) => {
    await page.goto('http://localhost:8080/restaurants?is_halal=1&has_prayer_space=1');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check that both halal and prayer space badges are present
    const halalBadges = page.locator('.badge:has-text("Halal")');
    const prayerBadges = page.locator('.badge:has-text("Prayer Space")');
    
    expect(await halalBadges.count()).toBeGreaterThan(0);
    expect(await prayerBadges.count()).toBeGreaterThan(0);
  });
});
