<?php
// healthcare_system/reports/birthdays.php
$page_title = "Birthday Report";
require_once '../config/db.php';
checkLogin();

// Birthdays in next 30 days
$birthdays_sql = "
SELECT 
    name,
    dob,
    TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS current_age,
    TIMESTAMPDIFF(YEAR, dob, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)) AS next_age,
    DAYOFYEAR(dob) AS day_of_year
FROM patients
WHERE 
    DAYOFYEAR(dob) BETWEEN DAYOFYEAR(CURDATE()) AND DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 30 DAY))
    OR (DAYOFYEAR(dob) < DAYOFYEAR(CURDATE()) AND 
        DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 30 DAY)) > 365 AND
        DAYOFYEAR(dob) + 365 BETWEEN DAYOFYEAR(CURDATE()) AND DAYOFYEAR(DATE_ADD(CURDATE(), INTERVAL 30 DAY)) + 365)
ORDER BY DAYOFYEAR(dob)
";

$birthdays_result = $conn->query($birthdays_sql);

// Patients turning specific ages this year
$specific_ages_sql = "
SELECT 
    name,
    dob,
    TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS current_age,
    TIMESTAMPDIFF(YEAR, dob, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)) AS turning_age
FROM patients
WHERE TIMESTAMPDIFF(YEAR, dob, DATE_ADD(CURDATE(), INTERVAL 1 YEAR)) IN (40, 50, 60)
ORDER BY dob
";

$specific_ages_result = $conn->query($specific_ages_sql);
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>🎂 Birthday Report</h2>
    <p>Upcoming birthdays and milestone ages</p>
</div>

<div class="card">
    <h3>Birthdays in Next 30 Days</h3>
    
    <?php if($birthdays_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Patient Name</th>
                <th>Date of Birth</th>
                <th>Current Age</th>
                <th>Turning Age</th>
                <th>Birthday Date</th>
                <th>Days Until</th>
            </tr>
            <?php while($row = $birthdays_result->fetch_assoc()):
            $birthday_this_year = getBirthdayThisYear($row['dob']); 
                $birthday_this_year = date('Y') . '-' . date('m-d', strtotime($row['dob']));
                $days_until = ceil((strtotime($birthday_this_year) - time()) / (60 * 60 * 24));
                if ($days_until < 0) {
                    $birthday_this_year = (date('Y') + 1) . '-' . date('m-d', strtotime($row['dob']));
                    $days_until = ceil((strtotime($birthday_this_year) - time()) / (60 * 60 * 24));
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo date('d M Y', strtotime($row['dob'])); ?></td>
                <td><?php echo $row['current_age']; ?> years</td>
                <td><?php echo $row['next_age']; ?> years</td>
                <td><?php echo date('d M', strtotime($birthday_this_year)); ?></td>
                <td>
                    <?php if($days_until == 0): ?>
                        <span class="status-upcoming">🎉 Today!</span>
                    <?php else: ?>
                        <span class="status-upcoming">in <?php echo $days_until; ?> days</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #666;">
            No birthdays in the next 30 days.
        </p>
    <?php endif; ?>
</div>

<div class="card">
    <h3>Patients Turning Milestone Ages This Year</h3>
    
    <?php if($specific_ages_result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Patient Name</th>
                <th>Date of Birth</th>
                <th>Current Age</th>
                <th>Turning Age</th>
                <th>Milestone</th>
            </tr>
            <?php while($row = $specific_ages_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo date('d M Y', strtotime($row['dob'])); ?></td>
                <td><?php echo $row['current_age']; ?> years</td>
                <td><strong><?php echo $row['turning_age']; ?> years</strong></td>
                <td>
                    <?php 
                    $milestone = '';
                    switch($row['turning_age']) {
                        case 40: $milestone = '🎂 40th Birthday'; break;
                        case 50: $milestone = '🎉 50th Golden Jubilee'; break;
                        case 60: $milestone = '🏆 60th Diamond Jubilee'; break;
                        default: $milestone = 'Birthday';
                    }
                    echo $milestone;
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p style="text-align: center; padding: 20px; color: #666;">
            No patients turning 40, 50, or 60 this year.
        </p>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>