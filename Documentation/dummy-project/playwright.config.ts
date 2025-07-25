import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  // Look for test files in the "tests" directory, relative to this configuration file.
  testDir: 'tests',

  // Run all tests in parallel.
  fullyParallel: true,

  // Fail the build on CI if you accidentally left test.only in the source code.
  forbidOnly: !!process.env.CI,

  // Retry on CI only.
  retries: process.env.CI ? 1 : 0,

  // Opt out of parallel tests on CI.
  workers: process.env.CI ? 2 : undefined,

  // Reporter to use
  reporter:  [
    ['list'],
    [
      'html',
      {
        // outputFolder: 'playwright-report',
        // host: '0.0.0.0',
        // port: 8080,
        // open: 'on-failure',
      },
    ],
    [
      'junit',
      {
        outputFile: 'test-results/junit.xml',
        stripANSIControlSequences: true,
      },
    ],
  ],

  use: {
    // Base URL to use in actions like `await page.goto('/')`.
    baseURL: process.env.STORYBOOK_ENDPOINT || 'http://localhost:8080',

    // Collect trace when retrying the failed test.
    trace: 'retain-on-failure',
  },
  // Configure projects for major browsers.
  projects: [
    {
      name: 'chromium',
      use: { ...devices['Desktop Chrome'] },
    },
  ],
  // Run your local dev server before starting the tests.
  webServer: {
    command: 'npm run storybook',
    url: 'http://localhost:8080',
    reuseExistingServer: !process.env.CI,
  },
});
