<?php
$page_title = "Edit Patient";
require_once '../config/db.php';
checkLogin();

// Get patient ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid patient ID");
}

$patient_id = $conn->real_escape_string($_GET['id']);

// Fetch existing patient data
$sql = "SELECT * FROM patients WHERE patient_id = $patient_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    die("Patient not found!");
}

$patient = $result->fetch_assoc();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $join_date = $conn->real_escape_string($_POST['join_date']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    
    // Validate DOB not future
    if ($dob > date('Y-m-d')) {
        $error = "Date of Birth cannot be in the future";
    } else {
        $update_sql = "UPDATE patients SET 
                        name = '$name',
                        dob = '$dob',
                        join_date = '$join_date',
                        phone = '$phone',
                        address = '$address'
                      WHERE patient_id = $patient_id";
        
        if ($conn->query($update_sql) === TRUE) {
            $success = "Patient updated successfully!";
            // Refresh patient data
            $result = $conn->query($sql);
            $patient = $result->fetch_assoc();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Edit Patient</h2>
        <div>
            <a href="view.php?id=<?php echo $patient_id; ?>" class="btn">← Back to View</a>
            <a href="list.php" class="btn">← Back to List</a>
        </div>
    </div>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" 
                   value="<?php echo htmlspecialchars($patient['name']); ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="dob">Date of Birth *</label>
                <input type="date" id="dob" name="dob" 
                       value="<?php echo $patient['dob']; ?>" 
                       max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="join_date">Join Date *</label>
                <input type="date" id="join_date" name="join_date" 
                       value="<?php echo $patient['join_date']; ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" 
                   value="<?php echo htmlspecialchars($patient['phone']); ?>">
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($patient['address']); ?></textarea>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button type="submit" class="btn btn-success">Update Patient</button>
            <button type="reset" class="btn">Reset</button>
            <a href="view.php?id=<?php echo $patient_id; ?>" class="btn">Cancel</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>Patient Statistics</h3>
    <?php
    // Get statistics for this patient
    $stats_sql = "
    SELECT 
        COUNT(*) as total_visits,
        MAX(visit_date) as last_visit,
        MIN(follow_up_due) as next_followup,
        SUM(consultation_fee + lab_fee) as total_fees
    FROM visits 
    WHERE patient_id = $patient_id";
    
    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result->fetch_assoc();
    ?>
    
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-top: 15px;">
        <div style="background: #e3f2fd; padding: 15px; border-radius: 5px;">
            <strong>Total Visits</strong><br>
            <?php echo $stats['total_visits']; ?>
        </div>
        <div style="background: #f3e5f5; padding: 15px; border-radius: 5px;">
            <strong>Last Visit</strong><br>
            <?php echo $stats['last_visit'] ?: 'Never'; ?>
        </div>
        <div style="background: #e8f5e9; padding: 15px; border-radius: 5px;">
            <strong>Next Follow-up</strong><br>
            <?php echo $stats['next_followup'] ?: 'None'; ?>
        </div>
        <div style="background: #fff3e0; padding: 15px; border-radius: 5px;">
            <strong>Total Fees</strong><br>
            ₹<?php echo number_format($stats['total_fees'] ?? 0, 2); ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>