<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Address Cascade Test - MyWisata</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Address Cascade Dropdowns Test</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="province_id" class="form-label">Provinsi</label>
                                <select class="form-select" id="province_id" name="province_id">
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="regency_id" class="form-label">Kabupaten/Kota</label>
                                <select class="form-select" id="regency_id" name="regency_id">
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="district_id" class="form-label">Kecamatan</label>
                                <select class="form-select" id="district_id" name="district_id">
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="village_id" class="form-label">Kelurahan/Desa</label>
                                <select class="form-select" id="village_id" name="village_id">
                                    <option value="">Pilih Kelurahan/Desa</option>
                                </select>
                            </div>
                        </div>
                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle me-2"></i>
                            This is a test page for address cascading dropdowns functionality.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set APP_URL for testing
        window.APP_URL = 'http://localhost/mywisata';
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Inline address cascade logic for testing
        class AddressCascade {
            constructor(options = {}) {
                this.provinceSelect = options.provinceSelect || '#province_id';
                this.regencySelect = options.regencySelect || '#regency_id';
                this.districtSelect = options.districtSelect || '#district_id';
                this.villageSelect = options.villageSelect || '#village_id';
                this.baseUrl = options.baseUrl || (window.APP_URL || '') + '/address';
                
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
                try {
                    const response = await fetch(`${this.baseUrl}/getProvinces`);
                    const data = await response.json();
                    
                    if (data.status === 'success') {
                        this.populateSelect(this.provinceSelect, data.data, 'Pilih Provinsi');
                    }
                } catch (error) {
                    console.error('Error loading provinces:', error);
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
        }
        
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            if (document.querySelector('#province_id')) {
                window.addressCascade = new AddressCascade();
            }
        });
    </script>
</body>
</html>
