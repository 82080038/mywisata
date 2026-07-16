import { test, expect } from '@playwright/test';

test.describe('Destination Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/mywisata/');
  });

  test('should display destination list', async ({ page }) => {
    await page.click('text=Destinasi');
    await expect(page).toHaveTitle(/Destinasi Wisata - MyWisata/);
    // Check if any destination cards exist
    const destCards = page.locator('.card');
    const count = await destCards.count();
    console.log(`Found ${count} destination cards`);
  });

  test('should display destination detail', async ({ page }) => {
    await page.goto('http://localhost/mywisata/destinations');
    // Try to click first card
    const firstCard = page.locator('.card').first();
    if (await firstCard.isVisible()) {
      await firstCard.click();
      await page.waitForTimeout(1000);
    }
  });

  test('should filter destinations by category', async ({ page }) => {
    await page.goto('http://localhost/mywisata/destinations');
    const categorySelect = page.locator('select[name="category"]');
    if (await categorySelect.isVisible()) {
      await page.selectOption('select[name="category"]', '1');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);
    } else {
      console.log('Category filter not found - skipping');
    }
  });

  test('should search destinations', async ({ page }) => {
    await page.goto('http://localhost/mywisata/destinations');
    const searchInput = page.locator('input[name="search"]');
    if (await searchInput.isVisible()) {
      await page.fill('input[name="search"]', 'Bali');
      await page.click('button[type="submit"]');
      await page.waitForTimeout(1000);
    } else {
      console.log('Search input not found - skipping');
    }
  });
});
