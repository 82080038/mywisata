<?php
/**
 * Weather Widget Partial
 * Uses Open-Meteo API (free, no API key required)
 * Usage: include with $latitude, $longitude, $cityName variables
 */
$latitude = $latitude ?? null;
$longitude = $longitude ?? null;
$cityName = $cityName ?? '';
?>

<?php if ($latitude && $longitude): ?>
<div class="card mb-4 weather-widget">
    <div class="card-header bg-info text-white">
        <h5 class="card-title mb-0">
            <i class="fas fa-cloud-sun me-2"></i>Cuaca <?= View::e($cityName) ?>
        </h5>
    </div>
    <div class="card-body" id="weatherBody">
        <div class="text-center py-3">
            <div class="spinner-border spinner-border-sm text-info" role="status"></div>
            <p class="small text-muted mt-2 mb-0">Memuat data cuaca...</p>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    var lat = <?= (float)$latitude ?>;
    var lon = <?= (float)$longitude ?>;
    
    $.getJSON('https://api.open-meteo.com/v1/forecast?latitude=' + lat + '&longitude=' + lon + '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&daily=weather_code,temperature_2m_max,temperature_2m_min&timezone=Asia/Jakarta&forecast_days=5', function(data) {
        var w = data.current;
        var d = data.daily;
        
        var weatherIcons = {
            0: 'fa-sun', 1: 'fa-cloud-sun', 2: 'fa-cloud', 3: 'fa-cloud',
            45: 'fa-smog', 48: 'fa-smog',
            51: 'fa-cloud-rain', 53: 'fa-cloud-rain', 55: 'fa-cloud-rain',
            56: 'fa-cloud-rain', 57: 'fa-cloud-rain',
            61: 'fa-cloud-showers-heavy', 63: 'fa-cloud-showers-heavy', 65: 'fa-cloud-showers-heavy',
            66: 'fa-cloud-rain', 67: 'fa-cloud-rain',
            71: 'fa-snowflake', 73: 'fa-snowflake', 75: 'fa-snowflake',
            77: 'fa-snowflake',
            80: 'fa-cloud-showers-heavy', 81: 'fa-cloud-showers-heavy', 82: 'fa-cloud-showers-heavy',
            85: 'fa-snowflake', 86: 'fa-snowflake',
            95: 'fa-bolt', 96: 'fa-bolt', 99: 'fa-bolt'
        };
        
        var weatherLabels = {
            0: 'Cerah', 1: 'Berawan Sebagian', 2: 'Berawan', 3: 'Mendung',
            45: 'Berkabut', 48: 'Berkabut',
            51: 'Gerimis', 53: 'Gerimis', 55: 'Gerimis',
            56: 'Gerimis Membeku', 57: 'Gerimis Membeku',
            61: 'Hujan', 63: 'Hujan', 65: 'Hujan Lebat',
            66: 'Hujan Membeku', 67: 'Hujan Membeku',
            71: 'Salju', 73: 'Salju', 75: 'Salju Lebat',
            77: 'Butiran Salju',
            80: 'Hujan', 81: 'Hujan', 82: 'Hujan Lebat',
            85: 'Salju', 86: 'Salju Lebat',
            95: 'Badai Petir', 96: 'Badai Petir', 99: 'Badai Petir'
        };
        
        var icon = weatherIcons[w.weather_code] || 'fa-cloud';
        var label = weatherLabels[w.weather_code] || 'Tidak Diketahui';
        
        var html = '<div class="text-center mb-3">';
        html += '<i class="fas ' + icon + ' fa-3x text-info mb-2"></i>';
        html += '<h3 class="mb-0">' + Math.round(w.temperature_2m) + '°C</h3>';
        html += '<p class="text-muted small mb-1">' + label + '</p>';
        html += '<div class="d-flex justify-content-center gap-3 small text-muted">';
        html += '<span><i class="fas fa-tint me-1"></i>' + w.relative_humidity_2m + '%</span>';
        html += '<span><i class="fas fa-wind me-1"></i>' + Math.round(w.wind_speed_10m) + ' km/h</span>';
        html += '</div></div>';
        
        // 5-day forecast
        html += '<div class="row text-center">';
        var dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        for (var i = 0; i < d.time.length; i++) {
            var date = new Date(d.time[i]);
            var dayName = dayNames[date.getDay()];
            var dIcon = weatherIcons[d.weather_code[i]] || 'fa-cloud';
            html += '<div class="col">';
            html += '<small class="text-muted">' + dayName + '</small>';
            html += '<div><i class="fas ' + dIcon + ' text-info"></i></div>';
            html += '<small>' + Math.round(d.temperature_2m_max[i]) + '° / ' + Math.round(d.temperature_2m_min[i]) + '°</small>';
            html += '</div>';
        }
        html += '</div>';
        
        $('#weatherBody').html(html);
    }).fail(function() {
        $('#weatherBody').html('<p class="text-muted text-center small mb-0">Data cuaca tidak tersedia</p>');
    });
});
</script>
<?php endif; ?>
