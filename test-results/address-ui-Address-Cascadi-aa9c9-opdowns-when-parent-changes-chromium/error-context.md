# Instructions

- Following Playwright test failed.
- Explain why, be concise, respect Playwright best practices.
- Provide a snippet of code with the fix, if possible.

# Test info

- Name: address-ui.spec.ts >> Address Cascading Dropdowns UI Interaction >> should clear dependent dropdowns when parent changes
- Location: tests/e2e/address-ui.spec.ts:151:7

# Error details

```
Test timeout of 30000ms exceeded.
```

```
Error: locator.selectOption: Test timeout of 30000ms exceeded.
Call log:
  - waiting for locator('#province_id')
    - locator resolved to <select id="province_id" name="province_id" class="form-select">…</select>
  - attempting select option action
    2 × waiting for element to be visible and enabled
      - did not find some options
    - retrying select option action
    - waiting 20ms
    2 × waiting for element to be visible and enabled
      - did not find some options
    - retrying select option action
      - waiting 100ms
    44 × waiting for element to be visible and enabled
       - did not find some options
     - retrying select option action
       - waiting 500ms

```

# Page snapshot

```yaml
- generic [ref=e5]:
  - heading "Address Cascade Dropdowns Test" [level=4] [ref=e7]
  - generic [ref=e8]:
    - generic [ref=e9]:
      - generic [ref=e10]:
        - generic [ref=e11]: Provinsi
        - combobox "Provinsi" [ref=e12]:
          - option "Pilih Provinsi"
          - option "SUMATERA UTARA" [selected]
      - generic [ref=e13]:
        - generic [ref=e14]: Kabupaten/Kota
        - combobox "Kabupaten/Kota" [ref=e15]:
          - option "Pilih Kabupaten/Kota"
          - option "KABUPATEN ASAHAN" [selected]
          - option "KABUPATEN BATU BARA"
          - option "KABUPATEN DAIRI"
          - option "KABUPATEN DELI SERDANG"
          - option "KABUPATEN HUMBANG HASUNDUTAN"
          - option "KABUPATEN KARO"
          - option "KABUPATEN LABUHAN BATU"
          - option "KABUPATEN LABUHAN BATU SELATAN"
          - option "KABUPATEN LABUHAN BATU UTARA"
          - option "KABUPATEN LANGKAT"
          - option "KABUPATEN MANDAILING NATAL"
          - option "KABUPATEN NIAS"
          - option "KABUPATEN NIAS BARAT"
          - option "KABUPATEN NIAS SELATAN"
          - option "KABUPATEN NIAS UTARA"
          - option "KABUPATEN PADANG LAWAS"
          - option "KABUPATEN PADANG LAWAS UTARA"
          - option "KABUPATEN PAKPAK BHARAT"
          - option "KABUPATEN SAMOSIR"
          - option "KABUPATEN SERDANG BEDAGAI"
          - option "KABUPATEN SIMALUNGUN"
          - option "KABUPATEN TAPANULI SELATAN"
          - option "KABUPATEN TAPANULI TENGAH"
          - option "KABUPATEN TAPANULI UTARA"
          - option "KABUPATEN TOBA SAMOSIR"
          - option "KOTA BINJAI"
          - option "KOTA GUNUNGSITOLI"
          - option "KOTA MEDAN"
          - option "KOTA PADANGSIDIMPUAN"
          - option "KOTA PEMATANG SIANTAR"
          - option "KOTA SIBOLGA"
          - option "KOTA TANJUNG BALAI"
          - option "KOTA TEBING TINGGI"
    - generic [ref=e16]:
      - generic [ref=e17]:
        - generic [ref=e18]: Kecamatan
        - combobox "Kecamatan" [ref=e19]:
          - option "Pilih Kecamatan"
          - option "AEK KUASAN" [selected]
          - option "AEK LEDONG"
          - option "AEK SONGSONGAN"
          - option "AIR BATU"
          - option "AIR JOMAN"
          - option "BANDAR PASIR MANDOGE"
          - option "BANDAR PULAU"
          - option "BUNTU PANE"
          - option "KISARAN BARAT"
          - option "KISARAN TIMUR"
          - option "MERANTI"
          - option "PULAU RAKYAT"
          - option "PULO BANDRING"
          - option "RAHUNING"
          - option "RAWANG PANCA ARGA"
          - option "SEI DADAP"
          - option "SEI KEPAYANG"
          - option "SEI KEPAYANG BARAT"
          - option "SEI KEPAYANG TIMUR"
          - option "SETIA JANJI"
          - option "SILAU LAUT"
          - option "SIMPANG EMPAT"
          - option "TANJUNG BALAI"
          - option "TELUK DALAM"
          - option "TINGGI RAJA"
      - generic [ref=e20]:
        - generic [ref=e21]: Kelurahan/Desa
        - combobox "Kelurahan/Desa" [ref=e22]:
          - option "Pilih Kelurahan/Desa" [selected]
          - option "AEK LOBA"
          - option "AEK LOBA AFDELING I"
          - option "AEK LOBA PEKAN"
          - option "ALANG BONBON"
          - option "LOBU JIUR"
          - option "RAWA SARI"
          - option "SENGON SARI"
    - generic [ref=e23]:
      - generic [ref=e24]: 
      - text: This is a test page for address cascading dropdowns functionality.
```

