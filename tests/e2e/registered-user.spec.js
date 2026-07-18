const { test, expect } = require('@playwright/test');

test.describe('Registered User Role Tests', () => {
  test.beforeEach(async ({ page }) => {
    // Login as registered user
    await page.goto('http://localhost/mywisata/auth/login');
    await page.fill('input[name="email"]', 'user@test.com');
    await page.fill('input[name="password"]', 'password123');
    await page.click('button[type="submit"]');
    await page.waitForURL('http://localhost/mywisata/');
  });

  test('User can view dashboard', async ({ page }) => {
    await page.goto('/dashboard');
    await expect(page.locator('h1')).toContainText('Dashboard');
  });

  test('User can view profile', async ({ page }) => {
    await page.click('text=Profile');
    await expect(page.locator('h1')).toContainText('Profile');
  });

  test('User can update profile', async ({ page }) => {
    await page.goto('/profile');
    await page.fill('input[name="full_name"]', 'Test User Updated');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('User can view bookings', async ({ page }) => {
    await page.goto('/bookings');
    await expect(page.locator('h1')).toContainText('Bookings');
  });

  test('User can create booking', async ({ page }) => {
    await page.goto('/destinations');
    await page.click('.card-body a').first();
    await page.click('text=Booking');
    await page.fill('input[name="travel_date"]', '2026-08-01');
    await page.fill('input[name="number_of_people"]', '2');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('User can view favorites', async ({ page }) => {
    await page.goto('/favorites');
    await expect(page.locator('h1')).toContainText('Favorites');
  });

  test('User can add to favorites', async ({ page }) => {
    await page.goto('/destinations');
    await page.click('.card-body a').first();
    await page.click('text=Favorite');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('User can view green credits', async ({ page }) => {
    await page.goto('/green-credits');
    await expect(page.locator('h1')).toContainText('Green Credits');
    await expect(page.locator('text=Saldo')).toBeVisible();
  });

  test('User can claim green credit rewards', async ({ page }) => {
    await page.goto('/green-credits');
    const rewardButton = page.locator('button').filter({ hasText: 'Claim' }).first();
    if (await rewardButton.isVisible()) {
      await rewardButton.click();
      await expect(page.locator('.alert-success')).toBeVisible();
    }
  });

  test('User can view itinerary', async ({ page }) => {
    await page.goto('/itinerary');
    await expect(page.locator('h1')).toContainText('Itinerary');
  });

  test('User can create itinerary', async ({ page }) => {
    await page.goto('/itinerary');
    await page.click('text=Create Itinerary');
    await page.fill('input[name="name"]', 'Test Itinerary');
    await page.fill('input[name="start_date"]', '2026-08-01');
    await page.fill('input[name="end_date"]', '2026-08-05');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('User can view social features', async ({ page }) => {
    await page.goto('/social-features/wishlists');
    await expect(page.locator('h1')).toContainText('Wishlists');
  });

  test('User can create wishlist', async ({ page }) => {
    await page.goto('/social-features/wishlists');
    await page.click('text=Create Wishlist');
    await page.fill('input[name="name"]', 'Test Wishlist');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('User can view notifications', async ({ page }) => {
    await page.goto('/notifications');
    await expect(page.locator('h1')).toContainText('Notifications');
  });

  test('User can logout', async ({ page }) => {
    await page.click('text=Logout');
    await expect(page).toHaveURL(/.*auth\/login/);
  });

  test('User can join split payment group', async ({ page }) => {
    await page.goto('/split-payment/join-group');
    await page.fill('input[name="group_code"]', 'TEST123');
    await page.fill('input[name="participant_name"]', 'Test User');
    await page.fill('input[name="participant_email"]', 'user@test.com');
    await page.fill('input[name="participant_phone"]', '08123456789');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });

  test('User can view walk-in booking', async ({ page }) => {
    await page.goto('/walk-in-booking');
    await expect(page.locator('h1')).toContainText('Express Book');
  });

  test('User can create walk-in booking', async ({ page }) => {
    await page.goto('/walk-in-booking');
    await page.fill('input[name="customer_name"]', 'Walk-in Customer');
    await page.fill('input[name="customer_phone"]', '08123456789');
    await page.fill('input[name="booking_date"]', '2026-08-01');
    await page.fill('input[name="number_of_people"]', '2');
    await page.click('button[type="submit"]');
    await expect(page.locator('.alert-success')).toBeVisible();
  });
});
