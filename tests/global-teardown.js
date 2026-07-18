// Global teardown for Playwright tests
const fs = require('fs');
const path = require('path');

async function globalTeardown(config) {
  console.log('Starting global teardown...');
  
  // Read test results
  const testResultsPath = path.join(__dirname, '..', 'test-results.json');
  if (fs.existsSync(testResultsPath)) {
    const testResults = JSON.parse(fs.readFileSync(testResultsPath, 'utf8'));
    console.log(`Test results: ${testResults.stats?.expected} total, ${testResults.stats?.passed} passed, ${testResults.stats?.failed} failed`);
  }
  
  console.log('Global teardown completed');
}

module.exports = globalTeardown;
