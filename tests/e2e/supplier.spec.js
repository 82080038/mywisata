const { test, expect } = require('@playwright/test');

test.describe('Supplier Role Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as supplier
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'supplier@test.com');
    await page.fill('input[name="password"]', 'supplier123');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost/mywisata/supplier/dashboard');
  });

  test('Supplier can view dashboard', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('Dashboard');
    await expect(page.locator('.stat-card')).toHaveCount(4);
  });

  test('Supplier can view profile', async ({ page }) => {
    await page.goto('/supplier/profile');
    await expect(page.locator('h1')).toContainText('Profile');
  });

  test('Supplier can update profile', async ({ page }) => {
    await page.goto('/supplier/profile');
    await page.fill('input[name="company_name"]', 'Supplier Updated');
    await page.fill('textarea[name="description"]', 'Updated description');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Supplier can view destinations', async ({ page }) => {
    await page.goto('/supplier/destinations');
    await expect(page.locator('h1')).toContainText('Destinations');
    await expect(page.locator('table')).toBeVisible();
  });

  test('Supplier can create destination', async ({ page }) => {
    await page.goto('/supplier/destinations');
    await page.click('text=Add Destination');
    await page.fill('input[name="name"]', 'Supplier Destination');
    await page.fill('input[name="city"]', 'Bandung');
    await page.fill('textarea[name="description"]', 'Test description');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Supplier can view bookings', async ({ page }) => {
    await page.goto('/supplier/bookings');
    await expect(page.locator('h1')).toContainText('Bookings');
    await expect(page.locator('table')).toBeVisible();
  });

  test('Supplier can confirm booking', async ({ page }) => {
    await page.goto('/supplier/bookings');
    const confirmButton = page.locator('button').filter({ hasText: 'Confirm' }).first();
    if (await confirmButton.isVisible()) {
      await confirmButton.click();
      await expect(page.locator('.alert-success')).toBeVisible();
    }
  });

  test('Supplier can view products', async ({ page }) => {
    await page.goto('/supplier/products');
    await expect(page.locator('h1')).toContainText('Products');
  });

  test('Supplier can add product', async ({ page }) => {
    await page.goto('/supplier/products');
    await page.click('text=Add Product');
    await page.fill('input[name="name"]', 'Test Product');
    await page.fill('input[name="price"]', '100000');
    await page.fill('input[name="stock"]', '10');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Supplier can view analytics', async ({ page }) => {
    await page.goto('/supplier/analytics');
    await expect(page.locator('h1')).toContainText('Analytics');
  });

  test('Supplier can view earnings', async ({ page }) => {
    await page.goto('/supplier/earnings');
    await expect(page.locator('h1')).toContainText('Earnings');
  });

  test('Supplier can view reviews', async ({ page }) => {
    await page.goto('/supplier/reviews');
    await expect(page.locator('h1')).toContainText('Reviews');
  });

  test('Supplier can view notifications', async ({ page }) => {
    await page.goto('/supplier/notifications');
    await expect(page.locator('h1')).toContainText('Notifications');
  });

  test('Supplier can manage halal tourism packages', async ({ page }) => {
    await page.goto('/supplier/halal-tourism');
    await expect(page.locator('h1')).toContainText('Halal Tourism');
  });

  test('Supplier can manage culinary tourism', async ({ page }) => {
    await page.goto('/supplier/culinary-tourism');
    await expect(page.locator('h1')).toContainText('Culinary Tourism');
  });

  test('Supplier can manage adventure tourism', async ({ page }) => {
    await page.goto('/supplier/adventure-tourism');
    await expect(page.locator('h1')).toContainText('Adventure Tourism');
  });

  test('Supplier can manage agritourism', async ({ page }) => {
    await page.goto('/supplier/agritourism');
    await expect(page.locator('h1')).toContainText('Agritourism');
  });

  test('Supplier can logout', async ({ page }) => {
    await page.click('text=Logout');
    await expect(page).toHaveURL(/.*auth\/login/);
  });
});
