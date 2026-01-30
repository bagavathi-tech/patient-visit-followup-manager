<?php
$page_title = "View Patient";
require_once '../config/db.php';
checkLogin();

// Get patient ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid patient ID");
}

$patient_id = $conn->real_escape_string($_GET['id']);

// SQL with ALL CALCULATIONS (as per task)
$sql = "
SELECT 
    p.*,
    -- Age in years
    TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age_years,
    -- Age in years + months
    CONCAT(
        TIMESTAMPDIFF(YEAR, p.dob, CURDATE()), ' years ',
        MOD(TIMESTAMPDIFF(MONTH, p.dob, CURDATE()), 12), ' months'
    ) AS age_full,
    -- Days since join
    DATEDIFF(CURDATE(), p.join_date) AS days_since_join,
    -- Last visit details
    MAX(v.visit_date) AS last_visit_date,
    -- Days since last visit
    DATEDIFF(CURDATE(), MAX(v.visit_date)) AS days_since_last_visit,
    -- Next follow-up
    MIN(v.follow_up_due) AS next_follow_up,
    -- Follow-up overdue check
    CASE 
        WHEN MIN(v.follow_up_due) < CURDATE() THEN 'Yes'
        ELSE 'No'
    END AS is_overdue,
    -- Total visits
    COUNT(v.visit_id) AS total_visits,
    -- Total fees
    SUM(v.consultation_fee + v.lab_fee) AS total_fees
FROM patients p
LEFT JOIN visits v ON p.patient_id = v.patient_id
WHERE p.patient_id = $patient_id
GROUP BY p.patient_id
";

$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Patient not found!");
}

$patient = $result->fetch_assoc();

// Get patient's visits
$visits_sql = "SELECT * FROM visits 
               WHERE patient_id = $patient_id 
               ORDER BY visit_date DESC";
$visits_result = $conn->query($visits_sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Patient Details</h2>
        <div>
            <a href="edit.php?id=<?php echo $patient_id; ?>" class="btn">✏️ Edit</a>
            <a href="list.php" class="btn">← Back to List</a>
        </div>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
        <!-- Left Column: Personal Info -->
        <div>
            <h3>Personal Information</h3>
            <table style="width: 100%;">
                <tr>
                    <th style="width: 40%;">Field</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td>Patient ID</td>
                    <td><strong><?php echo $patient['patient_id']; ?></strong></td>
                </tr>
                <tr>
                    <td>Full Name</td>
                    <td><?php echo htmlspecialchars($patient['name']); ?></td>
                </tr>
                <tr>
                    <td>Date of Birth</td>
                    <td><?php echo date('d M Y', strtotime($patient['dob'])); ?></td>
                </tr>
                <tr>
                    <td>Age</td>
                    <td>
                        <strong><?php echo $patient['age_years']; ?> years</strong><br>
                        <small><?php echo $patient['age_full']; ?></small>
                    </td>
                </tr>
                <tr>
                    <td>Phone</td>
                    <td><?php echo $patient['phone'] ?: 'Not provided'; ?></td>
                </tr>
                <tr>
                    <td>Address</td>
                    <td><?php echo nl2br(htmlspecialchars($patient['address'])); ?></td>
                </tr>
                <tr>
                    <td>Join Date</td>
                    <td>
                        <?php echo date('d M Y', strtotime($patient['join_date'])); ?><br>
                        <small>(<?php echo $patient['days_since_join']; ?> days ago)</small>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Right Column: Medical Stats -->
        <div>
            <h3>Medical Statistics</h3>
            <table style="width: 100%;">
                <tr>
                    <th style="width: 40%;">Metric</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td>Total Visits</td>
                    <td><strong><?php echo $patient['total_visits']; ?></strong></td>
                </tr>
                <tr>
                    <td>Total Fees Paid</td>
                    <td><strong>₹<?php echo number_format($patient['total_fees'] ?? 0, 2); ?></strong></td>
                </tr>
                <tr>
                    <td>Last Visit</td>
                    <td>
                        <?php if($patient['last_visit_date']): ?>
                            <?php echo date('d M Y', strtotime($patient['last_visit_date'])); ?><br>
                            <small>(<?php echo $patient['days_since_last_visit']; ?> days ago)</small>
                        <?php else: ?>
                            No visits yet
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Next Follow-up</td>
                    <td>
                        <?php if($patient['next_follow_up']): ?>
                            <?php echo date('d M Y', strtotime($patient['next_follow_up'])); ?>
                            <?php if($patient['is_overdue'] == 'Yes'): ?>
                                <br><span class="status-overdue">⚠️ OVERDUE</span>
                            <?php endif; ?>
                        <?php else: ?>
                            No follow-up scheduled
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td>Days Since Last Visit</td>
                    <td>
                        <?php if($patient['last_visit_date']): ?>
                            <?php echo $patient['days_since_last_visit']; ?> days
                            <?php if($patient['days_since_last_visit'] > 180): ?>
                                <br><span class="status-overdue">⚠️ Inactive (180+ days)</span>
                            <?php endif; ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Visits History -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3>Visit History</h3>
        <a href="../visits/add.php?patient_id=<?php echo $patient_id; ?>" class="btn btn-success">
            + Add New Visit
        </a>
    </div>
    
    <?php if($visits_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Visit ID</th>
                <th>Date</th>
                <th>Consultation Fee</th>
                <th>Lab Fee</th>
                <th>Total</th>
                <th>Follow-up Due</th>
                <th>Status</th>
            </tr>
            <?php while($visit = $visits_result->fetch_assoc()): 
                $total_fee = $visit['consultation_fee'] + $visit['lab_fee'];
                $followup_status = '';
                if($visit['follow_up_due'] < date('Y-m-d')) {
                    $followup_status = '<span class="status-overdue">Overdue</span>';
                } elseif(date_diff(date_create($visit['follow_up_due']), date_create())->days <= 7) {
                    $followup_status = '<span class="status-upcoming">Upcoming</span>';
                } else {
                    $followup_status = 'Scheduled';
                }
            ?>
            <tr>
                <td><?php echo $visit['visit_id']; ?></td>
                <td><?php echo date('d M Y', strtotime($visit['visit_date'])); ?></td>
                <td>₹<?php echo number_format($visit['consultation_fee'], 2); ?></td>
                <td>₹<?php echo number_format($visit['lab_fee'], 2); ?></td>
                <td><strong>₹<?php echo number_format($total_fee, 2); ?></strong></td>
                <td><?php echo date('d M Y', strtotime($visit['follow_up_due'])); ?></td>
                <td><?php echo $followup_status; ?></td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #666;">
            No visits recorded for this patient yet.
        </p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>