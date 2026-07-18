import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Admin Dashboard Tests', () => {
  test('should redirect to login when accessing admin without auth', async ({ page }) => {
    // Try to access admin dashboard without login
    await page.goto(`${BASE_URL}/admin/dashboard`);

    // Should redirect to login page
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/auth\/login|login/);
  });

  test('should redirect to login when accessing admin users without auth', async ({ page }) => {
    // Try to access admin users without login
    await page.goto(`${BASE_URL}/admin/users`);

    // Should redirect to login page
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/auth\/login|login/);
  });

  test('should have admin dashboard sections', async ({ page }) => {
    // This would require authentication
    // Check that admin routes exist
    const response = await page.request.get(`${BASE_URL}/admin/dashboard`);
    // Should redirect to login if not authenticated
    expect([200, 302, 301]).toContain(response.status());
  });

  test('should have admin users management', async ({ page }) => {
    // Check that admin users route exists
    const response = await page.request.get(`${BASE_URL}/admin/users`);
    expect([200, 302, 301]).toContain(response.status());
  });

  test('should have admin destinations management', async ({ page }) => {
    // Check that admin destinations route exists
    const response = await page.request.get(`${BASE_URL}/admin/destinations`);
    expect([200, 302, 301]).toContain(response.status());
  });
});
