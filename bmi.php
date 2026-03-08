<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

// Auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']); exit();
}
$user_id = (int)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!$data) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']); exit();
    }

    $age      = isset($data['age'])      ? (int)$data['age']                      : null;
    $weight   = isset($data['weight'])   ? (float)$data['weight']                 : null;
    $height   = isset($data['height'])   ? (float)$data['height']                 : null;
    $unit     = isset($data['unit'])     ? $data['unit']                          : 'metric';
    $bmi      = isset($data['bmi'])      ? (float)$data['bmi']                    : null;
    $category = isset($data['category']) ? htmlspecialchars($data['category'])    : null;

    if (!$age || !$weight || !$height || !$bmi) {
        echo json_encode(['status' => 'error', 'message' => 'Missing fields']); exit();
    }

    $stmt = $conn->prepare(
        "INSERT INTO bmi_records (user_id, age, weight, height, unit, bmi, category, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
    );
    $stmt->bind_param('iiidsds', $user_id, $age, $weight, $height, $unit, $bmi, $category);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Saved', 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $conn->prepare(
        "SELECT * FROM bmi_records WHERE user_id=? ORDER BY created_at DESC LIMIT 20"
    );
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result  = $stmt->get_result();
    $records = [];
    while ($row = $result->fetch_assoc()) { $records[] = $row; }
    echo json_encode(['status' => 'success', 'data' => $records]);
    $stmt->close();
}

$conn->close();
?>
