<?php

session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$errors = [];
$success = '';

// Get record ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch record and verify ownership
$stmt = $conn->prepare("SELECT * FROM bmi_records WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record) {
    // No record or not owned → redirect
    header('Location: history.php');
    exit();
}

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $age        = $_POST['age'] ?? '';
    $weight     = $_POST['weight'] ?? '';
    $height     = $_POST['height'] ?? '';

    // Server‑side validation
    if (!preg_match('/^[a-zA-Z ]+$/', $first_name)) {
        $errors[] = 'First name must contain only letters and spaces.';
    }
    if (!preg_match('/^[a-zA-Z ]+$/', $last_name)) {
        $errors[] = 'Last name must contain only letters and spaces.';
    }
    if (!preg_match('/^\d{1,3}$/', $age) || $age < 2 || $age > 120) {
        $errors[] = 'Age must be a number between 2 and 120.';
    }
    if (!is_numeric($weight) || $weight <= 0) {
        $errors[] = 'Weight must be a positive number.';
    }
    if (!preg_match('/^\d+(\.\d+)?$/', $height) || $height <= 0 || $height > 3) {
        $errors[] = 'Height must be a valid number in meters (e.g. 1.75) and ≤ 3.';
    }

    if (empty($errors)) {
        // Recalculate BMI and category
        $bmi = $weight / ($height * $height);
        if ($bmi < 18.5) {
            $category = 'Underweight';
        } elseif ($bmi < 25) {
            $category = 'Normal Weight';
        } elseif ($bmi < 30) {
            $category = 'Overweight';
        } else {
            $category = 'Obese';
        }

        // Update record (prepared statement)
        $stmt = $conn->prepare("UPDATE bmi_records SET first_name = ?, last_name = ?, age = ?, weight = ?, height = ?, bmi = ?, category = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param('ssidddsii', $first_name, $last_name, $age, $weight, $height, $bmi, $category, $id, $user_id);
        if ($stmt->execute()) {
            $success = 'Record updated successfully.';
            // Refresh displayed data
            $record['first_name'] = $first_name;
            $record['last_name']  = $last_name;
            $record['age']        = $age;
            $record['weight']     = $weight;
            $record['height']     = $height;
            $record['bmi']        = $bmi;
            $record['category']   = $category;
        } else {
            $errors[] = 'Failed to update record.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit BMI Record</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f2f2f2; margin: 0; padding: 0; }
        nav { background-color: #2c7be5; padding: 12px 25px; display: flex; justify-content: space-between; align-items: center; }
        nav span { color: white; font-size: 18px; font-weight: bold; }
        nav a { color: white; text-decoration: none; margin-left: 20px; font-size: 14px; }
        nav a:hover { text-decoration: underline; }
        .container { max-width: 500px; margin: 30px auto; background-color: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c7be5; }
        label { display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px; }
        input { width: 100%; padding: 9px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; margin-bottom: 10px; }
        button { width: 100%; padding: 11px; background-color: #2c7be5; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #1a5dc8; }
        .error { color: red; font-size: 13px; margin-bottom: 5px; }
        .success { color: green; font-size: 13px; margin-bottom: 10px; }
        .back-link { text-align: center; margin-top: 15px; }
        .back-link a { color: #2c7be5; text-decoration: none; }
    </style>
</head>
<body>
<nav>
    <span>BMI Calculator</span>
    <div>
        <span>Welcome, <?php echo htmlspecialchars($username); ?></span>
        <a href="index.php">Calculator</a>
        <a href="history.php">History</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>
<div class="container">
    <h2>Edit Record</h2>
    <?php foreach ($errors as $err): ?>
        <div class="error"><?php echo $err; ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>
    <form method="POST">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?php echo htmlspecialchars($record['first_name']); ?>" required>
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?php echo htmlspecialchars($record['last_name']); ?>" required>
        <label>Age (years)</label>
        <input type="number" name="age" value="<?php echo $record['age']; ?>" required min="2" max="120">
        <label>Weight (kg)</label>
        <input type="number" step="0.1" name="weight" value="<?php echo $record['weight']; ?>" required min="0.1">
        <label>Height (m)</label>
        <input type="number" step="0.01" name="height" value="<?php echo $record['height']; ?>" required min="0.5" max="3">
        <button type="submit">Update Record</button>
    </form>
    <div class="back-link"><a href="history.php">← Back to History</a></div>
</div>
</body>
</html>