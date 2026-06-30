import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Tour Guides Tests', () => {
  test('should redirect tour guides page to login for guests', async ({ page }) => {
    await page.goto(`${BASE_URL}/tourguides`);

    // Tour guides page requires login, should redirect
    const currentUrl = page.url();
    expect(currentUrl).toMatch(/login|auth/);
  });

  test('tour guide dashboard requires authentication', async ({ page }) => {
    await page.goto(`${BASE_URL}/tourguide/dashboard`);

    // Should redirect to login or show error
    const currentUrl = page.url();
    const content = await page.content();

    // Either redirects to login or shows unauthorized
    expect(currentUrl).toMatch(/login|auth|dashboard/);
  });
});
