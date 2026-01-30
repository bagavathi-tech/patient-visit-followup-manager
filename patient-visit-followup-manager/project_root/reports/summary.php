<?php
// healthcare_system/reports/summary.php
$page_title = "Summary Report";
require_once '../config/db.php';
checkLogin();

$sql = "
SELECT 
    p.patient_id,
    p.name,
    p.dob,
    TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age,
    COUNT(v.visit_id) AS total_visits,
    MAX(v.visit_date) AS last_visit_date,
    DATEDIFF(CURDATE(), MAX(v.visit_date)) AS days_since_last_visit,
    MIN(v.follow_up_due) AS next_follow_up,
    CASE 
        WHEN MIN(v.follow_up_due) < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(MIN(v.follow_up_due), CURDATE()) <= 7 THEN 'Upcoming'
        ELSE 'Scheduled'
    END AS follow_up_status
FROM patients p
LEFT JOIN visits v ON p.patient_id = v.patient_id
GROUP BY p.patient_id
ORDER BY p.name
";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>📊 Full Patient Summary Report</h2>
    <p>Complete overview of all patients with calculated statistics</p>
    
    <table>
        <tr>
            <th>Name</th>
            <th>Age</th>
            <th>Total Visits</th>
            <th>Last Visit Date</th>
            <th>Days Since Last Visit</th>
            <th>Next Follow-up</th>
            <th>Status</th>
        </tr>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td>
                    <a href="../patients/view.php?id=<?php echo $row['patient_id']; ?>">
                        <?php echo htmlspecialchars($row['name']); ?>
                    </a>
                </td>
                <td><?php echo $row['age']; ?> years</td>
                <td><?php echo $row['total_visits']; ?></td>
                <td>
                    <?php 
                    if($row['last_visit_date']) {
                        echo $row['last_visit_date'];
                    } else {
                        echo 'No visits';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if($row['last_visit_date']) {
                        echo $row['days_since_last_visit'] . ' days';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if($row['next_follow_up']) {
                        echo $row['next_follow_up'];
                    } else {
                        echo 'No follow-up';
                    }
                    ?>
                </td>
                <td>
                    <?php if($row['follow_up_status'] == 'Overdue'): ?>
                        <span class="status-overdue">⚠️ <?php echo $row['follow_up_status']; ?></span>
                    <?php elseif($row['follow_up_status'] == 'Upcoming'): ?>
                        <span class="status-upcoming">⏰ <?php echo $row['follow_up_status']; ?></span>
                    <?php else: ?>
                        <?php echo $row['follow_up_status']; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" style="text-align: center; padding: 30px;">
                    No patient data available.
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>