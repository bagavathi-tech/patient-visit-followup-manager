<?php
$page_title = "Patients with No Visits";
require_once '../config/db.php';
checkLogin();

$sql = "
SELECT 
    p.patient_id,
    p.name,
    p.phone,
    TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age,
    DATEDIFF(CURDATE(), p.join_date) AS days_since_join
FROM patients p
LEFT JOIN visits v ON p.patient_id = v.patient_id
WHERE v.visit_id IS NULL
ORDER BY p.join_date DESC
";

$result = $conn->query($sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>🚨 Patients with No Visits</h2>
    <p>Patients who registered but never visited</p>

    <?php if ($result->num_rows > 0): ?>
        <div class="alert alert-danger">
            Found <?php echo $result->num_rows; ?> patient(s) with no visits
        </div>

        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Age</th>
                <th>Phone</th>
                <th>Days Since Join</th>
                <th>Actions</th>
            </tr>

            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['patient_id']; ?></td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo $row['age']; ?> years</td>
                <td><?php echo $row['phone']; ?></td>
                <td>
                    <?php echo $row['days_since_join']; ?> days
                    <?php if ($row['days_since_join'] > 90): ?>
                        <br><span class="status-overdue">⚠️ Never visited</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="../patients/view.php?id=<?php echo $row['patient_id']; ?>" class="btn">View</a>
                    <a href="../visits/add.php?patient_id=<?php echo $row['patient_id']; ?>" class="btn btn-success">
                        Add Visit
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

    <?php else: ?>
        <div class="alert alert-success">
            🎉 All patients have at least one visit.
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>