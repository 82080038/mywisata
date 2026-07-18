<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-pink text-white" style="background-color: #e83e8c;">
                    <h4 class="mb-0">
                        <i class="fas fa-heart me-2"></i>
                        Shared Wishlists
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Create Wishlist Button -->
                    <div class="mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createWishlistModal">
                            <i class="fas fa-plus me-2"></i>Create Wishlist
                        </button>
                    </div>

                    <!-- Wishlists List -->
                    <div class="row">
                        <?php if (!empty($data['wishlists'])): ?>
                            <?php foreach ($data['wishlists'] as $wishlist): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($wishlist['wishlist_name']); ?></h5>
                                            <p class="card-text">
                                                <span class="badge bg-info">
                                                    <?php echo $wishlist['item_count']; ?> items
                                                </span>
                                                <?php if ($wishlist['is_public']): ?>
                                                    <span class="badge bg-success">Public</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Private</span>
                                                <?php endif; ?>
                                            </p>
                                            <div class="btn-group w-100">
                                                <a href="/social_features/wishlist/<?php echo $wishlist['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="/social_features/wishlist/<?php echo $wishlist['id']; ?>/add" class="btn btn-sm btn-outline-success">Add Item</a>
                                                <a href="/social_features/wishlist/<?php echo $wishlist['id']; ?>/collaborate" class="btn btn-sm btn-outline-info">Share</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No wishlists yet. Create your first shared wishlist!
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Wishlist Modal -->
<div class="modal fade" id="createWishlistModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Shared Wishlist</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/social_features/createWishlist">
                    <div class="mb-3">
                        <label class="form-label">Wishlist Name</label>
                        <input type="text" class="form-control" name="wishlist_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_public" id="isPublicWishlist">
                        <label class="form-check-label" for="isPublicWishlist">Public Wishlist</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Wishlist</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
