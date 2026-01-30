<?php
$page_title = "Patient List";
require_once '../config/db.php';
checkLogin();

// Get filters
$month  = $_GET['month']  ?? '';
$search = $_GET['search'] ?? '';

// Main SQL
$sql = "
SELECT 
    p.patient_id,
    p.name,
    p.join_date,
    TIMESTAMPDIFF(YEAR, p.dob, CURDATE()) AS age_years,
    CONCAT(
        TIMESTAMPDIFF(YEAR, p.dob, CURDATE()), ' years ',
        MOD(TIMESTAMPDIFF(MONTH, p.dob, CURDATE()), 12), ' months'
    ) AS age_full,
    COUNT(v.visit_id) AS total_visits,
    MAX(v.visit_date) AS last_visit,
    DATEDIFF(CURDATE(), MAX(v.visit_date)) AS days_since_last
FROM patients p
LEFT JOIN visits v ON p.patient_id = v.patient_id
WHERE 1=1
";

// Month filter
if ($month !== '') {
    $sql .= " AND DATE_FORMAT(p.join_date, '%Y-%m') = '" . $conn->real_escape_string($month) . "'";
}

// Search filter
if ($search !== '') {
    $safe = $conn->real_escape_string($search);
    $sql .= " AND (p.name LIKE '%$safe%' OR p.phone LIKE '%$safe%')";
}

$sql .= " GROUP BY p.patient_id ORDER BY p.name";

$result = $conn->query($sql);

// Month dropdown data
$months_sql = "
SELECT DISTINCT 
    DATE_FORMAT(join_date, '%Y-%m') AS month_code,
    DATE_FORMAT(join_date, '%M %Y') AS month_name
FROM patients
ORDER BY month_code DESC
";
$months_result = $conn->query($months_sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center;">
        <h2>Patient List</h2>
        <a href="add.php" class="btn btn-success">➕ Add New Patient</a>
    </div>

    <!-- Filters -->
    <form method="GET" style="margin:15px 0; display:grid; grid-template-columns: 1fr 1fr auto; gap:20px;">
        <div>
            <label>Join Month</label>
            <select name="month">
                <option value="">All Months</option>
                <?php while($m = $months_result->fetch_assoc()): ?>
                    <option value="<?php echo $m['month_code']; ?>"
                        <?php echo ($month === $m['month_code']) ? 'selected' : ''; ?>>
                        <?php echo $m['month_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div>
            <label>Search</label>
            <input type="text" name="search" placeholder="Name or phone"
                   value="<?php echo htmlspecialchars($search); ?>">
        </div>

        <div style="align-self:end;">
            <button class="btn">Apply</button>
            <a href="list.php" class="btn">Clear</a>
        </div>
    </form>

    <?php if ($month !== ''): ?>
        <div class="alert alert-info">
            Showing patients joined in
            <strong><?php echo date('F Y', strtotime($month . '-01')); ?></strong>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Age</th>
            <th>Total Visits</th>
            <th>Last Visit</th>
            <th>Actions</th>
        </tr>

        <?php if ($result->num_rows > 0): ?>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['patient_id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['age_full']; ?></td>
                    <td><?php echo $row['total_visits']; ?></td>
                    <td>
                        <?php
                        if ($row['last_visit']) {
                            echo $row['last_visit'] . " (" . $row['days_since_last'] . " days ago)";
                            if ($row['days_since_last'] > 180) {
                                echo "<br><span class='status-overdue'>⚠️ Inactive (180+ days)</span>";
                            }
                        } else {
                            echo "<span style='color:orange;'>No visits</span>";
                        }
                        ?>
                    </td>
                    <td>
   <div class="action-buttons">
      <a href="view.php?id=<?php echo $row['patient_id']; ?>" class="btn">View</a>
      <a href="edit.php?id=<?php echo $row['patient_id']; ?>" class="btn">Edit</a>
      <a href="delete.php?id=<?php echo $row['patient_id']; ?>" class="btn btn-danger"
         onclick="return confirm('Are you sure?')">Delete</a>
   </div>
</td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; padding:20px;">
                    No patients found.
                </td>
            </tr>
        <?php endif; ?>
    </table>
</div>

<?php require_once '../includes/footer.php'; ?>