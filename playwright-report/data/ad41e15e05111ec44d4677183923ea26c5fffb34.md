# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: destinations.spec.ts >> Destinations Tests >> should have filter options
- Location: tests/e2e/destinations.spec.ts:39:7

# Error details

```
Error: expect(received).toBeGreaterThan(expected)

Expected: > 0
Received:   0
```

# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - navigation [ref=e2]:
    - generic [ref=e3]:
      - link "MyWisata" [ref=e4] [cursor=pointer]:
        - /url: http://localhost/mywisata/
        - text: MyWisata
      - button [ref=e5]
      - generic [ref=e6]:
        - list [ref=e7]:
          - listitem [ref=e8]:
            - link "Beranda" [ref=e9] [cursor=pointer]:
              - /url: http://localhost/mywisata/
          - listitem [ref=e10]:
            - link "Tentang" [ref=e11] [cursor=pointer]:
              - /url: http://localhost/mywisata/home/about
          - listitem [ref=e12]:
            - link "Kontak" [ref=e13] [cursor=pointer]:
              - /url: http://localhost/mywisata/home/contact
        - list [ref=e14]:
          - listitem [ref=e15]:
            - link "Masuk" [ref=e16] [cursor=pointer]:
              - /url: http://localhost/mywisata/auth/login
              - text: Masuk
          - listitem [ref=e17]:
            - link "Daftar" [ref=e18] [cursor=pointer]:
              - /url: http://localhost/mywisata/auth/register
              - text: Daftar
  - main [ref=e19]:
    - generic [ref=e21]:
      - heading "MyWisata - Platform Marketplace Pariwisata" [level=1] [ref=e22]
      - paragraph [ref=e23]: Temukan tour guide profesional, destinasi wisata, hotel, restoran, dan event budaya di Indonesia.
      - generic [ref=e24]:
        - generic [ref=e27]:
          - heading "Tour Guide" [level=5] [ref=e28]
          - paragraph [ref=e29]: Temukan pemandu wisata profesional untuk perjalanan Anda
        - generic [ref=e32]:
          - heading "Destinasi" [level=5] [ref=e33]
          - paragraph [ref=e34]: Jelajahi destinasi wisata terbaik di Indonesia
        - generic [ref=e37]:
          - heading "Tiket" [level=5] [ref=e38]
          - paragraph [ref=e39]: Beli tiket wisata dengan mudah dan cepat
      - generic [ref=e40]:
        - generic [ref=e43]:
          - heading "Hotel" [level=5] [ref=e44]
          - paragraph [ref=e45]: Booking hotel dan homestay dengan harga terbaik
        - generic [ref=e48]:
          - heading "Restoran" [level=5] [ref=e49]
          - paragraph [ref=e50]: Temukan kuliner lokal dan restoran terbaik
        - generic [ref=e53]:
          - heading "Event" [level=5] [ref=e54]
          - paragraph [ref=e55]: Daftar event budaya dan festival menarik
  - contentinfo [ref=e56]:
    - generic [ref=e57]:
      - generic [ref=e58]:
        - generic [ref=e59]:
          - heading "MyWisata" [level=5] [ref=e60]
          - paragraph [ref=e61]: Platform marketplace untuk layanan pariwisata di Indonesia.
        - generic [ref=e62]:
          - heading "Tautan" [level=5] [ref=e63]
          - list [ref=e64]:
            - listitem [ref=e65]:
              - link "Beranda" [ref=e66] [cursor=pointer]:
                - /url: http://localhost/mywisata/
            - listitem [ref=e67]:
              - link "Tentang" [ref=e68] [cursor=pointer]:
                - /url: http://localhost/mywisata/home/about
            - listitem [ref=e69]:
              - link "Kontak" [ref=e70] [cursor=pointer]:
                - /url: http://localhost/mywisata/home/contact
        - generic [ref=e71]:
          - heading "Kontak" [level=5] [ref=e72]
          - paragraph [ref=e73]: admin@mywisata.com
          - paragraph [ref=e74]: +62 812 3456 7890
      - separator [ref=e75]
      - paragraph [ref=e77]: © 2026 MyWisata Application. All rights reserved.
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
  10 |     await expect(page).toHaveTitle(/Destinasi|Destinations/);
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
> 47 |     expect(filterCount).toBeGreaterThan(0);
     |                         ^ Error: expect(received).toBeGreaterThan(expected)
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