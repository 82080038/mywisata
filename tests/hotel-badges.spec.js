import { test, expect } from '@playwright/test';

test.describe('Hotel Muslim-Friendly Badges', () => {
  test('should display hotel page with Muslim-friendly badges', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Check if page loads
    await expect(page.locator('h1')).toContainText('Hotel & Homestay');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
  });

  test('should display prayer room badges on hotel cards', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for prayer room badges
    const prayerBadges = page.locator('.badge:has-text("Prayer Room")');
    const count = await prayerBadges.count();
    
    expect(count).toBeGreaterThan(0);
    
    // Check first prayer room badge
    const firstBadge = prayerBadges.first();
    await expect(firstBadge).toBeVisible();
    await expect(firstBadge).toContainText('Prayer Room');
  });

  test('should display alcohol free badges', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for alcohol free badges
    const alcoholBadges = page.locator('.badge:has-text("Alcohol Free")');
    const count = await alcoholBadges.count();
    
    expect(count).toBeGreaterThan(0);
    
    // Check first alcohol free badge
    const firstBadge = alcoholBadges.first();
    await expect(firstBadge).toBeVisible();
    await expect(firstBadge).toContainText('Alcohol Free');
  });

  test('should display women only facilities badges', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for women only badges
    const womenBadges = page.locator('.badge:has-text("Women Only")');
    const count = await womenBadges.count();
    
    expect(count).toBeGreaterThan(0);
    
    // Check first women only badge
    const firstBadge = womenBadges.first();
    await expect(firstBadge).toBeVisible();
    await expect(firstBadge).toContainText('Women Only');
  });

  test('should display qibla direction badges', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for qibla direction badges (compass icon)
    const qiblaBadges = page.locator('.badge').filter({ hasText: /Northwest|Southeast|Northeast|Southwest|North|South|East|West/ });
    const count = await qiblaBadges.count();
    
    expect(count).toBeGreaterThan(0);
  });

  test('should display distance to mosque badges', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for distance badges (with km text)
    const distanceBadges = page.locator('.badge').filter({ hasText: /km/ });
    const count = await distanceBadges.count();
    
    expect(count).toBeGreaterThan(0);
  });

  test('should display all Muslim-friendly badges on single hotel card', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Get first hotel card
    const firstCard = page.locator('.card').first();
    
    // Check for multiple badges
    const badges = firstCard.locator('.badge');
    const badgeCount = await badges.count();
    
    expect(badgeCount).toBeGreaterThan(2); // Should have multiple badges
  });

  test('should have proper badge styling and icons', async ({ page }) => {
    await page.goto('http://localhost:8080/hotels');
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for prayer room badge with mosque icon
    const prayerBadge = page.locator('.badge:has-text("Prayer Room")').first();
    await expect(prayerBadge).toBeVisible();
    
    // Check badge has proper class
    const badgeClass = await prayerBadge.getAttribute('class');
    expect(badgeClass).toContain('badge');
  });
});