# Test source

```ts
  100 |     // Select regency
  101 |     const regencySelect = page.locator('#regency_id');
  102 |     await regencySelect.selectOption({ index: 1 });
  103 |     await page.waitForTimeout(2000);
  104 |     
  105 |     // Check district dropdown
  106 |     const districtSelect = page.locator('#district_id');
  107 |     await expect(districtSelect).toBeVisible();
  108 |     
  109 |     // Get options in district dropdown
  110 |     const options = await districtSelect.locator('option').all();
  111 |     const optionCount = options.length;
  112 |     
  113 |     // Should have districts loaded
  114 |     expect(optionCount).toBeGreaterThan(1);
  115 |   });
  116 | 
  117 |   test('should load villages when district is selected', async ({ page }) => {
  118 |     await page.goto(`${BASE_URL}/test/address`);
  119 |     
  120 |     // Wait for page to load
  121 |     await page.waitForLoadState('networkidle');
  122 |     await page.waitForTimeout(2000);
  123 |     
  124 |     // Select province
  125 |     const provinceSelect = page.locator('#province_id');
  126 |     await provinceSelect.selectOption({ index: 1 });
  127 |     await page.waitForTimeout(2000);
  128 |     
  129 |     // Select regency
  130 |     const regencySelect = page.locator('#regency_id');
  131 |     await regencySelect.selectOption({ index: 1 });
  132 |     await page.waitForTimeout(2000);
  133 |     
  134 |     // Select district
  135 |     const districtSelect = page.locator('#district_id');
  136 |     await districtSelect.selectOption({ index: 1 });
  137 |     await page.waitForTimeout(2000);
  138 |     
  139 |     // Check village dropdown
  140 |     const villageSelect = page.locator('#village_id');
  141 |     await expect(villageSelect).toBeVisible();
  142 |     
  143 |     // Get options in village dropdown
  144 |     const options = await villageSelect.locator('option').all();
  145 |     const optionCount = options.length;
  146 |     
  147 |     // Should have villages loaded
  148 |     expect(optionCount).toBeGreaterThan(1);
  149 |   });
  150 | 
  151 |   test('should clear dependent dropdowns when parent changes', async ({ page }) => {
  152 |     await page.goto(`${BASE_URL}/test/address`);
  153 |     
  154 |     // Wait for page to load
  155 |     await page.waitForLoadState('networkidle');
  156 |     await page.waitForTimeout(3000);
  157 |     
  158 |     // Get initial province options count
  159 |     const provinceSelect = page.locator('#province_id');
  160 |     const provinceOptions = await provinceSelect.locator('option').all();
  161 |     const provinceCount = provinceOptions.length;
  162 |     
  163 |     // Only run this test if we have at least 2 provinces to select from
  164 |     if (provinceCount < 2) {
  165 |       console.log('Skipping test - not enough provinces to test clearing');
  166 |       return;
  167 |     }
  168 |     
  169 |     // Select first province
  170 |     await provinceSelect.selectOption({ index: 1 });
  171 |     await page.waitForTimeout(2000);
  172 |     
  173 |     // Select regency
  174 |     const regencySelect = page.locator('#regency_id');
  175 |     const regencyOptions = await regencySelect.locator('option').all();
  176 |     const regencyCount = regencyOptions.length;
  177 |     
  178 |     if (regencyCount < 2) {
  179 |       console.log('Skipping test - not enough regencies to test clearing');
  180 |       return;
  181 |     }
  182 |     
  183 |     await regencySelect.selectOption({ index: 1 });
  184 |     await page.waitForTimeout(2000);
  185 |     
  186 |     // Select district
  187 |     const districtSelect = page.locator('#district_id');
  188 |     const districtOptions = await districtSelect.locator('option').all();
  189 |     const districtCount = districtOptions.length;
  190 |     
  191 |     if (districtCount < 2) {
  192 |       console.log('Skipping test - not enough districts to test clearing');
  193 |       return;
  194 |     }
  195 |     
  196 |     await districtSelect.selectOption({ index: 1 });
  197 |     await page.waitForTimeout(2000);
  198 |     
  199 |     // Change province to second option - should clear all dependent dropdowns
> 200 |     await provinceSelect.selectOption({ index: 2 });
      |                          ^ Error: locator.selectOption: Test timeout of 30000ms exceeded.
  201 |     await page.waitForTimeout(2000);
  202 |     
  203 |     // Check that regency is reset
  204 |     const regencyValue = await regencySelect.inputValue();
  205 |     expect(regencyValue).toBe('');
  206 |   });
  207 | 
  208 |   test('should have address cascade JavaScript loaded', async ({ page }) => {
  209 |     await page.goto(`${BASE_URL}/test/address`);
  210 |     
  211 |     // Wait for page to load
  212 |     await page.waitForLoadState('networkidle');
  213 |     
  214 |     // Check if address-cascade.js is loaded
  215 |     const scriptLoaded = await page.evaluate(() => {
  216 |       return typeof (window as any).addressCascade !== 'undefined';
  217 |     });
  218 |     
  219 |     expect(scriptLoaded).toBe(true);
  220 |   });
  221 | });
  222 | 
```