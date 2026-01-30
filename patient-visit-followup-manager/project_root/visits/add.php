<?php
// healthcare_system/visits/add.php
$page_title = "Add Visit";
require_once '../config/db.php';
checkLogin();

$patients_result = $conn->query("SELECT patient_id, name FROM patients ORDER BY name");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $patient_id = escape($_POST['patient_id']);
    $visit_date = escape($_POST['visit_date']);
    $consultation_fee = escape($_POST['consultation_fee'] ?? 0);
    $lab_fee = escape($_POST['lab_fee'] ?? 0);
    
    $sql = "INSERT INTO visits (patient_id, visit_date, consultation_fee, lab_fee, follow_up_due) 
            VALUES ('$patient_id', '$visit_date', '$consultation_fee', '$lab_fee', 
                    DATE_ADD('$visit_date', INTERVAL 7 DAY))";
    
    if ($conn->query($sql) === TRUE) {
        $success = "Visit recorded successfully! Follow-up scheduled for " . 
                   date('d M Y', strtotime($visit_date . ' +7 days'));
        $_POST = array();
    } else {
        $error = "Error: " . $conn->error;
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>Record New Visit</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="patient_id">Select Patient *</label>
            <select id="patient_id" name="patient_id" required>
                <option value="">-- Select Patient --</option>
                <?php while($patient = $patients_result->fetch_assoc()): ?>
                    <option value="<?php echo $patient['patient_id']; ?>" 
                        <?php echo ($_POST['patient_id'] ?? '') == $patient['patient_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($patient['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="visit_date">Visit Date *</label>
            <input type="date" id="visit_date" name="visit_date" 
                   value="<?php echo $_POST['visit_date'] ?? date('Y-m-d'); ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="consultation_fee">Consultation Fee (₹)</label>
                <input type="number" id="consultation_fee" name="consultation_fee" 
                       step="0.01" value="<?php echo $_POST['consultation_fee'] ?? '0'; ?>">
            </div>
            
            <div class="form-group">
                <label for="lab_fee">Lab Fee (₹)</label>
                <input type="number" id="lab_fee" name="lab_fee" 
                       step="0.01" value="<?php echo $_POST['lab_fee'] ?? '0'; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <p class="alert alert-info">
                <strong>Note:</strong> Follow-up date will be automatically calculated (Visit Date + 7 days)
            </p>
        </div>
        
        <button type="submit" class="btn btn-success">Record Visit</button>
        <a href="list.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>