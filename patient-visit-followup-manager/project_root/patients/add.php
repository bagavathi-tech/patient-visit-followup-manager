<?php
$page_title = "Add Patient";
require_once '../config/db.php';
checkLogin();

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
        $sql = "INSERT INTO patients (name, dob, join_date, phone, address) 
                VALUES ('$name', '$dob', '$join_date', '$phone', '$address')";
        
        if ($conn->query($sql) === TRUE) {
            $success = "Patient added successfully!";
            // Clear form
            $_POST = array();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<?php require_once '../includes/header.php'; ?>

<div class="card">
    <h2>Add New Patient</h2>
    
    <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label for="name">Full Name *</label>
            <input type="text" id="name" name="name" value="<?php echo $_POST['name'] ?? ''; ?>" required>
        </div>
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label for="dob">Date of Birth *</label>
                <input type="date" id="dob" name="dob" value="<?php echo $_POST['dob'] ?? ''; ?>" max="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="join_date">Join Date *</label>
                <input type="date" id="join_date" name="join_date" value="<?php echo $_POST['join_date'] ?? date('Y-m-d'); ?>" required>
            </div>
        </div>
        
        <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" value="<?php echo $_POST['phone'] ?? ''; ?>">
        </div>
        
        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" rows="3"><?php echo $_POST['address'] ?? ''; ?></textarea>
        </div>
        
        <button type="submit" class="btn btn-success">Add Patient</button>
        <a href="list.php" class="btn">Cancel</a>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?>