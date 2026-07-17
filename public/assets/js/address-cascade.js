/**
 * MyWisata Application - Address Cascading Dropdowns
 * 
 * Handles cascading dropdowns for Indonesian administrative divisions:
 * Province → Regency → District → Village
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-17
 */

class AddressCascade {
    constructor(options = {}) {
        this.provinceSelect = options.provinceSelect || '#province_id';
        this.regencySelect = options.regencySelect || '#regency_id';
        this.districtSelect = options.districtSelect || '#district_id';
        this.villageSelect = options.villageSelect || '#village_id';
        this.baseUrl = options.baseUrl || '/address';
        
        this.init();
    }
    
    init() {
        this.loadProvinces();
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        const provinceEl = document.querySelector(this.provinceSelect);
        const regencyEl = document.querySelector(this.regencySelect);
        const districtEl = document.querySelector(this.districtSelect);
        
        if (provinceEl) {
            provinceEl.addEventListener('change', (e) => {
                this.loadRegencies(e.target.value);
                this.clearSelect(this.regencySelect);
                this.clearSelect(this.districtSelect);
                this.clearSelect(this.villageSelect);
            });
        }
        
        if (regencyEl) {
            regencyEl.addEventListener('change', (e) => {
                this.loadDistricts(e.target.value);
                this.clearSelect(this.districtSelect);
                this.clearSelect(this.villageSelect);
            });
        }
        
        if (districtEl) {
            districtEl.addEventListener('change', (e) => {
                this.loadVillages(e.target.value);
                this.clearSelect(this.villageSelect);
            });
        }
    }
    
    async loadProvinces() {
        console.log('Address Cascade: Loading provinces...');
        try {
            const response = await fetch(`${this.baseUrl}/getProvinces`);
            console.log('Address Cascade: Response received', response.status);
            const data = await response.json();
            console.log('Address Cascade: Data received', data);
            
            if (data.status === 'success') {
                this.populateSelect(this.provinceSelect, data.data, 'Pilih Provinsi');
                console.log('Address Cascade: Provinces loaded successfully');
            } else {
                console.error('Address Cascade: API returned error', data);
            }
        } catch (error) {
            console.error('Address Cascade: Error loading provinces:', error);
        }
    }
    
    async loadRegencies(provinceId) {
        if (!provinceId) return;
        
        try {
            const response = await fetch(`${this.baseUrl}/getRegencies?province_id=${provinceId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                this.populateSelect(this.regencySelect, data.data, 'Pilih Kabupaten/Kota');
            }
        } catch (error) {
            console.error('Error loading regencies:', error);
        }
    }
    
    async loadDistricts(regencyId) {
        if (!regencyId) return;
        
        try {
            const response = await fetch(`${this.baseUrl}/getDistricts?regency_id=${regencyId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                this.populateSelect(this.districtSelect, data.data, 'Pilih Kecamatan');
            }
        } catch (error) {
            console.error('Error loading districts:', error);
        }
    }
    
    async loadVillages(districtId) {
        if (!districtId) return;
        
        try {
            const response = await fetch(`${this.baseUrl}/getVillages?district_id=${districtId}`);
            const data = await response.json();
            
            if (data.status === 'success') {
                this.populateSelect(this.villageSelect, data.data, 'Pilih Kelurahan/Desa');
            }
        } catch (error) {
            console.error('Error loading villages:', error);
        }
    }
    
    populateSelect(selector, data, placeholder) {
        const select = document.querySelector(selector);
        if (!select) return;
        
        select.innerHTML = `<option value="">${placeholder}</option>`;
        
        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item.id;
            option.textContent = item.name;
            select.appendChild(option);
        });
    }
    
    clearSelect(selector) {
        const select = document.querySelector(selector);
        if (select) {
            select.innerHTML = '<option value="">Pilih...</option>';
        }
    }
    
    // Method to set initial values (for edit forms)
    async setValues(provinceId, regencyId, districtId, villageId) {
        if (provinceId) {
            const provinceEl = document.querySelector(this.provinceSelect);
            if (provinceEl) provinceEl.value = provinceId;
            await this.loadRegencies(provinceId);
            
            if (regencyId) {
                const regencyEl = document.querySelector(this.regencySelect);
                if (regencyEl) regencyEl.value = regencyId;
                await this.loadDistricts(regencyId);
                
                if (districtId) {
                    const districtEl = document.querySelector(this.districtSelect);
                    if (districtEl) districtEl.value = districtId;
                    await this.loadVillages(districtId);
                    
                    if (villageId) {
                        const villageEl = document.querySelector(this.villageSelect);
                        if (villageEl) villageEl.value = villageId;
                    }
                }
            }
        }
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('Address Cascade: DOM Content Loaded');
    
    // Check if address cascade elements exist
    if (document.querySelector('#province_id') || document.querySelector('.address-cascade')) {
        console.log('Address Cascade: Elements found, initializing...');
        window.addressCascade = new AddressCascade();
        console.log('Address Cascade: Initialized successfully');
    } else {
        console.log('Address Cascade: No elements found');
    }
});
