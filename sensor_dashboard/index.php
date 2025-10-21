<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MineGuard - Miner Safety Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background-color: #2c2c2c;
        color: #f4f4f9;
    }
    h2 {
        text-align: center;
        color: #ffcc00;
        text-shadow: 0px 0px 8px #ffcc00;
    }
    .container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 20px;
        margin: 20px 0;
    }
    .box {
        border: 2px solid #7a7a7a;
        background-color: #3b3b3b;
        color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.5);
        flex: 1;
        min-width: 280px;
        max-width: 48%;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .box h3 {
        text-align: center;
        color: #ffcc00;
    }
    #map {
        height: 100%;
        width: 100%;
    }
    canvas {
        max-width: 100%;
        height: auto;
    }
    p {
        text-align: center;
        font-size: 18px;
        font-weight: bold;
    }
    video {
    width: 90%;
    height: 90%;
    object-fit: cover; /* Ensures the video fills the box */
    border-radius: 8px;
    transform: scaleX(-1); /* Horizontally flip the video */
}


    /* Responsive Design */
    @media screen and (max-width: 768px) {
        .container {
            flex-direction: column;
        }
        .box {
            max-width: 100%;
        }
    }
</style>

</head>
<body>
    <h2>⛏️ MineGuard - Miner Safety Dashboard</h2>

    <div class="container">
        <!-- Box for Sound Sensor -->
        <div class="box">
            <h3>🚧 Sound Sensor</h3>
            <canvas id="soundChart"></canvas>
        </div>

        <!-- Box for GPS Tracking -->
        <div class="box">
            <h3>🗺️ GPS Location</h3>
            <div id="map"></div>
        </div>

        <!-- Box for Live Camera Feed -->
        <div class="box">
            <h3>📹 Live Camera</h3>

            <video id="liveVideo" autoplay playsinline></video>
        </div>
    </div>

    <div class="container">
        <!-- Box for Oxygen Level -->
        <div class="box">
            <h3>💨 Oxygen Level</h3>
            <p id="oxygenLevel"></p>
            <canvas id="oxygenChart"></canvas>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
        function updateMap(lat, lon) {
            const map = L.map('map').setView([lat, lon], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([lat, lon]).addTo(map);
        }

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                updateMap(position.coords.latitude, position.coords.longitude);
            }, function() {
                updateMap(48.8584, 2.2945); // Default to Paris if geolocation fails
            });
        } else {
            updateMap(48.8584, 2.2945);
        }

        // Initialize Oxygen Level Chart
        const oxygenCtx = document.getElementById('oxygenChart').getContext('2d');
        const oxygenChart = new Chart(oxygenCtx, {
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
        });

        function fetchData() {
            fetch('get_data.php')
                .then(res => res.json())
                .then(data => {
                    // Update Oxygen Chart
                    oxygenChart.data.labels.push(data.timestamp);
                    oxygenChart.data.datasets[0].data.push(data.oxygen);
                    if (oxygenChart.data.labels.length > 20) {
                        oxygenChart.data.labels.shift();
                        oxygenChart.data.datasets[0].data.shift();
                    }
                    oxygenChart.update();

                    // Display Oxygen Level
                    document.getElementById('oxygenLevel').textContent = data.oxygen + '%';
                });
        }

        setInterval(fetchData, 2000);

        // Enable Live Video Feed from Laptop Camera
        navigator.mediaDevices.getUserMedia({ video: true })
            .then(function(stream) {
                document.getElementById("liveVideo").srcObject = stream;
            })
            .catch(function(err) {
                console.error("Error accessing camera: ", err);
            });

    </script>
</body>
</html>
