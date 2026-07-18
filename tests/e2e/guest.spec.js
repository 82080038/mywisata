const { test, expect } = require('@playwright/test');

test.describe('Guest User Role Tests', () => {
  test.beforeEach(async ({ page }) => {
    await page.goto('http://localhost/mywisata/');
  });

  test('Guest can view home page', async ({ page }) => {
    await expect(page.locator('h1')).toContainText('MyWisata');
    await expect(page).toHaveTitle(/MyWisata/);
  });

  test('Guest can browse destinations', async ({ page }) => {
    await page.click('text=Destinasi');
    await expect(page).toHaveURL(/.*destinations/);
    await expect(page.locator('h1')).toContainText('Destinasi Wisata');
  });

  test('Guest can view destination details', async ({ page }) => {
    await page.goto('http://localhost/mywisata/destinations');
    await page.click('.card-body a').first();
    await expect(page.locator('h1')).toBeVisible();
  });

  test('Guest can search destinations', async ({ page }) => {
    await page.goto('http://localhost/mywisata/destinations');
    await page.fill('input[name="search"]', 'Bali');
    await page.click('button[type="submit"]');
    await expect(page.locator('.card')).toBeVisible();
  });

  test('Guest can view hotels', async ({ page }) => {
    await page.click('text=Hotels');
    await expect(page).toHaveURL(/.*hotels/);
    await expect(page.locator('h1')).toContainText('Hotel');
  });

  test('Guest can view restaurants', async ({ page }) => {
    await page.click('text=Restaurants');
    await expect(page).toHaveURL(/.*restaurants/);
    await expect(page.locator('h1')).toContainText('Restaurant');
  });

  test('Guest can view events', async ({ page }) => {
    await page.click('text=Events');
    await expect(page).toHaveURL(/.*events/);
    await expect(page.locator('h1')).toContainText('Event');
  });

  test('Guest can access login page', async ({ page }) => {
    await page.click('text=Login');
    await expect(page).toHaveURL(/.*auth\/login/);
    await expect(page.locator('h1')).toContainText('Login');
  });

  test('Guest can access registration page', async ({ page }) => {
    await page.click('text=Register');
    await expect(page).toHaveURL(/.*auth\/register/);
    await expect(page.locator('h1')).toContainText('Register');
  });

  test('Guest can view about page', async ({ page }) => {
    await page.click('text=About');
    await expect(page).toHaveURL(/.*home\/about/);
  });

  test('Guest can view contact page', async ({ page }) => {
    await page.click('text=Contact');
    await expect(page).toHaveURL(/.*home\/contact/);
  });

  test('Guest can view halal tourism packages', async ({ page }) => {
    await page.goto('http://localhost/mywisata/halal-tourism');
    await expect(page.locator('h1')).toContainText('Wisata Halal');
    await expect(page.locator('.card')).toBeVisible();
  });

  test('Guest can view culinary tourism', async ({ page }) => {
    await page.goto('http://localhost/mywisata/culinary-tourism/food-tours');
    await expect(page.locator('h1')).toContainText('Food Tours');
  });

  test('Guest can view religious tourism', async ({ page }) => {
    await page.goto('http://localhost/mywisata/religious-tourism');
    await expect(page.locator('h1')).toContainText('Wisata Religi');
  });

  test('Guest can view green credits page', async ({ page }) => {
    await page.goto('http://localhost/mywisata/green-credits');
    // Guest should see login prompt for green credits
    await expect(page.locator('text=Login')).toBeVisible();
  });

  test('Guest can view adventure tourism', async ({ page }) => {
    await page.goto('http://localhost/mywisata/adventure-tourism');
    await expect(page.locator('h1')).toContainText('Adventure Tourism');
  });

  test('Guest can view agritourism', async ({ page }) => {
    await page.goto('http://localhost/mywisata/agritourism');
    await expect(page.locator('h1')).toContainText('Agritourism');
  });

  test('Guest can view location discovery', async ({ page }) => {
    await page.goto('http://localhost/mywisata/location/nearby');
    await expect(page.locator('h1')).toContainText('Nearby');
  });
});
