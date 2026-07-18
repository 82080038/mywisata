<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-briefcase me-2"></i>
                        Business Operations Dashboard
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Quick Actions -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-user-check fa-2x mb-2"></i>
                                    <h5>Match Guide</h5>
                                    <p class="mb-0">AI-powered guide matching</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                                    <h5>Schedule</h5>
                                    <p class="mb-0">Manage guide schedules</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                    <h5>Payroll</h5>
                                    <p class="mb-0">Guide payroll management</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body text-center">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <h5>Clock In/Out</h5>
                                    <p class="mb-0">GPS-based attendance</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Guide Match Results -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Guide Matches</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Booking ID</th>
                                                    <th>Guide</th>
                                                    <th>Match Score</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($data['recent_matches'])): ?>
                                                    <?php foreach ($data['recent_matches'] as $match): ?>
                                                        <tr>
                                                            <td>#<?php echo $match['booking_id']; ?></td>
                                                            <td>Guide #<?php echo $match['matched_guide_id']; ?></td>
                                                            <td>
                                                                <div class="progress" style="height: 20px;">
                                                                    <div class="progress-bar bg-success" style="width: <?php echo $match['match_score']; ?>%">
                                                                        <?php echo number_format($match['match_score'], 1); ?>%
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <?php if ($match['is_accepted'] === 1): ?>
                                                                    <span class="badge bg-success">Accepted</span>
                                                                <?php elseif ($match['is_accepted'] === 0): ?>
                                                                    <span class="badge bg-danger">Rejected</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-warning">Pending</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><?php echo date('M d, Y', strtotime($match['created_at'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No recent matches</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Clock In Records -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Clock In Records</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Guide</th>
                                                    <th>Booking</th>
                                                    <th>Clock In</th>
                                                    <th>Clock Out</th>
                                                    <th>Hours</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($data['recent_clock_ins'])): ?>
                                                    <?php foreach ($data['recent_clock_ins'] as $record): ?>
                                                        <tr>
                                                            <td>Guide #<?php echo $record['guide_id']; ?></td>
                                                            <td>#<?php echo $record['booking_id']; ?></td>
                                                            <td><?php echo date('H:i', strtotime($record['clock_in_time'])); ?></td>
                                                            <td><?php echo $record['clock_out_time'] ? date('H:i', strtotime($record['clock_out_time'])) : '-'; ?></td>
                                                            <td><?php echo $record['hours_worked'] ? number_format($record['hours_worked'], 2) : '-'; ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo $record['status'] === 'clocked_out' ? 'success' : 'warning'; ?>">
                                                                    <?php echo ucfirst($record['status']); ?>
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="6" class="text-center">No clock in records</td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
