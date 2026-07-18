<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-magic me-2"></i>AI Itinerary Builder</h2>

    <div class="row">
        <!-- Left: Preferences Panel -->
        <div class="col-lg-4">
            <div class="card shadow-sm mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-sliders me-2"></i>Preferensi Perjalanan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Kota / Destinasi Utama</label>
                        <select class="form-select" id="prefCity">
                            <option value="">Semua Kota</option>
                            <?php foreach ($cities as $city): ?>
                            <option value="<?= View::e($city['city']) ?>"><?= View::e($city['city']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Hari</label>
                        <input type="number" class="form-control" id="prefDays" value="3" min="1" max="7">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jumlah Traveler</label>
                        <input type="number" class="form-control" id="prefTravelers" value="2" min="1" max="20">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Budget (IDR)</label>
                        <select class="form-select" id="prefBudget">
                            <option value="1000000">Hemat (&lt; Rp 1jt)</option>
                            <option value="3000000" selected>Sedang (Rp 1-3jt)</option>
                            <option value="5000000">Nyaman (Rp 3-5jt)</option>
                            <option value="10000000">Mewah (&gt; Rp 5jt)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minat</label>
                        <div class="row">
                            <?php foreach ($categories as $cat): ?>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input prefInterest" type="checkbox" value="<?= View::e($cat['slug']) ?>" id="cat_<?= $cat['id'] ?>">
                                    <label class="form-check-label small" for="cat_<?= $cat['id'] ?>">
                                        <i class="fas <?= View::e($cat['icon'] ?? 'fa-tag') ?> me-1"></i><?= View::e($cat['name']) ?>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary w-100" id="generateBtn">
                        <i class="fas fa-magic me-1"></i>Generate Itinerary
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Results -->
        <div class="col-lg-8">
            <div id="suggestionResult" style="display:none;">
                <div class="card shadow-sm mb-3">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-route me-2"></i>Itinerary yang Dihasilkan</h5>
                        <span class="badge bg-success fs-6" id="totalCostBadge">Rp 0</span>
                    </div>
                    <div class="card-body">
                        <!-- Save Form -->
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control form-control-sm" id="itinTitle" placeholder="Judul Itinerary">
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="itinStartDate">
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control form-control-sm" id="itinEndDate">
                            </div>
                        </div>
                        <div id="itineraryDays"></div>
                        <button type="button" class="btn btn-success mt-3 w-100" id="saveBtn">
                            <i class="fas fa-save me-1"></i>Simpan Itinerary
                        </button>
                    </div>
                </div>
            </div>
            <div id="loadingSpinner" class="text-center py-5" style="display:none;">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">AI sedang menyusun itinerary...</p>
            </div>
            <div id="emptyState" class="text-center py-5">
                <i class="fas fa-magic fa-4x text-muted mb-3 opacity-50"></i>
                <h4 class="text-muted">Atur preferensi dan klik Generate</h4>
                <p class="text-muted">AI akan menyusun itinerary optimal berdasarkan preferensi Anda</p>
            </div>
        </div>
    </div>
</div>

<script>
var generatedData = null;

$(document).ready(function() {
    $('#generateBtn').on('click', function() {
        var interests = [];
        $('.prefInterest:checked').each(function() {
            interests.push($(this).val());
        });

        $('#emptyState, #suggestionResult').hide();
        $('#loadingSpinner').show();

        ajax({
            url: APP_URL + 'itinerary/generate',
            method: 'POST',
            data: {
                city: $('#prefCity').val(),
                days: $('#prefDays').val(),
                travelers: $('#prefTravelers').val(),
                budget: $('#prefBudget').val(),
                interests: interests,
                csrf_token: '<?= $csrf_token ?>'
            },
            success: function(response) {
                $('#loadingSpinner').hide();
                if (response.status === 'success') {
                    generatedData = response;
                    renderItinerary(response);
                    $('#suggestionResult').show();
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: response.message || 'Gagal generate' });
                    $('#emptyState').show();
                }
            },
            error: function() {
                $('#loadingSpinner').hide();
                $('#emptyState').show();
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan' });
            }
        });
    });

    $('#saveBtn').on('click', function() {
        if (!generatedData) return;

        var title = $('#itinTitle').val() || 'Itinerary ' + new Date().toLocaleDateString('id-ID');
        var startDate = $('#itinStartDate').val();
        var endDate = $('#itinEndDate').val();

        if (!startDate || !endDate) {
            var today = new Date();
            startDate = today.toISOString().split('T')[0];
            var end = new Date(today);
            end.setDate(end.getDate() + parseInt(generatedData.days) - 1);
            endDate = end.toISOString().split('T')[0];
        }

        var items = [];
        for (var day in generatedData.suggestion) {
            generatedData.suggestion[day].forEach(function(item) {
                items.push({
                    day: parseInt(day),
                    item_type: item.item_type,
                    item_id: item.item_id,
                    item_name: item.item_name,
                    start_time: item.start_time,
                    end_time: item.end_time,
                    location: item.location,
                    estimated_cost: item.estimated_cost,
                    notes: item.notes
                });
            });
        }

        var form = document.createElement('form');
        form.method = 'POST';
        form.action = APP_URL + 'itinerary/save';

        function addField(name, value) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        addField('csrf_token', '<?= $csrf_token ?>');
        addField('title', title);
        addField('start_date', startDate);
        addField('end_date', endDate);
        addField('num_days', generatedData.days);
        addField('num_travelers', $('#prefTravelers').val());
        addField('budget_max', $('#prefBudget').val());
        addField('items_json', JSON.stringify(items));

        document.body.appendChild(form);
        form.submit();
    });
});

function renderItinerary(data) {
    var typeIcons = {
        destination: 'fa-map-marked-alt', hotel: 'fa-hotel',
        restaurant: 'fa-utensils', event: 'fa-calendar-alt',
        transport: 'fa-car', rest: 'fa-coffee', custom: 'fa-star'
    };
    var typeColors = {
        destination: 'primary', hotel: 'info',
        restaurant: 'warning', event: 'success',
        transport: 'secondary', rest: 'light', custom: 'dark'
    };

    var html = '';
    var totalCost = 0;

    for (var day in data.suggestion) {
        var items = data.suggestion[day];
        html += '<div class="card mb-2"><div class="card-header bg-light">';
        html += '<strong>Hari ' + day + '</strong>';
        html += '</div><div class="card-body p-0"><table class="table table-sm mb-0">';

        items.forEach(function(item) {
            var icon = typeIcons[item.item_type] || 'fa-star';
            var color = typeColors[item.item_type] || 'primary';
            var cost = item.estimated_cost || 0;
            totalCost += cost;

            html += '<tr>';
            html += '<td style="width:60px;" class="text-center"><i class="fas ' + icon + ' text-' + color + '"></i></td>';
            html += '<td><strong>' + item.item_name + '</strong>';
            if (item.start_time) html += '<br><small class="text-muted">' + (item.start_time || '') + (item.end_time ? ' - ' + item.end_time : '') + '</small>';
            if (item.notes) html += '<br><small class="text-muted">' + item.notes + '</small>';
            html += '</td>';
            html += '<td class="text-end"><small class="text-muted">Rp ' + cost.toLocaleString('id-ID') + '</small></td>';
            html += '</tr>';
        });

        html += '</table></div></div>';
    }

    $('#itineraryDays').html(html);
    $('#totalCostBadge').text('Rp ' + data.total_cost.toLocaleString('id-ID'));
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
