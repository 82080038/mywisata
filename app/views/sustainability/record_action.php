<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-seedling me-2"></i>
                        Record Eco Action
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="/sustainability/recordAction">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Action Type</label>
                                <select class="form-select" name="action_type" required>
                                    <option value="">Select action</option>
                                    <option value="public_transport">Used Public Transport (+10 pts)</option>
                                    <option value="eco_accommodation">Stayed at Eco Accommodation (+15 pts)</option>
                                    <option value="local_food">Ate Local Food (+5 pts)</option>
                                    <option value="carbon_offset">Carbon Offset (+50 pts)</option>
                                    <option value="waste_reduction">Waste Reduction (+5 pts)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <input type="text" class="form-control" name="description" required>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Recording eco actions will earn you points and improve your eco score!
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Record Action
                        </button>
                        <a href="/sustainability" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
