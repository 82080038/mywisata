import { test, expect } from '@playwright/test';

const BASE_URL = 'http://localhost/mywisata';

test.describe('Address Cascading Dropdowns UI', () => {
  test('should load provinces API endpoint', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getProvinces`);
    
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
    await page.goto(`${BASE_URL}/?url=address/getRegencies&province_id=3`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
  });

  test('should load districts by regency ID', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getDistricts&regency_id=31`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
  });

  test('should load villages by district ID', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getVillages&district_id=402`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data).toBeInstanceOf(Array);
    expect(response.data.length).toBeGreaterThan(0);
  });

  test('should handle missing parameters gracefully', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getRegencies`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('error');
    expect(response.message).toContain('Province ID is required');
  });

  test('should return correct province data structure', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getProvinces`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('code');
    expect(response.data[0]).toHaveProperty('name');
  });

  test('should return correct regency data structure', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getRegencies&province_id=3`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('code');
    expect(response.data[0]).toHaveProperty('name');
    expect(response.data[0]).toHaveProperty('postal_code');
  });

  test('should return correct district data structure', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getDistricts&regency_id=31`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('code');
    expect(response.data[0]).toHaveProperty('name');
  });

  test('should return correct village data structure', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getVillages&district_id=402`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response.data[0]).toHaveProperty('id');
    expect(response.data[0]).toHaveProperty('code');
    expect(response.data[0]).toHaveProperty('name');
    expect(response.data[0]).toHaveProperty('postal_code');
  });

  test('should include count in response', async ({ page }) => {
    await page.goto(`${BASE_URL}/?url=address/getProvinces`);
    
    const response = await page.evaluate(() => {
      return JSON.parse(document.body.innerText);
    });
    
    expect(response.status).toBe('success');
    expect(response).toHaveProperty('count');
    expect(response.count).toBe(response.data.length);
  });
});
