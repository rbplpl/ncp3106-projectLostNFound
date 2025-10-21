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
            // Sound Chart
            soundChart.data.labels.push(data.timestamp);
            soundChart.data.datasets[0].data.push(data.sound);
            if (soundChart.data.labels.length > 20) {
                soundChart.data.labels.shift();
                soundChart.data.datasets[0].data.shift();
            }
            soundChart.update();

            // Oxygen Chart
            oxygenChart.data.labels.push(data.timestamp);
            oxygenChart.data.datasets[0].data.push(data.oxygen);
            if (oxygenChart.data.labels.length > 20) {
                oxygenChart.data.labels.shift();
                oxygenChart.data.datasets[0].data.shift();
            }
            oxygenChart.update();
        });
}

setInterval(fetchData, 2000);
