# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: homepage.spec.ts >> Homepage Tests >> should display hero section
- Location: tests/e2e/homepage.spec.ts:25:7

# Error details

```
Error: expect(locator).toBeVisible() failed

Locator: locator('.hero, .jumbotron, header').first()
Expected: visible
Timeout: 5000ms
Error: element(s) not found

Call log:
  - Expect "toBeVisible" with timeout 5000ms
  - waiting for locator('.hero, .jumbotron, header').first()

```

```yaml
- navigation:
  - link "MyWisata":
    - /url: http://localhost/mywisata/
  - button
  - list:
    - listitem:
      - link "Beranda":
        - /url: http://localhost/mywisata/
    - listitem:
      - link "Tentang":
        - /url: http://localhost/mywisata/home/about
    - listitem:
      - link "Kontak":
        - /url: http://localhost/mywisata/home/contact
  - list:
    - listitem:
      - link "Masuk":
        - /url: http://localhost/mywisata/auth/login
    - listitem:
      - link "Daftar":
        - /url: http://localhost/mywisata/auth/register
- main:
  - heading "MyWisata - Platform Marketplace Pariwisata" [level=1]
  - paragraph: Temukan tour guide profesional, destinasi wisata, hotel, restoran, dan event budaya di Indonesia.
  - heading "Tour Guide" [level=5]
  - paragraph: Temukan pemandu wisata profesional untuk perjalanan Anda
  - heading "Destinasi" [level=5]
  - paragraph: Jelajahi destinasi wisata terbaik di Indonesia
  - heading "Tiket" [level=5]
  - paragraph: Beli tiket wisata dengan mudah dan cepat
  - heading "Hotel" [level=5]
  - paragraph: Booking hotel dan homestay dengan harga terbaik
  - heading "Restoran" [level=5]
  - paragraph: Temukan kuliner lokal dan restoran terbaik
  - heading "Event" [level=5]
  - paragraph: Daftar event budaya dan festival menarik
- contentinfo:
  - heading "MyWisata" [level=5]
  - paragraph: Platform marketplace untuk layanan pariwisata di Indonesia.
  - heading "Tautan" [level=5]
  - list:
    - listitem:
      - link "Beranda":
        - /url: http://localhost/mywisata/
    - listitem:
      - link "Tentang":
        - /url: http://localhost/mywisata/home/about
    - listitem:
      - link "Kontak":
        - /url: http://localhost/mywisata/home/contact
  - heading "Kontak" [level=5]
  - paragraph: admin@mywisata.com
  - paragraph: +62 812 3456 7890
  - separator
  - paragraph: © 2026 MyWisata Application. All rights reserved.
```

# Test source

```ts
  1  | import { test, expect } from '@playwright/test';
  2  | 
  3  | const BASE_URL = 'http://localhost/mywisata';
  4  | 
  5  | test.describe('Homepage Tests', () => {
  6  |   test('should load homepage successfully', async ({ page }) => {
  7  |     await page.goto(BASE_URL);
  8  | 
  9  |     // Check page title
  10 |     await expect(page).toHaveTitle(/MyWisata/);
  11 | 
  12 |     // Check if page loads without errors
  13 |     const content = await page.content();
  14 |     expect(content).toBeTruthy();
  15 |   });
  16 | 
  17 |   test('should display navigation menu', async ({ page }) => {
  18 |     await page.goto(BASE_URL);
  19 | 
  20 |     // Check for navigation elements
  21 |     const nav = page.locator('nav');
  22 |     await expect(nav).toBeVisible();
  23 |   });
  24 | 
  25 |   test('should display hero section', async ({ page }) => {
  26 |     await page.goto(BASE_URL);
  27 | 
  28 |     // Check for hero section
  29 |     const hero = page.locator('.hero, .jumbotron, header');
> 30 |     await expect(hero.first()).toBeVisible();
     |                                ^ Error: expect(locator).toBeVisible() failed
  31 |   });
  32 | 
  33 |   test('should have working links', async ({ page }) => {
  34 |     await page.goto(BASE_URL);
  35 | 
  36 |     // Get all links
  37 |     const links = page.locator('a[href]').all();
  38 | 
  39 |     // Check that at least some links exist
  40 |     const linkCount = await page.locator('a[href]').count();
  41 |     expect(linkCount).toBeGreaterThan(0);
  42 |   });
  43 | 
  44 |   test('should be responsive', async ({ page }) => {
  45 |     await page.goto(BASE_URL);
  46 | 
  47 |     // Test mobile viewport
  48 |     await page.setViewportSize({ width: 375, height: 667 });
  49 |     await expect(page).toHaveTitle(/MyWisata/);
  50 | 
  51 |     // Test tablet viewport
  52 |     await page.setViewportSize({ width: 768, height: 1024 });
  53 |     await expect(page).toHaveTitle(/MyWisata/);
  54 | 
  55 |     // Test desktop viewport
  56 |     await page.setViewportSize({ width: 1920, height: 1080 });
  57 |     await expect(page).toHaveTitle(/MyWisata/);
  58 |   });
  59 | });
  60 | 
```