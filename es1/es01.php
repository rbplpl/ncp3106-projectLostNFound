<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Dashboard</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f9;
        }
        h2 {
            text-align: center;
        }
        .container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            margin: 20px 0;
        }
        .box {
            border: 2px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            flex: 1;
            min-width: 300px;
            max-width: 48%;
        }
        .box h3 {
            text-align: center;
            margin-bottom: 15px;
        }
        #map {
            height: 300px;
        }
        canvas {
            max-width: 100%;
        }
        p {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h2>Real-Time Sensor Dashboard</h2>

    <div class="container">
        <!-- Box for Sound Sensor -->
        <div class="box">
            <h3>Sound Sensor</h3>
            <canvas id="soundChart"></canvas>
        </div>

        <!-- Box for GPS Tracking -->
        <div class="box">
            <h3>GPS Location</h3>
            <div id="map"></div>
        </div>
    </div>

    <div class="container">
        <!-- Box for Oxygen Level -->
        <div class="box">
            <h3>Oxygen Level</h3>
            <p id="oxygenLevel">Loading...</p>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.3/dist/leaflet.js"></script>
    <script>
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

        // Initialize Oxygen Chart
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

        // Function to update the map with the current location
        function updateMap(lat, lon) {
            // Initialize Leaflet Map centered on the current location
            const map = L.map('map').setView([lat, lon], 13); // Set view to current coordinates

            // Set up OpenStreetMap tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Create a marker for the GPS location
            const marker = L.marker([lat, lon]).addTo(map); // Marker at the current location
        }

        // Get the user's current position using the Geolocation API
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                // Update the map with the current location
                updateMap(lat, lon);
            }, function(error) {
                console.error('Geolocation error: ', error);
                // If geolocation fails, show default location (Paris)
                updateMap(48.8584, 2.2945); // Default to Paris, France
            });
        } else {
            console.error("Geolocation is not supported by this browser.");
            // If geolocation is not supported, show default location (Paris)
            updateMap(48.8584, 2.2945); // Default to Paris, France
        }

        function fetchData() {
            fetch('dashboard_es1.php')
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

        // Fetch new data every 2 seconds
        setInterval(fetchData, 2000);

        
    </script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const toggleButton = document.getElementById("toggle-mode");

    toggleButton.addEventListener("click", function () {
        document.body.classList.toggle("dark-mode");
        toggleButton.textContent = document.body.classList.contains("dark-mode")
            ? "☀️ Light Mode"
            : "🌙 Dark Mode";
    });
});
</script>

<button id="toggle-mode">☀️ Light Mode</button>
<body class="dark-mode">

</body>
</html>
