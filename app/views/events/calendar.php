<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<?php
$monthNames = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
$fullDayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

$firstDay = mktime(0, 0, 0, $month, 1, $year);
$numDays = date('t', $firstDay);
$startWeekday = date('w', $firstDay);

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

$eventsByDay = [];
foreach ($events as $ev) {
    $day = (int)date('j', strtotime($ev['start_date']));
    if (!isset($eventsByDay[$day])) {
        $eventsByDay[$day] = [];
    }
    $eventsByDay[$day][] = $ev;
}

$categoryColors = [
    'festival' => 'danger',
    'seni' => 'info',
    'kuliner' => 'warning',
    'olahraga' => 'primary',
    'budaya' => 'success',
    'religi' => 'secondary',
    'other' => 'dark',
];
?>

<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2><i class="fas fa-calendar-alt me-2"></i>Kalender Event</h2>
            <p class="text-muted">Jelajahi event lokal per kota/region</p>
        </div>
        <div class="col-md-6">
            <form method="GET" class="d-flex gap-2">
                <?php if ($cityFilter): ?>
                <input type="hidden" name="city" value="<?= View::e($cityFilter) ?>">
                <?php endif; ?>
                <select name="month" class="form-select" onchange="this.form.submit()">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                    <?php endfor; ?>
                </select>
                <select name="year" class="form-select" onchange="this.form.submit()">
                    <?php $curYear = (int)date('Y'); for ($y = $curYear; $y <= $curYear + 2; $y++): ?>
                    <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- City Filter -->
    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="<?= View::url('events/calendar') ?>" class="btn btn-sm <?= empty($cityFilter) ? 'btn-primary' : 'btn-outline-primary' ?>">Semua Kota</a>
        <?php foreach ($cities as $c): ?>
        <a href="<?= View::url('events/calendar?city=' . urlencode($c['location_name'])) ?>" 
           class="btn btn-sm <?= $cityFilter === $c['location_name'] ? 'btn-primary' : 'btn-outline-primary' ?>">
            <?= View::e($c['location_name']) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Month Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="<?= View::url('events/calendar?month=' . $prevMonth . '&year=' . $prevYear . ($cityFilter ? '&city=' . urlencode($cityFilter) : '')) ?>" 
           class="btn btn-outline-primary btn-sm">
            <i class="fas fa-chevron-left me-1"></i><?= $monthNames[$prevMonth] ?>
        </a>
        <h4 class="mb-0"><?= $monthNames[$month] . ' ' . $year ?></h4>
        <a href="<?= View::url('events/calendar?month=' . $nextMonth . '&year=' . $nextYear . ($cityFilter ? '&city=' . urlencode($cityFilter) : '')) ?>" 
           class="btn btn-outline-primary btn-sm">
            <?= $monthNames[$nextMonth] ?><i class="fas fa-chevron-right ms-1"></i>
        </a>
    </div>

    <!-- Calendar Grid -->
    <div class="card shadow-sm">
        <div class="card-body p-2">
            <table class="table table-bordered mb-0 calendar-table">
                <thead>
                    <tr>
                        <?php foreach ($dayNames as $dn): ?>
                        <th class="text-center py-2" style="width: 14.28%;"><?= $dn ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $currentDay = 1;
                    $totalCells = ceil(($numDays + $startWeekday) / 7) * 7;
                    $today = date('Y-m-d');
                    for ($i = 0; $i < $totalCells; $i++):
                        $weekday = $i % 7;
                        if ($i < $startWeekday || $currentDay > $numDays):
                    ?>
                    <td class="p-1" style="min-height: 100px;">&nbsp;</td>
                    <?php
                        else:
                            $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $currentDay);
                            $isToday = $dateStr === $today;
                            $dayEvents = $eventsByDay[$currentDay] ?? [];
                    ?>
                    <td class="p-1 align-top <?= $isToday ? 'bg-light border-primary border-2' : '' ?>" style="min-height: 100px;">
                        <div class="small fw-bold <?= $isToday ? 'text-primary' : 'text-muted' ?>"><?= $currentDay ?></div>
                        <?php foreach (array_slice($dayEvents, 0, 3) as $ev): ?>
                        <a href="<?= View::url('events/detail/' . $ev['id']) ?>" 
                           class="d-block mb-1 px-1 py-0 rounded text-white text-decoration-none small bg-<?= $categoryColors[$ev['category']] ?? 'primary' ?>"
                           style="font-size: 0.7rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            <?= View::e($ev['title']) ?>
                        </a>
                        <?php endforeach; ?>
                        <?php if (count($dayEvents) > 3): ?>
                        <small class="text-muted" style="font-size: 0.65rem;">+<?= count($dayEvents) - 3 ?> lainnya</small>
                        <?php endif; ?>
                    </td>
                    <?php
                            $currentDay++;
                        endif;
                        if ($weekday === 6 && $i < $totalCells - 1):
                            echo '</tr><tr>';
                        endif;
                    endfor;
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Event List for This Month -->
    <div class="mt-4">
        <h5><i class="fas fa-list me-2"></i>Event di <?= $monthNames[$month] . ' ' . $year ?></h5>
        <?php if (empty($events)): ?>
        <div class="text-center py-4">
            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
            <p class="text-muted">Tidak ada event pada bulan ini</p>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($events as $ev): ?>
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge bg-<?= $categoryColors[$ev['category']] ?? 'primary' ?>">
                                <?= View::e($ev['category_name'] ?? $ev['category']) ?>
                            </span>
                            <small class="text-muted">
                                <i class="fas fa-calendar me-1"></i><?= date('d M', strtotime($ev['start_date'])) ?>
                            </small>
                        </div>
                        <h6 class="card-title"><?= View::e($ev['title']) ?></h6>
                        <p class="small text-muted mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i><?= View::e($ev['location_name'] ?? '-') ?>
                        </p>
                        <a href="<?= View::url('events/detail/' . $ev['id']) ?>" class="btn btn-sm btn-outline-primary w-100">
                            Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="text-center mt-3">
        <a href="<?= View::url('events') ?>" class="btn btn-link">
            <i class="fas fa-arrow-left me-1"></i>Kembali ke Daftar Event
        </a>
    </div>
</div>

<style>
.calendar-table td {
    height: 100px;
    vertical-align: top;
}
.calendar-table .bg-light {
    background-color: #f0f7ff !important;
}
</style>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
