<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-envelope me-2"></i>Pesan Saya</h2>
                <a href="<?= View::url('messages/compose') ?>" class="btn btn-primary">
                    <i class="fas fa-pen me-2"></i>Pesan Baru
                </a>
            </div>

            <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success"><?= View::e(Session::getFlash('success')) ?></div>
            <?php endif; ?>
            <?php if (Session::hasFlash('error')): ?>
            <div class="alert alert-danger"><?= View::e(Session::getFlash('error')) ?></div>
            <?php endif; ?>

            <?php if (empty($conversations)): ?>
            <div class="text-center py-5">
                <i class="fas fa-envelope-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Belum ada pesan</h4>
                <p class="text-muted mb-4">Mulai percakapan dengan pemilik hotel, tour guide, atau penjual souvenir</p>
            </div>
            <?php else: ?>
            <div class="list-group shadow-sm">
                <?php foreach ($conversations as $conv):
                    $userId = Session::get('user_id');
                    $otherName = $conv['user1_id'] == $userId ? $conv['user2_name'] : $conv['user1_name'];
                    $otherRole = $conv['user1_id'] == $userId ? $conv['user1_role'] : $conv['user2_role'];
                    $unread = $conv['unread_count'] ?? 0;
                    $roleLabels = ['admin' => 'Admin', 'merchant' => 'Penjual', 'tour_guide' => 'Tour Guide', 'wisatawan' => 'Wisatawan'];
                    $roleColors = ['admin' => 'danger', 'merchant' => 'success', 'tour_guide' => 'info', 'wisatawan' => 'secondary'];
                    $roleIcons = ['admin' => 'fa-cog', 'merchant' => 'fa-store', 'tour_guide' => 'fa-user-tie', 'wisatawan' => 'fa-user'];
                ?>
                <a href="<?= View::url('messages/chat?id=' . $conv['id']) ?>" 
                   class="list-group-item list-group-item-action d-flex align-items-center <?= $unread > 0 ? 'fw-bold' : '' ?>">
                    <div class="bg-<?= $roleColors[$otherRole] ?? 'secondary' ?> bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px; min-width: 50px;">
                        <i class="fas <?= $roleIcons[$otherRole] ?? 'fa-user' ?> text-<?= $roleColors[$otherRole] ?? 'secondary' ?> fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold"><?= View::e($otherName) ?></span>
                                <span class="badge bg-<?= $roleColors[$otherRole] ?? 'secondary' ?> ms-1"><?= $roleLabels[$otherRole] ?? 'User' ?></span>
                                <?php if (!empty($conv['subject'])): ?>
                                <small class="text-muted ms-2">— <?= View::e($conv['subject']) ?></small>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($conv['last_message_time'])): ?>
                            <small class="text-muted"><?= date('d M H:i', strtotime($conv['last_message_time'])) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($conv['last_message'])): ?>
                        <p class="mb-0 text-muted small text-truncate" style="max-width: 500px;">
                            <?= $conv['last_sender_id'] == $userId ? '<i class="fas fa-reply me-1"></i>' : '' ?>
                            <?= View::e(substr($conv['last_message'], 0, 80)) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php if ($unread > 0): ?>
                    <span class="badge bg-danger rounded-pill ms-2"><?= $unread ?></span>
                    <?php endif; ?>
                </a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
