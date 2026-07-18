<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4 shadow">
                <?php if ($package['main_image']): ?>
                <img src="<?= View::asset('uploads/packages/' . $package['main_image']) ?>" class="card-img-top" style="height:350px;object-fit:cover;">
                <?php else: ?>
                <img src="https://via.placeholder.com/800x350?text=<?= urlencode($package['title']) ?>" class="card-img-top" style="height:350px;object-fit:cover;">
                <?php endif; ?>
                <div class="card-body">
                    <h1 class="card-title"><?= View::e($package['title']) ?></h1>
                    <div class="d-flex gap-3 mb-3">
                        <span class="text-muted"><i class="fas fa-clock me-1"></i><?= $package['duration_days'] ?> hari</span>
                        <span class="text-muted"><i class="fas fa-users me-1"></i><?= $package['min_travelers'] ?>-<?= $package['max_travelers'] ?> orang</span>
                    </div>
                    
                    <?php
                    $shareUrl = View::url('packages/detail/' . $package['id']);
                    $shareTitle = $package['title'] . ' - Paket Wisata MyWisata';
                    $shareText = 'Liburan hemat dengan ' . $package['title'];
                    include APP_ROOT . '/app/views/partials/social_share.php';
                    ?>

                    <p class="card-text mt-3"><?= nl2br(View::e($package['description'])) ?></p>
                </div>
            </div>

            <!-- Itinerary -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-route me-2"></i>Itinerary Paket</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($itemsByDay)): ?>
                    <p class="text-muted">Itinerary akan ditambahkan segera</p>
                    <?php else: ?>
                    <?php foreach ($itemsByDay as $day => $items): ?>
                    <div class="mb-3">
                        <h6 class="text-primary"><i class="fas fa-day-<?= $day ?> me-2"></i>Hari ke-<?= $day ?></h6>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($items as $item): ?>
                            <li class="list-group-item px-0">
                                <i class="fas fa-<?= $item['item_type'] === 'destination' ? 'map-marked-alt' : ($item['item_type'] === 'hotel' ? 'hotel' : ($item['item_type'] === 'restaurant' ? 'utensils' : 'star')) ?> me-2 text-primary"></i>
                                <?= View::e($item['item_name'] ?? 'Item') ?>
                                <?php if ($item['notes']): ?>
                                <br><small class="text-muted"><?= View::e($item['notes']) ?></small>
                                <?php endif; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Includes/Excludes -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0"><i class="fas fa-check me-2"></i>Sudah Termasuk</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <?php $includes = json_decode($package['includes'], true) ?: []; ?>
                                <?php foreach ($includes as $inc): ?>
                                <li class="mb-1"><i class="fas fa-check text-success me-2"></i><?= View::e($inc) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mb-3">
                        <div class="card-header bg-danger text-white">
                            <h6 class="mb-0"><i class="fas fa-times me-2"></i>Tidak Termasuk</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled mb-0">
                                <?php $excludes = json_decode($package['excludes'], true) ?: []; ?>
                                <?php foreach ($excludes as $exc): ?>
                                <li class="mb-1"><i class="fas fa-times text-danger me-2"></i><?= View::e($exc) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Booking -->
        <div class="col-md-4">
            <div class="card shadow sticky-top" style="top:80px;">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Pesan Paket Ini</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <?php if ($package['discount_price']): ?>
                        <small class="text-muted text-decoration-line-through">Rp <?= number_format($package['price'], 0, ',', '.') ?></small>
                        <h3 class="text-danger mb-0">Rp <?= number_format($package['discount_price'], 0, ',', '.') ?></h3>
                        <small class="text-muted">per traveler</small>
                        <?php else: ?>
                        <h3 class="text-primary mb-0">Rp <?= number_format($package['price'], 0, ',', '.') ?></h3>
                        <small class="text-muted">per traveler</small>
                        <?php endif; ?>
                    </div>

                    <form method="POST" action="<?= View::url('packages/book') ?>">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                        <input type="hidden" name="package_id" value="<?= $package['id'] ?>">
                        
                        <div class="mb-3">
                            <label class="form-label small">Tanggal Mulai</label>
                            <input type="date" class="form-control" name="start_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small">Jumlah Traveler</label>
                            <input type="number" class="form-control" name="travelers" value="<?= $package['min_travelers'] ?>" min="<?= $package['min_travelers'] ?>" max="<?= $package['max_travelers'] ?>" required>
                        </div>

                        <div class="mb-3" id="totalDisplay">
                            <strong>Total: </strong><span class="text-primary fs-5" id="totalAmount">Rp <?= number_format($package['discount_price'] ?: $package['price'], 0, ',', '.') ?></span>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-shopping-cart me-1"></i>Pesan Sekarang
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var pricePerTraveler = <?= ($package['discount_price'] ?: $package['price']) ?>;
    $('input[name="travelers"]').on('input', function() {
        var total = pricePerTraveler * parseInt($(this).val() || 1);
        $('#totalAmount').text('Rp ' + total.toLocaleString('id-ID'));
    });
});
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
