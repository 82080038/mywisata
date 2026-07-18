<?php require_once APP_PATH . '/app/views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-leaf me-2"></i>
                        Sustainability Dashboard
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Eco Score Card -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Eco Score</h5>
                                    <div class="display-4 fw-bold text-success" id="ecoScore">
                                        <?php echo $data['eco_score']['score'] ?? 0; ?>
                                    </div>
                                    <span class="badge bg-<?php echo $data['eco_score']['level'] === 'platinum' ? 'primary' : ($data['eco_score']['level'] === 'gold' ? 'warning' : ($data['eco_score']['level'] === 'silver' ? 'secondary' : 'info')); ?>">
                                        <?php echo ucfirst($data['eco_score']['level'] ?? 'bronze'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">CO2 Saved</h5>
                                    <div class="display-4 fw-bold text-info">
                                        <?php echo number_format($data['total_co2_saved'] ?? 0, 2); ?> kg
                                    </div>
                                    <small class="text-muted">Total carbon offset</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Eco Points</h5>
                                    <div class="display-4 fw-bold text-warning">
                                        <?php echo number_format($data['total_points'] ?? 0); ?>
                                    </div>
                                    <small class="text-muted">Points earned</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Carbon Emissions Chart -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Carbon Emissions by Type</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="emissionsChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Eco Actions -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Recent Eco Actions</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Action</th>
                                                    <th>Type</th>
                                                    <th>CO2 Saved</th>
                                                    <th>Points</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($data['recent_actions'])): ?>
                                                    <?php foreach ($data['recent_actions'] as $action): ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars($action['description']); ?></td>
                                                            <td>
                                                                <span class="badge bg-secondary">
                                                                    <?php echo ucfirst(str_replace('_', ' ', $action['action_type'])); ?>
                                                                </span>
                                                            </td>
                                                            <td><?php echo number_format($action['co2_saved_kg'], 2); ?> kg</td>
                                                            <td><?php echo $action['points_earned']; ?></td>
                                                            <td><?php echo date('M d, Y', strtotime($action['created_at'])); ?></td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td colspan="5" class="text-center">No eco actions recorded yet</td>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Carbon Emissions Chart
const ctx = document.getElementById('emissionsChart').getContext('2d');
const emissionsChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Transport', 'Accommodation', 'Food', 'Activity'],
        datasets: [{
            label: 'CO2 Emissions (kg)',
            data: [
                <?php echo $data['emissions_by_type']['transport'] ?? 0; ?>,
                <?php echo $data['emissions_by_type']['accommodation'] ?? 0; ?>,
                <?php echo $data['emissions_by_type']['food'] ?? 0; ?>,
                <?php echo $data['emissions_by_type']['activity'] ?? 0; ?>
            ],
            backgroundColor: [
                'rgba(255, 99, 132, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(255, 206, 86, 0.6)',
                'rgba(75, 192, 192, 0.6)'
            ],
            borderColor: [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php require_once APP_PATH . '/app/views/layouts/footer.php'; ?>
