<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= View::url() ?>">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="<?= View::url('dashboard') ?>">Dashboard</a></li>
                    <li class="breadcrumb-item active">Preferensi Makanan</li>
                </ol>
            </nav>

            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning bg-opacity-10">
                    <h4 class="mb-0"><i class="fas fa-utensils me-2 text-warning"></i>Preferensi & Alergi Makanan</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Informasi ini digunakan untuk:
                        <ul class="mb-0 mt-1">
                            <li>Menampilkan rekomendasi restoran yang sesuai</li>
                            <li>Memberi tahu pemandu wisata & hotel tentang kebutuhan makanan Anda</li>
                            <li>Memfilter menu yang aman untuk Anda konsumsi</li>
                        </ul>
                    </div>

                    <form id="foodPrefsForm">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">

                        <!-- Allergies -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Alergi Makanan</h5>
                            <p class="text-muted small">Pilih jenis makanan yang Anda alergi. Ini akan ditampilkan saat booking untuk keselamatan Anda.</p>
                            <div class="row">
                                <?php foreach ($allergyOptions as $allergy): ?>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="allergies[]" value="<?= $allergy ?>" id="allergy_<?= $allergy ?>"
                                            <?= in_array($allergy, $prefs['allergies']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="allergy_<?= $allergy ?>">
                                            <?= User::allergyLabel($allergy) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Dietary Preferences -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-leaf text-success me-2"></i>Preferensi Diet</h5>
                            <p class="text-muted small">Pilih preferensi diet Anda untuk rekomendasi yang lebih sesuai.</p>
                            <div class="row">
                                <?php foreach ($preferenceOptions as $pref): ?>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="preferences[]" value="<?= $pref ?>" id="pref_<?= $pref ?>"
                                            <?= in_array($pref, $prefs['preferences']) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="pref_<?= $pref ?>">
                                            <?= User::preferenceLabel($pref) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <hr>

                        <!-- Notes -->
                        <div class="mb-4">
                            <h5 class="mb-3"><i class="fas fa-sticky-note text-secondary me-2"></i>Catatan Tambahan</h5>
                            <p class="text-muted small">Tambahkan informasi lain yang penting, misalnya tingkat kepedasan, intoleransi tertentu, dll.</p>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Misal: Saya tidak bisa makan makanan terlalu pedas, intoleransi fruktosa, dll."><?= View::e($prefs['notes']) ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Simpan Preferensi
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Current Preferences Summary -->
            <?php if (!empty($prefs['allergies']) || !empty($prefs['preferences'])): ?>
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Preferensi Saat Ini</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($prefs['allergies'])): ?>
                    <div class="mb-3">
                        <strong class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Alergi:</strong>
                        <?php foreach ($prefs['allergies'] as $a): ?>
                        <span class="badge bg-danger me-1 mb-1"><?= User::allergyLabel($a) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($prefs['preferences'])): ?>
                    <div class="mb-3">
                        <strong class="text-success"><i class="fas fa-leaf me-1"></i>Preferensi Diet:</strong>
                        <?php foreach ($prefs['preferences'] as $p): ?>
                        <span class="badge bg-success me-1 mb-1"><?= User::preferenceLabel($p) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($prefs['notes'])): ?>
                    <div>
                        <strong><i class="fas fa-sticky-note me-1"></i>Catatan:</strong>
                        <p class="text-muted mb-0"><?= View::e($prefs['notes']) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('foodPrefsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);

    // Convert checkboxes to arrays
    var allergies = [];
    var preferences = [];
    this.querySelectorAll('input[name="allergies[]"]:checked').forEach(function(cb) { allergies.push(cb.value); });
    this.querySelectorAll('input[name="preferences[]"]:checked').forEach(function(cb) { preferences.push(cb.value); });

    formData.append('allergies', allergies);
    formData.append('preferences', preferences);

    fetch(window.APP_URL + 'dashboard/updateFoodPreferences', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.status === 'success') {
            Swal.fire({ icon: 'success', title: 'Tersimpan!', text: data.message, timer: 1500, showConfirmButton: false })
                .then(function() { location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message });
        }
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
