import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: false, // Run sequentially for headed mode
  forbidOnly: !!process.env.CI,
  retries: 0, // No retries for manual testing
  workers: 1, // Single worker for headed mode
  reporter: ['html', 'list'],
  use: {
    baseURL: 'http://localhost/mywisata',
    trace: 'retain-on-failure',
    screenshot: 'only-on-failure',
    video: 'retain-on-failure',
    headless: false, // Run in headed mode
    viewport: { width: 1280, height: 720 },
    ignoreHTTPSErrors: true,
  },

  projects: [
    {
      name: 'chromium',
      use: { 
        ...devices['Desktop Chrome'],
        launchOptions: {
          args: ['--start-maximized']
        }
      },
    },
  ],

  // No webServer - XAMPP Apache is already running
  timeout: 60000, // 60 second timeout per test
});
