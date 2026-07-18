<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-purple text-white" style="background-color: #6f42c1;">
                    <h4 class="mb-0">
                        <i class="fas fa-users me-2"></i>
                        Group Trips
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Create Group Trip Button -->
                    <div class="mb-4">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTripModal">
                            <i class="fas fa-plus me-2"></i>Create Group Trip
                        </button>
                    </div>

                    <!-- Group Trips List -->
                    <div class="row">
                        <?php if (!empty($data['trips'])): ?>
                            <?php foreach ($data['trips'] as $trip): ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title"><?php echo htmlspecialchars($trip['trip_name']); ?></h5>
                                            <p class="card-text">
                                                <small class="text-muted">
                                                    <?php if ($trip['start_date']): ?>
                                                        <?php echo date('M d, Y', strtotime($trip['start_date'])); ?>
                                                        <?php if ($trip['end_date']): ?>
                                                            - <?php echo date('M d, Y', strtotime($trip['end_date'])); ?>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </small>
                                            </p>
                                            <p class="card-text">
                                                <span class="badge bg-<?php echo $trip['status'] === 'confirmed' ? 'success' : ($trip['status'] === 'planning' ? 'warning' : 'secondary'); ?>">
                                                    <?php echo ucfirst($trip['status']); ?>
                                                </span>
                                                <span class="badge bg-info">
                                                    <?php echo $trip['participant_count']; ?> participants
                                                </span>
                                            </p>
                                            <div class="btn-group w-100">
                                                <a href="/social_features/group_trip/<?php echo $trip['id']; ?>" class="btn btn-sm btn-outline-primary">View</a>
                                                <a href="/social_features/group_trip/<?php echo $trip['id']; ?>/invite" class="btn btn-sm btn-outline-success">Invite</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-2"></i>
                                    No group trips yet. Create your first group trip!
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create Group Trip Modal -->
<div class="modal fade" id="createTripModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Group Trip</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST" action="/social_features/createGroupTrip">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Trip Name</label>
                            <input type="text" class="form-control" name="trip_name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destination</label>
                            <select class="form-select" name="destination_id">
                                <option value="">Select destination</option>
                                <?php if (!empty($data['destinations'])): ?>
                                    <?php foreach ($data['destinations'] as $dest): ?>
                                        <option value="<?php echo $dest['id']; ?>">
                                            <?php echo htmlspecialchars($dest['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Max Participants</label>
                            <input type="number" class="form-control" name="max_participants">
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" name="is_public" id="isPublic">
                                <label class="form-check-label" for="isPublic">Public Trip</label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create Trip</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
