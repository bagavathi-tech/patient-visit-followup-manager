<?php
// healthcare_system/visits/list.php
$page_title = "Visit List";
require_once '../config/db.php';
checkLogin();

$sql = "
SELECT 
    v.visit_id,
    v.visit_date,
    v.consultation_fee,
    v.lab_fee,
    v.follow_up_due,
    p.patient_id,
    p.name as patient_name,
    DATEDIFF(CURDATE(), v.visit_date) AS days_since_visit,
    CASE 
        WHEN v.follow_up_due < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(v.follow_up_due, CURDATE()) <= 7 THEN 'Upcoming'
        ELSE 'Scheduled'
    END AS follow_up_status,
    DATEDIFF(v.follow_up_due, CURDATE()) AS days_to_followup
FROM visits v
JOIN patients p ON v.patient_id = p.patient_id
ORDER BY v.visit_date DESC
";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Visit Records</h2>
        <a href="add.php" class="btn btn-success">📝 Record New Visit</a>
    </div>

    <table>
        <tr>
            <th>Visit ID</th>
            <th>Patient</th>
            <th>Visit Date</th>
            <th>Days Since</th>
            <th>Consultation Fee</th>
            <th>Lab Fee</th>
            <th>Follow-up Due</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['visit_id']; ?></td>
                <td>
                    <a href="../patients/view.php?id=<?php echo $row['patient_id']; ?>">
                        <?php echo htmlspecialchars($row['patient_name']); ?>
                    </a>
                </td>
                <td><?php echo $row['visit_date']; ?></td>
                <td><?php echo $row['days_since_visit']; ?> days</td>
                <td>₹<?php echo number_format($row['consultation_fee'], 2); ?></td>
                <td>₹<?php echo number_format($row['lab_fee'], 2); ?></td>
                <td><?php echo $row['follow_up_due']; ?></td>
                <td>
                    <?php if($row['follow_up_status'] == 'Overdue'): ?>
                        <span class="status-overdue">⚠️ <?php echo $row['follow_up_status']; ?></span>
                    <?php elseif($row['follow_up_status'] == 'Upcoming'): ?>
                        <span class="status-upcoming">⏰ <?php echo $row['follow_up_status']; ?></span>
                        <br><small>(in <?php echo $row['days_to_followup']; ?> days)</small>
                    <?php else: ?>
                        <?php echo $row['follow_up_status']; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="patient_visits.php?patient_id=<?php echo $row['patient_id']; ?>" class="btn">
                        View All
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" style="text-align: center; padding: 30px;">
                    No visits recorded. <a href="add.php">Record first visit</a>
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>