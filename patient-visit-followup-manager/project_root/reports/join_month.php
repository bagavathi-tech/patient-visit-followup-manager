<?php
$page_title = "Patients Joined by Month";
require_once '../config/db.php';
checkLogin();

$sql = "
SELECT 
    DATE_FORMAT(join_date, '%Y-%m') AS join_month,
    DATE_FORMAT(join_date, '%M %Y') AS month_name,
    COUNT(*) AS patients_joined,
    GROUP_CONCAT(name SEPARATOR ', ') AS patient_names
FROM patients
GROUP BY DATE_FORMAT(join_date, '%Y-%m')
ORDER BY join_month DESC
";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h3>Patients Joined by Month</h3>

    <table>
        <tr>
            <th>Month</th>
            <th>Patients Joined</th>
            <th>Patient Names</th>
            <th>View</th>
        </tr>

        <?php while($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo $row['month_name']; ?></td>
            <td><strong><?php echo $row['patients_joined']; ?></strong></td>
            <td><?php echo htmlspecialchars($row['patient_names']); ?></td>
            <td>
                <a href="../patients/list.php?month=<?php echo $row['join_month']; ?>" class="btn">
                    View
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>