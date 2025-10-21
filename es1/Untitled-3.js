// Initialize Sound Chart
const soundCtx = document.getElementById('soundChart').getContext('2d');
const soundChart = new Chart(soundCtx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Sound Level (dB)',
            data: [],
            borderColor: 'blue',
            fill: false
        }]
    }
});

// Initialize Oxygen Chart (optional)
const oxygenCtx = document.getElementById('oxygenChart') ? document.getElementById('oxygenChart').getContext('2d') : null;
const oxygenChart = oxygenCtx ? new Chart(oxygenCtx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
            label: 'Oxygen Level (%)',
            data: [],
            borderColor: 'green',
            fill: false
        }]
    }
}) : null;

function updateMap(lat, lon) {
    const map = L.map('map').setView([lat, lon], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    const marker = L.marker([lat, lon]).addTo(map);
}

if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        position => {
            const lat = position.coords.latitude;
            const lon = position.coords.longitude;
            updateMap(lat, lon);
        },
        error => {
            console.error('Geolocation error:', error);
            updateMap(48.8584, 2.2945); // Paris fallback
        }
    );
} else {
    console.error("Geolocation is not supported by this browser.");
    updateMap(48.8584, 2.2945);
}

function fetchData() {
    fetch('get_data.php')
        .then(res => res.json())
        .then(data => {
            // Update Sound Chart
            soundChart.data.labels.push(data.timestamp);
            soundChart.data.datasets[0].data.push(data.sound);
            if (soundChart.data.labels.length > 20) {
                soundChart.data.labels.shift();
                soundChart.data.datasets[0].data.shift();
            }
            soundChart.update();

            // Update Oxygen Chart
            if (oxygenChart) {
                oxygenChart.data.labels.push(data.timestamp);
                oxygenChart.data.datasets[0].data.push(data.oxygen);
                if (oxygenChart.data.labels.length > 20) {
                    oxygenChart.data.labels.shift();
                    oxygenChart.data.datasets[0].data.shift();
                }
                oxygenChart.update();
            }

            // Display Oxygen Level
            document.getElementById('oxygenLevel').textContent = data.oxygen + '%';
        });
}

setInterval(fetchData, 2000);
