<?php
// healthcare_system/index.php
$page_title = "Dashboard";
require_once 'config/db.php';
checkLogin();

// Get statistics using SQL
$stats_sql = "
SELECT 
    (SELECT COUNT(*) FROM patients) as total_patients,
    (SELECT COUNT(*) FROM visits) as total_visits,
    (SELECT COUNT(*) FROM visits WHERE visit_date = CURDATE()) as today_visits,
    (SELECT COUNT(*) FROM visits WHERE follow_up_due < CURDATE()) as overdue_followups
";

$stats = $conn->query($stats_sql)->fetch_assoc();

// Get recent patients
$recent_patients = $conn->query("
    SELECT patient_id, name, phone, DATE_FORMAT(join_date, '%d %b %Y') as join_date 
    FROM patients 
    ORDER BY patient_id DESC 
    LIMIT 5
");

// Get upcoming follow-ups
$upcoming_followups = $conn->query("
    SELECT p.name, v.follow_up_due, DATEDIFF(v.follow_up_due, CURDATE()) as days_left
    FROM visits v
    JOIN patients p ON v.patient_id = p.patient_id
    WHERE v.follow_up_due >= CURDATE()
    ORDER BY v.follow_up_due
    LIMIT 5
");
?>

<?php require_once 'includes/header.php'; ?>

<div class="card">
    <h2>Dashboard Overview</h2>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 30px;">
        <div style="background: #e3f2fd; padding: 20px; border-radius: 8px; text-align: center;">
            <h3>👥 Total Patients</h3>
            <p style="font-size: 32px; font-weight: bold; color: #1976d2;"><?php echo $stats['total_patients']; ?></p>
        </div>
        
        <div style="background: #f3e5f5; padding: 20px; border-radius: 8px; text-align: center;">
            <h3>🏥 Total Visits</h3>
            <p style="font-size: 32px; font-weight: bold; color: #7b1fa2;"><?php echo $stats['total_visits']; ?></p>
        </div>
        
        <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; text-align: center;">
            <h3>📅 Today's Visits</h3>
            <p style="font-size: 32px; font-weight: bold; color: #388e3c;"><?php echo $stats['today_visits']; ?></p>
        </div>
        
        <div style="background: #ffebee; padding: 20px; border-radius: 8px; text-align: center;">
            <h3>⚠️ Overdue Follow-ups</h3>
            <p style="font-size: 32px; font-weight: bold; color: #d32f2f;"><?php echo $stats['overdue_followups']; ?></p>
        </div>
    </div>
</div>

<div class="card">
    <h3>Quick Actions</h3>
    <div style="display: flex; gap: 15px; margin-top: 20px;">
        <a href="patients/add.php" class="btn btn-success">➕ Add New Patient</a>
        <a href="visits/add.php" class="btn">📝 Record Visit</a>
        <a href="reports/followups.php" class="btn">📋 View Follow-ups</a>
        <a href="reports/birthdays.php" class="btn">🎂 Upcoming Birthdays</a>
        <a href="reports/join_month.php"class="btn"> 📅 Patients Joined by Month</a>
        <a href="reports/zero_visits.php"class="btn">🚨  zero visits</a>
        <a href="reports/charts.php"class="btn">📈 charts</a>

    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <!-- Recent Patients -->
    <div class="card">
        <h3>Recent Patients</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Join Date</th>
            </tr>
            <?php while($row = $recent_patients->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['patient_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['join_date']; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
    
    <!-- Upcoming Follow-ups -->
    <div class="card">
        <h3>Upcoming Follow-ups</h3>
        <table>
            <tr>
                <th>Patient</th>
                <th>Follow-up Date</th>
                <th>Days Left</th>
            </tr>
            <?php while($row = $upcoming_followups->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo $row['follow_up_due']; ?></td>
                <td>
                    <?php if($row['days_left'] == 0): ?>
                        <span class="status-upcoming">Today</span>
                    <?php else: ?>
                        <span class="status-upcoming"><?php echo $row['days_left']; ?> days</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>