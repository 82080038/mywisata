<?php require_once APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h2 class="h4 mb-4">Supplier Dashboard</h2>
            
            <?php if (isset($guide)): ?>
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h6 class="card-title">Pending Bookings</h6>
                            <h3 class="mb-0"><?php echo count($pending_bookings); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h6 class="card-title">Monthly Earnings</h6>
                            <h3 class="mb-0">Rp <?php echo number_format($monthly_earnings['total'] ?? 0); ?></h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h6 class="card-title">Rating</h6>
                            <h3 class="mb-0"><?php echo number_format($guide['rating_avg'], 1); ?> ⭐</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <h6 class="card-title">Total Tours</h6>
                            <h3 class="mb-0"><?php echo $guide['total_tours']; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex gap-2">
                                <a href="<?php echo BASE_URL; ?>supplier/schedule" class="btn btn-primary">
                                    <i class="fas fa-calendar me-2"></i>Manage Schedule
                                </a>
                                <a href="<?php echo BASE_URL; ?>supplier/bookings" class="btn btn-success">
                                    <i class="fas fa-list me-2"></i>View Bookings
                                </a>
                                <a href="<?php echo BASE_URL; ?>supplier/profile" class="btn btn-info">
                                    <i class="fas fa-user me-2"></i>Edit Profile
                                </a>
                                <a href="<?php echo BASE_URL; ?>supplier/earnings" class="btn btn-warning">
                                    <i class="fas fa-chart-line me-2"></i>View Earnings
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Pending Bookings -->
            <?php if (!empty($pending_bookings)): ?>
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Pending Bookings</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                            <th>Amount</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pending_bookings as $booking): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($booking['booking_date'])); ?></td>
                                            <td><?php echo date('H:i', strtotime($booking['start_time'])); ?></td>
                                            <td><?php echo $booking['duration']; ?> hours</td>
                                            <td>Rp <?php echo number_format($booking['total_amount']); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-success btn-accept" data-id="<?php echo $booking['id']; ?>">
                                                    Accept
                                                </button>
                                                <button class="btn btn-sm btn-danger btn-reject" data-id="<?php echo $booking['id']; ?>">
                                                    Reject
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="alert alert-info">
                <h5>Welcome to Supplier Portal</h5>
                <p>Please complete your profile to start accepting bookings.</p>
                <a href="<?php echo BASE_URL; ?>supplier/profile" class="btn btn-primary">
                    Complete Profile
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
$(document).on('click', '.btn-accept', function() {
    const bookingId = $(this).data('id');
    
    Swal.fire({
        title: 'Accept Booking?',
        text: 'You will be committed to this booking.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Accept',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            API.post('supplier/updateBookingStatus', {
                booking_id: bookingId,
                status: 'confirmed'
            }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Success!', 'Booking accepted', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
        }
    });
});

$(document).on('click', '.btn-reject', function() {
    const bookingId = $(this).data('id');
    
    Swal.fire({
        title: 'Reject Booking?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            API.post('supplier/updateBookingStatus', {
                booking_id: bookingId,
                status: 'rejected'
            }, function(res) {
                if (res.status === 'success') {
                    Swal.fire('Success!', 'Booking rejected', 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', res.message, 'error');
                }
            });
        }
    });
});
</script>

<?php require_once APP_ROOT . '/app/views/layouts/footer.php'; ?>
