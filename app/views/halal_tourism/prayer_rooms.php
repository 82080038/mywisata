<?php include APP_ROOT . '/app/views/layouts/header.php'; ?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1 class="mb-3"><i class="fas fa-pray me-2"></i>Prayer Room Terdekat</h1>
            <p class="text-muted">Temukan prayer room terdekat dari lokasi Anda</p>
        </div>
    </div>
    
    <div class="card glass-card">
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="search-location" placeholder="Cari lokasi...">
                        <label for="search-location">Cari lokasi...</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-floating">
                        <select class="form-select" id="radius">
                            <option value="1">1 km</option>
                            <option value="5" selected>5 km</option>
                            <option value="10">10 km</option>
                            <option value="20">20 km</option>
                        </select>
                        <label for="radius">Radius</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <button onclick="searchPrayerRooms()" class="btn btn-primary btn-modern w-100 h-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </div>
            
            <div id="prayer-rooms-list" class="mt-3">
                <div class="text-center py-5">
                    <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Klik "Cari" untuk menemukan prayer room terdekat</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let userLat = null;
let userLng = null;

function searchPrayerRooms() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;
                loadPrayerRooms();
            },
            function(error) {
                alert("Gagal mendapatkan lokasi: " + error.message);
            }
        );
    } else {
        alert("Geolocation tidak didukung oleh browser ini");
    }
}

function loadPrayerRooms() {
    const radius = document.getElementById('radius').value;
    
    fetch(`<?= View::url('halal-tourism/prayer-rooms') ?>?lat=${userLat}&lng=${userLng}&radius=${radius}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                displayPrayerRooms(data.data);
            } else {
                alert('Gagal memuat prayer rooms: ' + data.message);
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error.message);
        });
}

function displayPrayerRooms(rooms) {
    let html = '';
    
    if (rooms.length === 0) {
        html = `
            <div class="text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <p class="text-muted">Tidak ada prayer room ditemukan dalam radius yang dipilih</p>
            </div>
        `;
    } else {
        html = '<div class="list-group">';
        rooms.forEach(room => {
            html += `
                <a href="#" class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1">${room.name}</h5>
                        <small class="text-primary fw-bold">${room.distance_km.toFixed(2)} km</small>
                    </div>
                    <p class="mb-1 small text-muted">${room.address}</p>
                    <div class="small">
                        <span class="badge bg-info me-1">${room.facilities}</span>
                        <span class="badge bg-success">${room.capacity} orang</span>
                    </div>
                </a>
            `;
        });
        html += '</div>';
    }
    
    document.getElementById('prayer-rooms-list').innerHTML = html;
}
</script>

<?php include APP_ROOT . '/app/views/layouts/footer.php'; ?>
