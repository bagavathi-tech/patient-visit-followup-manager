<?php
$page_title = "Charts & Analytics";
require_once '../config/db.php';
checkLogin();

/* =========================
   Chart 1: Patients Joined by Month
   ========================= */
$patients_sql = "
SELECT 
    DATE_FORMAT(join_date, '%b %Y') AS month,
    COUNT(*) AS total
FROM patients
GROUP BY DATE_FORMAT(join_date, '%Y-%m')
ORDER BY DATE_FORMAT(join_date, '%Y-%m')
";

$patients_result = $conn->query($patients_sql);

$patient_months = [];
$patient_counts = [];

while ($row = $patients_result->fetch_assoc()) {
    $patient_months[] = $row['month'];
    $patient_counts[] = $row['total'];
}

/* =========================
   Chart 2: Visits Per Month
   ========================= */
$visits_sql = "
SELECT 
    DATE_FORMAT(visit_date, '%b %Y') AS month,
    COUNT(*) AS total
FROM visits
GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
ORDER BY DATE_FORMAT(visit_date, '%Y-%m')
";

$visits_result = $conn->query($visits_sql);

$visit_months = [];
$visit_counts = [];

while ($row = $visits_result->fetch_assoc()) {
    $visit_months[] = $row['month'];
    $visit_counts[] = $row['total'];
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>📊 Charts & Analytics</h2>
    <p>Visual representation of patient and visit statistics</p>
</div>

<div class="card">
    <h3>👥 Patients Joined by Month</h3>
    <canvas id="patientsChart" height="120"></canvas>
</div>

<div class="card">
    <h3>🏥 Visits Per Month</h3>
    <canvas id="visitsChart" height="120"></canvas>
</div>

<script>
// Patients Joined Chart
new Chart(document.getElementById('patientsChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($patient_months); ?>,
        datasets: [{
            label: 'Patients Joined',
            data: <?php echo json_encode($patient_counts); ?>,
            backgroundColor: '#4e73df'
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});

// Visits Chart
new Chart(document.getElementById('visitsChart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($visit_months); ?>,
        datasets: [{
            label: 'Visits',
            data: <?php echo json_encode($visit_counts); ?>,
            borderColor: '#1cc88a',
            backgroundColor: 'rgba(28, 200, 138, 0.2)',
            fill: true,
            tension: 0.3
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true }
        }
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>