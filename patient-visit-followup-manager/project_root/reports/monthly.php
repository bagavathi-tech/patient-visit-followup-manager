<?php
// healthcare_system/reports/monthly.php
$page_title = "Monthly Report";
require_once '../config/db.php';
checkLogin();

$sql = "
SELECT 
    DATE_FORMAT(visit_date, '%Y-%m') AS month,
    COUNT(*) AS visit_count,
    COUNT(DISTINCT patient_id) AS patient_count,
    SUM(consultation_fee + lab_fee) AS total_revenue
FROM visits
WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
GROUP BY DATE_FORMAT(visit_date, '%Y-%m')
ORDER BY month DESC
";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>📅 Monthly Statistics Report (Last 6 Months)</h2>
    <p>Monthly visit and revenue analysis</p>
    
    <table>
        <tr>
            <th>Month</th>
            <th>Total Visits</th>
            <th>Unique Patients</th>
            <th>Total Revenue</th>
            <th>Average per Visit</th>
        </tr>
        <?php if($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): 
                $avg_revenue = $row['visit_count'] > 0 ? $row['total_revenue'] / $row['visit_count'] : 0;
            ?>
            <tr>
                <td><?php echo $row['month']; ?></td>
                <td><?php echo $row['visit_count']; ?></td>
                <td><?php echo $row['patient_count']; ?></td>
                <td>₹<?php echo number_format($row['total_revenue'], 2); ?></td>
                <td>₹<?php echo number_format($avg_revenue, 2); ?></td>
            </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center; padding: 30px;">
                    No monthly data available.
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<div class="card">
    <h3>Additional Monthly Statistics</h3>
    <?php
    // Patients joined per month
    $patients_sql = "
    SELECT 
        DATE_FORMAT(join_date, '%Y-%m') AS month,
        COUNT(*) AS patients_joined
    FROM patients
    WHERE join_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(join_date, '%Y-%m')
    ORDER BY month DESC
    ";
    
    $patients_result = $conn->query($patients_sql);
    ?>
    
    <table>
        <tr>
            <th>Month</th>
            <th>Patients Joined</th>
            <th>Cumulative Patients</th>
        </tr>
        <?php 
        $cumulative = 0;
        if($patients_result->num_rows > 0):
            while($row = $patients_result->fetch_assoc()): 
                $cumulative += $row['patients_joined'];
        ?>
        <tr>
            <td><?php echo $row['month']; ?></td>
            <td><?php echo $row['patients_joined']; ?></td>
            <td><?php echo $cumulative; ?></td>
        </tr>
        <?php 
            endwhile;
        else: 
        ?>
        <tr>
            <td colspan="3" style="text-align: center; padding: 20px;">
                No patient join data available.
            </td>
        </tr>
        <?php endif; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>