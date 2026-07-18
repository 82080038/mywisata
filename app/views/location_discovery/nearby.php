<?php
/**
 * Location Discovery - Nearby Attractions View
 * 
 * @package MyWisata
 * @version 1.0.0
 * @since 2026-07-18
 */
?>
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Nearby Attractions</h1>
            <p class="lead">Discover attractions near your location</p>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                This feature requires geolocation access to find nearby attractions.
            </div>
            
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">How to use</h5>
                    <ol>
                        <li>Allow location access when prompted</li>
                        <li>Set your search radius (default: 5km)</li>
                        <li>Browse nearby attractions</li>
                    </ol>
                    
                    <button class="btn btn-primary mt-3" onclick="getLocation()">
                        <i class="fas fa-location-arrow me-2"></i>Find My Location
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition, showError);
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

function showPosition(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    
    // Load nearby attractions via AJAX
    fetch(`/location/nearby-attractions?lat=${lat}&lng=${lng}&radius=5`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Display attractions
                console.log(data.attractions);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => console.error('Error:', error));
}

function showError(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            alert('User denied the request for Geolocation.');
            break;
        case error.POSITION_UNAVAILABLE:
            alert('Location information is unavailable.');
            break;
        case error.TIMEOUT:
            alert('The request to get user location timed out.');
            break;
        case error.UNKNOWN_ERROR:
            alert('An unknown error occurred.');
            break;
    }
}
</script>
