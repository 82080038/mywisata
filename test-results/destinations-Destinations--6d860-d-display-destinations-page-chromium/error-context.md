# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: destinations.spec.ts >> Destinations Tests >> should display destinations page
- Location: tests/e2e/destinations.spec.ts:6:7

# Error details

```
Error: expect(page).toHaveTitle(expected) failed

Expected pattern: /Destinasi|Destinations/
Received string:  "MyWisata - Platform Marketplace Pariwisata"
Timeout: 5000ms

Call log:
  - Expect "toHaveTitle" with timeout 5000ms
    14 × unexpected value "MyWisata - Platform Marketplace Pariwisata"

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
  5  | test.describe('Destinations Tests', () => {
  6  |   test('should display destinations page', async ({ page }) => {
  7  |     await page.goto(`${BASE_URL}/destinations`);
  8  | 
  9  |     // Check page title
> 10 |     await expect(page).toHaveTitle(/Destinasi|Destinations/);
     |                        ^ Error: expect(page).toHaveTitle(expected) failed
  11 | 
  12 |     // Check that page loads
  13 |     const content = await page.content();
  14 |     expect(content).toBeTruthy();
  15 |   });
  16 | 
  17 |   test('should display destination cards', async ({ page }) => {
  18 |     await page.goto(`${BASE_URL}/destinations`);
  19 | 
  20 |     // Check for destination cards
  21 |     const cards = page.locator('.card, .destination-card, .col-md-4');
  22 |     const cardCount = await cards.count();
  23 | 
  24 |     // Should have at least some cards
  25 |     expect(cardCount).toBeGreaterThan(0);
  26 |   });
  27 | 
  28 |   test('should have search functionality', async ({ page }) => {
  29 |     await page.goto(`${BASE_URL}/destinations`);
  30 | 
  31 |     // Check for search input
  32 |     const searchInput = page.locator('input[type="search"], input[placeholder*="cari"], input[placeholder*="search"]');
  33 |     const searchCount = await searchInput.count();
  34 | 
  35 |     // Search input should be present
  36 |     expect(searchCount).toBeGreaterThan(0);
  37 |   });
  38 | 
  39 |   test('should have filter options', async ({ page }) => {
  40 |     await page.goto(`${BASE_URL}/destinations`);
  41 | 
  42 |     // Check for filter dropdowns
  43 |     const filters = page.locator('select, .filter');
  44 |     const filterCount = await filters.count();
  45 | 
  46 |     // Filters should be present
  47 |     expect(filterCount).toBeGreaterThan(0);
  48 |   });
  49 | 
  50 |   test('should navigate to destination detail', async ({ page }) => {
  51 |     await page.goto(`${BASE_URL}/destinations`);
  52 | 
  53 |     // Click on first destination card
  54 |     const firstCard = page.locator('.card, .destination-card').first();
  55 |     await firstCard.click();
  56 | 
  57 |     // Should navigate to detail page
  58 |     const currentUrl = page.url();
  59 |     expect(currentUrl).toMatch(/destinations|detail/);
  60 |   });
  61 | });
  62 | 
```