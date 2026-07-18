import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: false, // Run sequentially for headed mode
  forbidOnly: !!process.env.CI,
  retries: 1, // Retry once on failure for robustness
  workers: 1, // Single worker for headed mode
  reporter: [
    ['html', { open: 'never' }], // Don't auto-open report, continue execution
    ['list'],
    ['json', { outputFile: 'test-results.json' }]
  ],
  use: {
    baseURL: 'http://localhost/mywisata',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    headless: false, // Run in headed mode
    viewport: { width: 1280, height: 720 },
    ignoreHTTPSErrors: true,
    actionTimeout: 30000, // 30 second action timeout
    navigationTimeout: 30000, // 30 second navigation timeout
  },

  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        launchOptions: {
          args: ['--start-maximized', '--no-sandbox', '--disable-setuid-sandbox']
        }
      },
    },
  ],

  // No webServer - XAMPP Apache is already running
  timeout: 120000, // 120 second timeout per test
  globalSetup: require.resolve('./tests/global-setup.js'),
  globalTeardown: require.resolve('./tests/global-teardown.js'),
});
