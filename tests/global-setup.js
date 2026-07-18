// Global setup for Playwright tests
const fs = require('fs');
const path = require('path');

async function globalSetup(config) {
  console.log('Starting global setup...');
  
  // Create test results directory if it doesn't exist
  const testResultsDir = path.join(__dirname, '..', 'test-results');
  if (!fs.existsSync(testResultsDir)) {
    fs.mkdirSync(testResultsDir, { recursive: true });
  }
  
  // Create screenshots directory
  const screenshotsDir = path.join(testResultsDir, 'screenshots');
  if (!fs.existsSync(screenshotsDir)) {
    fs.mkdirSync(screenshotsDir, { recursive: true });
  }
  
  console.log('Global setup completed');
}

module.exports = globalSetup;
