import { test, expect } from '@playwright/test';

test.describe('Address Cascading Dropdowns', () => {
  test('should load provinces on page load', async ({ page }) => {
    await page.goto('http://localhost:8080/address/getProvinces');
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('name');
  });

  test('should load regencies by province ID', async ({ page }) => {
    await page.goto('http://localhost:8080/address/getRegencies?province_id=3');
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('name');
  });

  test('should load districts by regency ID', async ({ page }) => {
    await page.goto('http://localhost:8080/address/getDistricts?regency_id=31');
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('name');
  });

  test('should load villages by district ID', async ({ page }) => {
    await page.goto('http://localhost:8080/address/getVillages?district_id=402');
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('name');
  });

  test('should handle missing province ID gracefully', async ({ page }) => {
    await page.goto('http://localhost:8080/address/getRegencies');
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('error');
    expect(response.message).toContain('Province ID is required');
  });
});
