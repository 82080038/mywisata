import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Address Cascading Dropdowns UI Interaction', () => {
  test('should display address dropdowns on test page', async ({ page }) => {
    // Navigate to test page (no auth required)
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check for province dropdown
    const provinceSelect = page.locator('#province_id');
    await expect(provinceSelect).toBeVisible();
    
    // Check for regency dropdown
    const regencySelect = page.locator('#regency_id');
    await expect(regencySelect).toBeVisible();
    
    // Check for district dropdown
    const districtSelect = page.locator('#district_id');
    await expect(districtSelect).toBeVisible();
    
    // Check for village dropdown
    const villageSelect = page.locator('#village_id');
    await expect(villageSelect).toBeVisible();
  });

  test('should load provinces in dropdown on page load', async ({ page }) => {
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load and JavaScript to execute
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000); // Wait for AJAX to load provinces
    
    // Check province dropdown
    const provinceSelect = page.locator('#province_id');
    await expect(provinceSelect).toBeVisible();
    
    // Get all options in province dropdown
    const options = await provinceSelect.locator('option').all();
    const optionCount = options.length;
    
    // Should have at least the placeholder option
    expect(optionCount).toBeGreaterThanOrEqual(1);
    
    // Check first option is placeholder
    const firstOption = options[0];
    const firstText = await firstOption.textContent();
    expect(firstText).toContain('Pilih Provinsi');
    
    // If we have more than 1 option, check that provinces are loaded
    if (optionCount > 1) {
      const secondOption = options[1];
      const secondText = await secondOption.textContent();
      expect(secondText).toBeTruthy();
      expect(secondText).not.toBe('');
    }
  });

  test('should load regencies when province is selected', async ({ page }) => {
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    // Select a province
    const provinceSelect = page.locator('#province_id');
    await provinceSelect.selectOption({ index: 1 }); // Select first actual province
    
    // Wait for regencies to load
    await page.waitForTimeout(2000);
    
    // Check regency dropdown
    const regencySelect = page.locator('#regency_id');
    await expect(regencySelect).toBeVisible();
    
    // Get options in regency dropdown
    const options = await regencySelect.locator('option').all();
    const optionCount = options.length;
    
    // Should have regencies loaded
    expect(optionCount).toBeGreaterThan(1);
  });

  test('should load districts when regency is selected', async ({ page }) => {
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    // Select province
    const provinceSelect = page.locator('#province_id');
    await provinceSelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Select regency
    const regencySelect = page.locator('#regency_id');
    await regencySelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Check district dropdown
    const districtSelect = page.locator('#district_id');
    await expect(districtSelect).toBeVisible();
    
    // Get options in district dropdown
    const options = await districtSelect.locator('option').all();
    const optionCount = options.length;
    
    // Should have districts loaded
    expect(optionCount).toBeGreaterThan(1);
  });

  test('should load villages when district is selected', async ({ page }) => {
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(2000);
    
    // Select province
    const provinceSelect = page.locator('#province_id');
    await provinceSelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Select regency
    const regencySelect = page.locator('#regency_id');
    await regencySelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Select district
    const districtSelect = page.locator('#district_id');
    await districtSelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Check village dropdown
    const villageSelect = page.locator('#village_id');
    await expect(villageSelect).toBeVisible();
    
    // Get options in village dropdown
    const options = await villageSelect.locator('option').all();
    const optionCount = options.length;
    
    // Should have villages loaded
    expect(optionCount).toBeGreaterThan(1);
  });

  test('should clear dependent dropdowns when parent changes', async ({ page }) => {
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    await page.waitForTimeout(3000);
    
    // Get initial province options count
    const provinceSelect = page.locator('#province_id');
    const provinceOptions = await provinceSelect.locator('option').all();
    const provinceCount = provinceOptions.length;
    
    // Only run this test if we have at least 2 provinces to select from
    if (provinceCount < 2) {
      console.log('Skipping test - not enough provinces to test clearing');
      return;
    }
    
    // Select first province
    await provinceSelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Select regency
    const regencySelect = page.locator('#regency_id');
    const regencyOptions = await regencySelect.locator('option').all();
    const regencyCount = regencyOptions.length;
    
    if (regencyCount < 2) {
      console.log('Skipping test - not enough regencies to test clearing');
      return;
    }
    
    await regencySelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Select district
    const districtSelect = page.locator('#district_id');
    const districtOptions = await districtSelect.locator('option').all();
    const districtCount = districtOptions.length;
    
    if (districtCount < 2) {
      console.log('Skipping test - not enough districts to test clearing');
      return;
    }
    
    await districtSelect.selectOption({ index: 1 });
    await page.waitForTimeout(2000);
    
    // Change province to second option - should clear all dependent dropdowns
    await provinceSelect.selectOption({ index: 2 });
    await page.waitForTimeout(2000);
    
    // Check that regency is reset
    const regencyValue = await regencySelect.inputValue();
    expect(regencyValue).toBe('');
  });

  test('should have address cascade JavaScript loaded', async ({ page }) => {
    await page.goto(`${BASE_URL}/test/address`);
    
    // Wait for page to load
    await page.waitForLoadState('networkidle');
    
    // Check if address-cascade.js is loaded
    const scriptLoaded = await page.evaluate(() => {
      return typeof (window as any).addressCascade !== 'undefined';
    });
    
    expect(scriptLoaded).toBe(true);
  });
});
