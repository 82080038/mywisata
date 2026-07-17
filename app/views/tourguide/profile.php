<?php include APP_ROOT . '/app/views/layouts/tourguide_header.php'; ?>

<div class="container-fluid py-4">
    <h2 class="mb-4">Profil Tour Guide</h2>
    
    <form method="POST" action="<?= View::url('tourguide/updateProfile') ?>" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= Middleware::csrfToken() ?>">
        
        <div class="row">
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <?php if ($guide && $guide['avatar']): ?>
                            <img src="<?= View::asset('uploads/avatars/' . $guide['avatar']) ?>" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle mb-3 bg-secondary d-flex align-items-center justify-content-center" style="width: 150px; height: 150px;">
                                <i class="fas fa-user text-white" style="font-size: 4rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="mb-3">
                            <label for="avatar" class="form-label">Upload Avatar</label>
                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                            <small class="text-muted">Max 2MB (JPG, PNG)</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Dasar</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= $guide ? View::e($guide['name']) : '' ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Nomor Telepon</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= $guide ? View::e($guide['phone']) : '' ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Bio / Deskripsi</label>
                            <textarea class="form-control" id="bio" name="bio" rows="4" maxlength="1000"><?= $guide ? View::e($guide['bio']) : '' ?></textarea>
                            <small class="text-muted">Maksimal 1000 karakter</small>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="license_number" class="form-label">Nomor Lisensi (Opsional)</label>
                                <input type="text" class="form-control" id="license_number" name="license_number" value="<?= $guide ? View::e($guide['license_number']) : '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="experience_years" class="form-label">Pengalaman (Tahun)</label>
                                <input type="number" class="form-control" id="experience_years" name="experience_years" value="<?= $guide ? $guide['experience_years'] : 0 ?>" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Harga</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hourly_rate" class="form-label">Tarif Per Jam (IDR)</label>
                                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" value="<?= $guide ? $guide['hourly_rate'] : 0 ?>" min="0" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="daily_rate" class="form-label">Tarif Per Hari (IDR)</label>
                                <input type="number" class="form-control" id="daily_rate" name="daily_rate" value="<?= $guide ? $guide['daily_rate'] : 0 ?>" min="0" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Lokasi</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="province_id" class="form-label">Provinsi</label>
                                <select class="form-select" id="province_id" name="province_id" required>
                                    <option value="">Pilih Provinsi</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="regency_id" class="form-label">Kabupaten/Kota</label>
                                <select class="form-select" id="regency_id" name="regency_id" required>
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="text" class="form-control" id="latitude" name="latitude" value="<?= $guide ? $guide['latitude'] : '' ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="text" class="form-control" id="longitude" name="longitude" value="<?= $guide ? $guide['longitude'] : '' ?>">
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="getLocation()">
                            <i class="fas fa-map-marker-alt me-1"></i>Dapatkan Lokasi Saya
                        </button>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="is_available" name="is_available" value="1" <?= ($guide && $guide['is_available']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_available">Saya tersedia untuk menerima booking</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Simpan Profil
                </button>
            </div>
        </div>
    </form>
</div>

<script src="<?= View::asset('assets/js/address-cascade.js') ?>"></script>
<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Lokasi berhasil didapatkan',
                timer: 1500,
                showConfirmButton: false
            });
        }, function(error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Gagal mendapatkan lokasi: ' + error.message,
                confirmButtonColor: '#0d6efd'
            });
        });
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Browser tidak mendukung geolocation',
            confirmButtonColor: '#0d6efd'
        });
    }
}
</script>

<?php include APP_ROOT . '/app/views/layouts/tourguide_footer.php'; ?>
