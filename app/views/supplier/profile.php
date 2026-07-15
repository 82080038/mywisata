<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="h4 mb-4">Manage Profile</h2>
            
            <?php if (isset($guide)): ?>
            <form id="profile-form" class="needs-validation" novalidate>
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" class="form-control" name="name" 
                                       value="<?php echo htmlspecialchars($guide['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($guide['phone']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city" 
                                       value="<?php echo htmlspecialchars($guide['city']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">License Number</label>
                                <input type="text" class="form-control" name="license_number" 
                                       value="<?php echo htmlspecialchars($guide['license_number']); ?>">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Bio</label>
                                <textarea class="form-control" name="bio" rows="4"><?php echo htmlspecialchars($guide['bio']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pricing</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hourly Rate (IDR)</label>
                                <input type="number" class="form-control" name="hourly_rate" 
                                       value="<?php echo $guide['hourly_rate']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Daily Rate (IDR)</label>
                                <input type="number" class="form-control" name="daily_rate" 
                                       value="<?php echo $guide['daily_rate']; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Experience</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" name="experience_years" 
                                       value="<?php echo $guide['experience_years']; ?>" required>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Location</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" class="form-control" name="latitude" 
                                       value="<?php echo $guide['latitude']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" class="form-control" name="longitude" 
                                       value="<?php echo $guide['longitude']; ?>">
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Availability</h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_available" 
                                   id="isAvailable" <?php echo $guide['is_available'] ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isAvailable">
                                Available for bookings
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Save Profile
                    </button>
                    <a href="<?php echo BASE_URL; ?>supplier" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-warning">
                <h5>Profile Not Found</h5>
                <p>Please create your tour guide profile first.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$('#profile-form').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    data.is_available = $('#isAvailable').is(':checked') ? 1 : 0;
    
    API.post('supplier/updateProfile', data, function(res) {
        if (res.status === 'success') {
            Swal.fire('Success!', 'Profile updated successfully', 'success').then(() => {
                location.reload();
            });
        } else {
            Swal.fire('Error', res.message, 'error');
        }
    });
});
</script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
