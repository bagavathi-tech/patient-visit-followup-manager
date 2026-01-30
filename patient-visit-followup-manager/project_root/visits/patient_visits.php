<?php
// healthcare_system/visits/patient_visits.php
$page_title = "Patient Visits";
require_once '../config/db.php';
checkLogin();

if (!isset($_GET['patient_id']) || !is_numeric($_GET['patient_id'])) {
    die("Invalid patient ID");
}

$patient_id = escape($_GET['patient_id']);

$patient_sql = "SELECT name FROM patients WHERE patient_id = $patient_id";
$patient_result = $conn->query($patient_sql);
if ($patient_result->num_rows == 0) {
    die("Patient not found!");
}
$patient = $patient_result->fetch_assoc();

$visits_sql = "
SELECT 
    v.*,
    DATEDIFF(CURDATE(), v.visit_date) AS days_since_visit,
    CASE 
        WHEN v.follow_up_due < CURDATE() THEN 'Overdue'
        WHEN DATEDIFF(v.follow_up_due, CURDATE()) <= 7 THEN 'Upcoming'
        ELSE 'Scheduled'
    END AS follow_up_status,
    DATEDIFF(v.follow_up_due, CURDATE()) AS days_to_followup
FROM visits v
WHERE v.patient_id = $patient_id
ORDER BY v.visit_date DESC
";

$visits_result = $conn->query($visits_sql);

$totals_sql = "SELECT 
                COUNT(*) as total_visits,
                SUM(consultation_fee + lab_fee) as total_fees,
                MIN(visit_date) as first_visit,
                MAX(visit_date) as last_visit
               FROM visits 
               WHERE patient_id = $patient_id";
$totals_result = $conn->query($totals_sql);
$totals = $totals_result->fetch_assoc();
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>
            <a href="../patients/view.php?id=<?php echo $patient_id; ?>" style="text-decoration: none;">
                <?php echo htmlspecialchars($patient['name']); ?>
            </a>
            - Visit History
        </h2>
        <div>
            <a href="add.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-success">
                + Add New Visit
            </a>
            <a href="../patients/view.php?id=<?php echo $patient_id; ?>" class="btn">← Back to Patient</a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px;">
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
            <strong>Total Visits</strong><br>
            <span style="font-size: 24px; font-weight: bold;"><?php echo $totals['total_visits']; ?></span>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #28a745;">
            <strong>Total Fees</strong><br>
            <span style="font-size: 24px; font-weight: bold;">₹<?php echo number_format($totals['total_fees'] ?? 0, 2); ?></span>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;">
            <strong>First Visit</strong><br>
            <?php echo $totals['first_visit'] ? date('d M Y', strtotime($totals['first_visit'])) : 'N/A'; ?>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #dc3545;">
            <strong>Last Visit</strong><br>
            <?php echo $totals['last_visit'] ? date('d M Y', strtotime($totals['last_visit'])) : 'N/A'; ?>
        </div>
    </div>
    
    <?php if($visits_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Visit ID</th>
                <th>Date</th>
                <th>Days Since</th>
                <th>Consultation</th>
                <th>Lab</th>
                <th>Total</th>
                <th>Follow-up Due</th>
                <th>Status</th>
                <th>Days to Follow-up</th>
            </tr>
            <?php while($visit = $visits_result->fetch_assoc()): 
                $total = $visit['consultation_fee'] + $visit['lab_fee'];
            ?>
            <tr>
                <td><?php echo $visit['visit_id']; ?></td>
                <td><?php echo $visit['visit_date']; ?></td>
                <td><?php echo $visit['days_since_visit']; ?> days</td>
                <td>₹<?php echo number_format($visit['consultation_fee'], 2); ?></td>
                <td>₹<?php echo number_format($visit['lab_fee'], 2); ?></td>
                <td><strong>₹<?php echo number_format($total, 2); ?></strong></td>
                <td><?php echo $visit['follow_up_due']; ?></td>
                <td>
                    <?php if($visit['follow_up_status'] == 'Overdue'): ?>
                        <span class="status-overdue">⚠️ <?php echo $visit['follow_up_status']; ?></span>
                    <?php elseif($visit['follow_up_status'] == 'Upcoming'): ?>
                        <span class="status-upcoming">⏰ <?php echo $visit['follow_up_status']; ?></span>
                    <?php else: ?>
                        <?php echo $visit['follow_up_status']; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($visit['follow_up_status'] == 'Overdue'): ?>
                        <span class="status-overdue"><?php echo abs($visit['days_to_followup']); ?> days ago</span>
                    <?php elseif($visit['follow_up_status'] == 'Upcoming'): ?>
                        <span class="status-upcoming">in <?php echo $visit['days_to_followup']; ?> days</span>
                    <?php else: ?>
                        <?php echo $visit['days_to_followup']; ?> days
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <div style="text-align: center; padding: 40px; color: #666;">
            <p style="font-size: 18px;">No visits recorded for this patient yet.</p>
            <a href="add.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-success" style="margin-top: 15px;">
                + Record First Visit
            </a>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>