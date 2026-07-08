# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: comprehensive.spec.ts >> Comprehensive Application Tests >> should have no JavaScript errors on homepage
- Location: tests/e2e/comprehensive.spec.ts:185:7

# Error details

```
Error: expect(received).toBe(expected) // Object.is equality

Expected: 0
Received: 1
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
  101 |     await page.goto(`${BASE_URL}/auth/register`);
  102 | 
  103 |     // Check if register form exists
  104 |     const registerForm = page.locator('form');
  105 |     await expect(registerForm).toBeVisible();
  106 | 
  107 |     // Check for required fields
  108 |     const nameInput = page.locator('input[name="name"]');
  109 |     const emailInput = page.locator('input[name="email"]');
  110 |     const passwordInput = page.locator('input[name="password"]');
  111 |     const confirmPasswordInput = page.locator('input[name="password_confirm"]');
  112 | 
  113 |     await expect(nameInput).toBeVisible();
  114 |     await expect(emailInput).toBeVisible();
  115 |     await expect(passwordInput).toBeVisible();
  116 |     await expect(confirmPasswordInput).toBeVisible();
  117 |   });
  118 | 
  119 |   test('should display destinations with data', async ({ page }) => {
  120 |     await page.goto(`${BASE_URL}/destinations`);
  121 | 
  122 |     // Wait for page to load
  123 |     await page.waitForLoadState('networkidle');
  124 | 
  125 |     // Check if destinations are displayed
  126 |     const destinationCards = page.locator('.destination-card, .card, .item');
  127 |     const count = await destinationCards.count();
  128 | 
  129 |     // Should have at least some destinations
  130 |     const countValue = await count;
  131 |     expect(countValue).toBeGreaterThan(0);
  132 |   });
  133 | 
  134 |   test('should handle search functionality', async ({ page }) => {
  135 |     await page.goto(`${BASE_URL}`);
  136 | 
  137 |     // Find search input
  138 |     const searchInput = page.locator('input[type="search"], input[name="search"], input[placeholder*="cari"], input[placeholder*="search"]');
  139 |     const count = await searchInput.count();
  140 | 
  141 |     if (count > 0) {
  142 |       await searchInput.first().fill('Bali');
  143 |       await page.keyboard.press('Enter');
  144 | 
  145 |       // Wait for search results
  146 |       await page.waitForLoadState('networkidle');
  147 | 
  148 |       // Check that we're on search page
  149 |       const currentUrl = page.url();
  150 |       expect(currentUrl).toMatch(/search/);
  151 |     }
  152 |   });
  153 | 
  154 |   test('should handle AJAX requests properly', async ({ page }) => {
  155 |     await page.goto(`${BASE_URL}/destinations`);
  156 | 
  157 |     // Monitor network requests
  158 |     const failedRequests: string[] = [];
  159 |     page.on('requestfailed', request => {
  160 |       failedRequests.push(request.url());
  161 |     });
  162 | 
  163 |     await page.waitForLoadState('networkidle');
  164 | 
  165 |     expect(failedRequests.length).toBe(0);
  166 |   });
  167 | 
  168 |   test('should be responsive on different viewports', async ({ page }) => {
  169 |     const viewports = [
  170 |       { width: 375, height: 667 }, // Mobile
  171 |       { width: 768, height: 1024 }, // Tablet
  172 |       { width: 1920, height: 1080 }, // Desktop
  173 |     ];
  174 | 
  175 |     for (const viewport of viewports) {
  176 |       await page.setViewportSize(viewport);
  177 |       const response = await page.goto(BASE_URL);
  178 |       if (response) {
  179 |         expect(response.status()).toBeLessThan(400);
  180 |       }
  181 |       await page.waitForLoadState('networkidle');
  182 |     }
  183 |   });
  184 | 
  185 |   test('should have no JavaScript errors on homepage', async ({ page }) => {
  186 |     const jsErrors: string[] = [];
  187 |     page.on('pageerror', error => {
  188 |       const errorMsg = error.toString();
  189 |       console.log('JavaScript Error:', errorMsg);
  190 |       jsErrors.push(errorMsg);
  191 |     });
  192 | 
  193 |     await page.goto(BASE_URL);
  194 |     await page.waitForLoadState('networkidle');
  195 | 
  196 |     // Log errors for debugging
  197 |     if (jsErrors.length > 0) {
  198 |       console.log('JavaScript Errors found:', jsErrors);
  199 |     }
  200 | 
> 201 |     expect(jsErrors.length).toBe(0);
      |                             ^ Error: expect(received).toBe(expected) // Object.is equality
  202 |   });
  203 | 
  204 |   test('should handle rate limiting on login', async ({ page }) => {
  205 |     await page.goto(`${BASE_URL}/auth/login`);
  206 | 
  207 |     // Attempt multiple login attempts
  208 |     for (let i = 0; i < 6; i++) {
  209 |       const emailInput = page.locator('input[name="email"]');
  210 |       const passwordInput = page.locator('input[name="password"]');
  211 |       const submitButton = page.locator('button[type="submit"], input[type="submit"]');
  212 | 
  213 |       await emailInput.fill('test@example.com');
  214 |       await passwordInput.fill('wrongpassword');
  215 | 
  216 |       if (await submitButton.count() > 0) {
  217 |         await submitButton.first().click();
  218 |         await page.waitForTimeout(500);
  219 |       }
  220 |     }
  221 | 
  222 |     // Check if rate limit message appears
  223 |     const rateLimitMessage = page.locator('text=/terlalu banyak|rate limit/').first();
  224 |     // Rate limiting should be triggered
  225 |   });
  226 | 
  227 |   test('should have proper CSRF protection', async ({ page }) => {
  228 |     await page.goto(`${BASE_URL}/auth/login`);
  229 | 
  230 |     // Get CSRF token
  231 |     const csrfToken = await page.locator('input[name="csrf_token"]').inputValue();
  232 |     expect(csrfToken).toBeTruthy();
  233 |     expect(csrfToken.length).toBeGreaterThan(0);
  234 |   });
  235 | 
  236 |   test('should handle 404 pages gracefully', async ({ page }) => {
  237 |     const response = await page.goto(`${BASE_URL}/non-existent-page`);
  238 |     if (response) {
  239 |       expect(response.status()).toBe(404);
  240 |     }
  241 |   });
  242 | 
  243 |   test('should handle 500 errors gracefully', async ({ page }) => {
  244 |     // Try to access a route that might cause server error
  245 |     const response = await page.goto(`${BASE_URL}/admin/dashboard`);
  246 | 
  247 |     // Should redirect to login (401/403) or show error page
  248 |     if (response) {
  249 |       expect(response.status()).toBeLessThan(500);
  250 |     }
  251 |   });
  252 | });
  253 | 
  254 | test.describe('Performance Tests', () => {
  255 |   test('should load homepage within reasonable time', async ({ page }) => {
  256 |     const startTime = Date.now();
  257 |     await page.goto(BASE_URL);
  258 |     await page.waitForLoadState('networkidle');
  259 |     const loadTime = Date.now() - startTime;
  260 | 
  261 |     // Page should load within 5 seconds
  262 |     expect(loadTime).toBeLessThan(5000);
  263 |   });
  264 | 
  265 |   test('should have no memory leaks in navigation', async ({ page }) => {
  266 |     for (let i = 0; i < 10; i++) {
  267 |       await page.goto(BASE_URL);
  268 |       await page.waitForLoadState('networkidle');
  269 |       await page.goto(`${BASE_URL}/destinations`);
  270 |       await page.waitForLoadState('networkidle');
  271 |     }
  272 | 
  273 |     // If we reach here without crashing, no obvious memory leaks
  274 |     expect(true).toBe(true);
  275 |   });
  276 | });
  277 | 
```