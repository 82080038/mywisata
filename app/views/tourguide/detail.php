<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <!-- Guide Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <?php if (!empty($guide['avatar'])): ?>
                            <img src="<?= View::asset('uploads/avatars/' . $guide['avatar']) ?>" class="rounded-circle me-4" style="width: 120px; height: 120px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle me-4 bg-success d-flex align-items-center justify-content-center" style="width: 120px; height: 120px;">
                                <i class="fas fa-user-tie text-white" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h1 class="mb-2"><?= View::e($guide['name'] ?? 'Tour Guide') ?></h1>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i><?= View::e($guide['city'] ?? 'Indonesia') ?>
                            </p>
                            <div class="d-flex align-items-center mb-2">
                                <div class="text-warning me-3">
                                    <i class="fas fa-star"></i> <?= number_format($guide['rating_avg'] ?? 0, 1) ?>
                                    <span class="text-muted small">(<?= count($reviews ?? []) ?> review)</span>
                                </div>
                                <?php if ($guide['is_verified'] ?? false): ?>
                                    <span class="badge bg-success">Terverifikasi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-2">Pengalaman</h6>
                            <p><?= $guide['experience_years'] ?? 0 ?> tahun</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Status</h6>
                            <p><?= ($guide['is_available'] ?? false) ? 'Tersedia' : 'Tidak Tersedia' ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h6 class="mb-2">Tentang</h6>
                        <p class="text-muted"><?= View::e($guide['bio'] ?? 'Tidak ada bio tersedia') ?></p>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="mb-2">Tarif</h6>
                            <p><?= View::currency($guide['hourly_rate'] ?? 0) ?>/jam</p>
                            <p><?= View::currency($guide['daily_rate'] ?? 0) ?>/hari</p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="mb-2">Kontak</h6>
                            <p><?= View::e($guide['phone'] ?? 'Tidak tersedia') ?></p>
                        </div>
                    </div>
                    
                    <!-- Languages -->
                    <?php if (!empty($languages)): ?>
                    <div class="mb-4">
                        <h6 class="mb-2">Bahasa</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($languages as $lang): ?>
                                <span class="badge bg-info"><?= View::e($lang['language_name'] ?? $lang['name'] ?? 'Unknown') ?> (<?= View::e($lang['proficiency'] ?? 'Basic') ?>)</span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Specializations -->
                    <?php if (!empty($specializations)): ?>
                    <div class="mb-4">
                        <h6 class="mb-2">Spesialisasi</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach ($specializations as $spec): ?>
                                <span class="badge bg-primary"><?= View::e($spec['specialization_name'] ?? $spec['name'] ?? 'Unknown') ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <a href="<?= View::url('booking/create?guide_id=' . $guide['id']) ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check me-2"></i>Booking Tour Guide
                    </a>
                </div>
            </div>
            
            <!-- Reviews -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Ulasan</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($reviews)): ?>
                        <p class="text-muted">Belum ada ulasan.</p>
                    <?php else: ?>
                        <?php foreach ($reviews as $review): ?>
                        <div class="border-bottom pb-3 mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <h6 class="mb-1"><?= View::e($review['user_name'] ?? 'Anonymous') ?></h6>
                                <small class="text-muted"><?= View::date($review['created_at'] ?? null) ?></small>
                            </div>
                            <div class="text-warning mb-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= ($review['rating'] ?? 0) ? '' : 'far' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <p class="mb-0 mt-2"><?= View::e($review['comment'] ?? '') ?></p>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Informasi Booking</h5>
                </div>
                <div class="card-body">
                    <h6 class="mb-2">Ketersediaan</h6>
                    <p class="text-muted"><?= ($guide['is_available'] ?? false) ? 'Tour guide ini tersedia untuk booking.' : 'Tour guide ini sedang tidak tersedia.' ?></p>
                    
                    <h6 class="mb-2">Metode Pembayaran</h6>
                    <p class="text-muted">Pembayaran dapat dilakukan melalui transfer bank atau kartu kredit.</p>
                    
                    <h6 class="mb-2">Kebijakan Pembatalan</h6>
                    <p class="text-muted">Pembatalan dapat dilakukan 24 jam sebelum jadwal booking dengan pengembalian dana penuh.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
