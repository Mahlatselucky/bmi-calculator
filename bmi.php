<?php
// bmi.php — BMI Backend Processor
// Place inside XAMPP's htdocs/bmi-calculator/

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once 'db.php'; // MySQL connection

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200); exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
        exit();
    }

    $age      = isset($data['age'])      ? (int)$data['age']           : null;
    $weight   = isset($data['weight'])   ? (float)$data['weight']      : null;
    $height   = isset($data['height'])   ? (float)$data['height']      : null;
    $unit     = isset($data['unit'])     ? $data['unit']               : 'metric';
    $bmi      = isset($data['bmi'])      ? (float)$data['bmi']         : null;
    $category = isset($data['category']) ? htmlspecialchars($data['category']) : null;

    if (!$age || !$weight || !$height || !$bmi) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
        exit();
    }

    // Save to database
    $stmt = $conn->prepare(
        "INSERT INTO bmi_records (age, weight, height, unit, bmi, category, created_at)
         VALUES (?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param('iddsds', $age, $weight, $height, $unit, $bmi, $category);

    if ($stmt->execute()) {
        echo json_encode([
            'status'   => 'success',
            'message'  => 'BMI record saved',
            'id'       => $conn->insert_id,
            'bmi'      => $bmi,
            'category' => $category
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch last 10 records
    $result = $conn->query("SELECT * FROM bmi_records ORDER BY created_at DESC LIMIT 10");
    $records = [];
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    echo json_encode(['status' => 'success', 'data' => $records]);
}

$conn->close();
?>
