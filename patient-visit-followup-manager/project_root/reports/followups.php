<?php
// healthcare_system/reports/followups.php
$page_title = "Follow-up Report";
require_once '../config/db.php';
checkLogin();

$sql = "
SELECT 
    p.name,
    p.phone,
    v.visit_date,
    v.follow_up_due,
    DATEDIFF(v.follow_up_due, CURDATE()) AS days_diff,
    CASE 
        WHEN v.follow_up_due < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(v.follow_up_due, CURDATE()) <= 7 THEN 'Upcoming'
        ELSE 'Future'
    END AS status
FROM visits v
JOIN patients p ON v.patient_id = p.patient_id
WHERE v.follow_up_due IS NOT NULL
ORDER BY v.follow_up_due
";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>📋 Follow-up Report</h2>
    <p>Overview of all scheduled follow-ups</p>
    
    <div style="display: flex; gap: 15px; margin-bottom: 20px;">
        <a href="?filter=overdue" class="btn btn-danger">Overdue</a>
        <a href="?filter=upcoming" class="btn btn-success">Upcoming (7 days)</a>
        <a href="?filter=all" class="btn">All Follow-ups</a>
    </div>
    
    <table>
        <tr>
            <th>Patient Name</th>
            <th>Phone</th>
            <th>Visit Date</th>
            <th>Follow-up Due</th>
            <th>Days Difference</th>
            <th>Status</th>
        </tr>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo $row['phone']; ?></td>
                <td><?php echo $row['visit_date']; ?></td>
                <td><?php echo $row['follow_up_due']; ?></td>
                <td>
                    <?php if($row['days_diff'] < 0): ?>
                        <span class="status-overdue"><?php echo abs($row['days_diff']); ?> days ago</span>
                    <?php else: ?>
                        <?php echo $row['days_diff']; ?> days
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($row['status'] == 'Overdue'): ?>
                        <span class="status-overdue">⚠️ <?php echo $row['status']; ?></span>
                    <?php elseif($row['status'] == 'Upcoming'): ?>
                        <span class="status-upcoming">⏰ <?php echo $row['status']; ?></span>
                    <?php else: ?>
                        <?php echo $row['status']; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align: center; padding: 30px;">
                    No follow-up data available.
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>