<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-smog me-2"></i>
                        Record Carbon Emission
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="/sustainability/recordEmission">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Emission Type</label>
                                <select class="form-select" name="emission_type" required>
                                    <option value="">Select type</option>
                                    <option value="transport">Transport</option>
                                    <option value="accommodation">Accommodation</option>
                                    <option value="food">Food</option>
                                    <option value="activity">Activity</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">CO2 Amount (kg)</label>
                                <input type="number" step="0.01" class="form-control" name="co2_kg" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Transport Mode (if applicable)</label>
                                <select class="form-select" name="transport_mode">
                                    <option value="">Select mode</option>
                                    <option value="car">Car</option>
                                    <option value="bus">Bus</option>
                                    <option value="train">Train</option>
                                    <option value="flight">Flight</option>
                                    <option value="motorcycle">Motorcycle</option>
                                    <option value="walking">Walking</option>
                                    <option value="cycling">Cycling</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Distance (km)</label>
                                <input type="number" step="0.1" class="form-control" name="distance_km">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Booking ID (optional)</label>
                            <input type="number" class="form-control" name="booking_id">
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Record Emission
                        </button>
                        <a href="/sustainability" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
