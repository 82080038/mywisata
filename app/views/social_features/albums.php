<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-orange text-white" style="background-color: #fd7e14;">
                    <h4 class="mb-0">
                        <i class="fas fa-images me-2"></i>
                        Trip Albums
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Create Album Button -->
                    <div class="mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createAlbumModal">
                            <i class="fas fa-plus me-2"></i>Create Album
                        </button>
                    </div>

                    <!-- Albums Grid -->
                    <div class="row">
                        <?php if (!empty($data['albums'])): ?>
                            <?php foreach ($data['albums'] as $album): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <?php if ($album['cover_photo']): ?>
                                            <img src="<?php echo htmlspecialchars($album['cover_photo']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($album['album_name']); ?>" style="height: 200px; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                                <i class="fas fa-image fa-3x text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($album['album_name']); ?></h5>
                                            <p class="card-text">
                                                <span class="badge bg-info">
                                                    <?php echo $album['photo_count']; ?> photos
                                                </span>
                                                <?php if ($album['is_public']): ?>
                                                    <span class="badge bg-success">Public</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Private</span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="btn-group w-100">
                                                <a href="/social_features/album/<?php echo $album['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="/social_features/album/<?php echo $album['id']; ?>/upload" class="btn btn-sm btn-outline-success">Upload</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No albums yet. Create your first trip album!
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Album Modal -->
<div class="modal fade" id="createAlbumModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Trip Album</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/social_features/createAlbum" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Album Name</label>
                        <input type="text" class="form-control" name="album_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Group Trip (optional)</label>
                        <select class="form-select" name="group_trip_id">
                            <option value="">Select group trip</option>
                            <?php if (!empty($data['group_trips'])): ?>
                                <?php foreach ($data['group_trips'] as $trip): ?>
                                    <option value="<?php echo $trip['id']; ?>">
                                        <?php echo htmlspecialchars($trip['trip_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" id="isPublicAlbum">
                        <label class="form-check-label" for="isPublicAlbum">Public Album</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Album</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
