const { test, expect } = require('@playwright/test');

test.describe('Tour Guide Role Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as tour guide
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'guide@test.com');
    await page.fill('input[name="password"]', 'guide123');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost/mywisata/tourguide/dashboard');
  });

  test('Tour guide can view dashboard', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('Dashboard');
    await expect(page.locator('.stat-card')).toHaveCount(4);
  });

  test('Tour guide can view profile', async ({ page }) => {
    await page.goto('/tourguide/profile');
    await expect(page.locator('h1')).toContainText('Profile');
  });

  test('Tour guide can update profile', async ({ page }) => {
    await page.goto('/tourguide/profile');
    await page.fill('input[name="full_name"]', 'Guide Updated');
    await page.fill('textarea[name="bio"]', 'Updated bio');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Tour guide can view bookings', async ({ page }) => {
    await page.goto('/tourguide/bookings');
    await expect(page.locator('h1')).toContainText('Bookings');
    await expect(page.locator('table')).toBeVisible();
  });

  test('Tour guide can accept booking', async ({ page }) => {
    await page.goto('/tourguide/bookings');
    const acceptButton = page.locator('button').filter({ hasText: 'Accept' }).first();
    if (await acceptButton.isVisible()) {
      await acceptButton.click();
      await expect(page.locator('.alert-success')).toBeVisible();
    }
  });

  test('Tour guide can reject booking', async ({ page }) => {
    await page.goto('/tourguide/bookings');
    const rejectButton = page.locator('button').filter({ hasText: 'Reject' }).first();
    if (await rejectButton.isVisible()) {
      await rejectButton.click();
      await expect(page.locator('.alert-success')).toBeVisible();
    }
  });

  test('Tour guide can view schedule', async ({ page }) => {
    await page.goto('/tourguide/schedule');
    await expect(page.locator('h1')).toContainText('Schedule');
  });

  test('Tour guide can view earnings', async ({ page }) => {
    await page.goto('/tourguide/earnings');
    await expect(page.locator('h1')).toContainText('Earnings');
  });

  test('Tour guide can view reviews', async ({ page }) => {
    await page.goto('/tourguide/reviews');
    await expect(page.locator('h1')).toContainText('Reviews');
  });

  test('Tour guide can respond to review', async ({ page }) => {
    await page.goto('/tourguide/reviews');
    const reviewCard = page.locator('.card').first();
    if (await reviewCard.isVisible()) {
      await reviewCard.locator('button').filter({ hasText: 'Respond' }).click();
      await page.fill('textarea[name="response"]', 'Thank you for your review!');
      await page.click('button[type="submit"]');
      await expect(page.locator('.alert-success')).toBeVisible();
    }
  });

  test('Tour guide can view availability', async ({ page }) => {
    await page.goto('/tourguide/availability');
    await expect(page.locator('h1')).toContainText('Availability');
  });

  test('Tour guide can set availability', async ({ page }) => {
    await page.goto('/tourguide/availability');
    await page.click('text=Set Availability');
    await page.fill('input[name="date"]', '2026-08-01');
    await page.fill('input[name="start_time"]', '09:00');
    await page.fill('input[name="end_time"]', '17:00');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('Tour guide can view notifications', async ({ page }) => {
    await page.goto('/tourguide/notifications');
    await expect(page.locator('h1')).toContainText('Notifications');
  });

  test('Tour guide can logout', async ({ page }) => {
    await page.click('text=Logout');
    await expect(page).toHaveURL(/.*auth\/login/);
  });
});
