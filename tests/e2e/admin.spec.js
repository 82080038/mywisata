const { test, expect } = require('@playwright/test');

test.describe('Admin Role Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as admin
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'admin@mywisata.com');
    await page.fill('input[name="password"]', 'admin123');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost/mywisata/admin/dashboard');
  });

  test('Admin can view dashboard', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('Dashboard');
    await expect(page.locator('.stat-card')).toHaveCount(4);
  });

  test('Admin can view users management', async ({ page }) => {
    await page.goto('/admin/users');
    await expect(page.locator('h1')).toContainText('Users');
    await expect(page.locator('table')).toBeVisible();
  });

  test('Admin can view destinations management', async ({ page }) => {
    await page.goto('/admin/destinations');
    await expect(page.locator('h1')).toContainText('Destinations');
    await expect(page.locator('table')).toBeVisible();
  });

  test('Admin can create destination', async ({ page }) => {
    await page.goto('/admin/destinations');
    await page.click('text=Add Destination');
    await page.fill('input[name="name"]', 'Test Destination');
    await page.fill('input[name="city"]', 'Jakarta');
    await page.fill('textarea[name="description"]', 'Test description');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Admin can view tour guides management', async ({ page }) => {
    await page.goto('/admin/guides');
    await expect(page.locator('h1')).toContainText('Tour Guides');
    await expect(page.locator('table')).toBeVisible();
  });

  test('Admin can view reports', async ({ page }) => {
    await page.goto('/admin/reports');
    await expect(page.locator('h1')).toContainText('Reports');
  });

  test('Admin can view settings', async ({ page }) => {
    await page.goto('/admin/settings');
    await expect(page.locator('h1')).toContainText('Settings');
  });

  test('Admin can update settings', async ({ page }) => {
    await page.goto('/admin/settings');
    await page.fill('input[name="site_name"]', 'MyWisata Updated');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Admin can view backup management', async ({ page }) => {
    await page.goto('/admin/backup');
    await expect(page.locator('h1')).toContainText('Backup');
  });

  test('Admin can create backup', async ({ page }) => {
    await page.goto('/admin/backup');
    await page.click('text=Create Backup');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Admin can manage halal tourism packages', async ({ page }) => {
    await page.goto('/admin/halal-tourism');
    await expect(page.locator('h1')).toContainText('Halal Tourism');
  });

  test('Admin can manage culinary tourism', async ({ page }) => {
    await page.goto('/admin/culinary-tourism');
    await expect(page.locator('h1')).toContainText('Culinary Tourism');
  });

  test('Admin can manage religious tourism', async ({ page }) => {
    await page.goto('/admin/religious-tourism');
    await expect(page.locator('h1')).toContainText('Religious Tourism');
  });

  test('Admin can manage green credits', async ({ page }) => {
    await page.goto('/admin/green-credits');
    await expect(page.locator('h1')).toContainText('Green Credits');
  });

  test('Admin can manage adventure tourism', async ({ page }) => {
    await page.goto('/admin/adventure-tourism');
    await expect(page.locator('h1')).toContainText('Adventure Tourism');
  });

  test('Admin can manage agritourism', async ({ page }) => {
    await page.goto('/admin/agritourism');
    await expect(page.locator('h1')).toContainText('Agritourism');
  });

  test('Admin can view analytics', async ({ page }) => {
    await page.goto('/admin/analytics');
    await expect(page.locator('h1')).toContainText('Analytics');
  });

  test('Admin can logout', async ({ page }) => {
    await page.click('text=Logout');
    await expect(page).toHaveURL(/.*auth\/login/);
  });
});
