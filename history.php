<?php
// history.php - BMI records with full CRUD (Create via bmi.php, Read, Update, Delete)


session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id  = $_SESSION['user_id'];
$username = $_SESSION['username'];

// UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
    $update_id  = (int)$_POST['update_id'];
    $new_weight = trim($_POST['new_weight'] ?? '');
    $new_height = trim($_POST['new_height'] ?? '');

    $update_errors = [];

    if (!preg_match('/^\d+(\.\d+)?$/', $new_weight) || (float)$new_weight <= 0) {
        $update_errors[] = 'Invalid weight.';
    }

    if (!preg_match('/^\d+(\.\d+)?$/', $new_height) || (float)$new_height <= 0 || (float)$new_height > 3) {
        $update_errors[] = 'Invalid height.';
    }

    if (empty($update_errors)) {
        $w = (float)$new_weight;
        $h = (float)$new_height;
        $new_bmi = $w / ($h * $h);

        if ($new_bmi < 18.5)      $new_category = 'Underweight';
        elseif ($new_bmi < 25)    $new_category = 'Normal Weight';
        elseif ($new_bmi < 30)    $new_category = 'Overweight';
        else                      $new_category = 'Obese';

        $stmt = $conn->prepare(
            "UPDATE bmi_records SET weight = ?, height = ?, bmi = ?, category = ?
             WHERE id = ? AND user_id = ?"
        );
        $stmt->bind_param('dddsii', $w, $h, $new_bmi, $new_category, $update_id, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// DELETE single
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
    $delete_id = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM bmi_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param('ii', $delete_id, $user_id);
    $stmt->execute();
    $stmt->close();
}

// DELETE all
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_all'])) {
    $stmt = $conn->prepare("DELETE FROM bmi_records WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

// READ
$stmt = $conn->prepare("SELECT * FROM bmi_records WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result  = $stmt->get_result();
$records = [];
while ($row = $result->fetch_assoc()) {
    $records[] = $row;
}
$stmt->close();

$total = count($records);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI History</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }

        nav {
            background-color: #2c7be5;
            padding: 12px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav span { color: white; font-size: 18px; font-weight: bold; }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
        }

        nav a:hover { text-decoration: underline; }

        .container { max-width: 1000px; margin: 30px auto; padding: 0 15px; }

        h2 { color: #2c7be5; }

        .stats { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }

        .stat-box {
            background-color: white;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            flex: 1;
            min-width: 120px;
            text-align: center;
        }

        .stat-box .label { font-size: 12px; color: #666; margin-bottom: 5px; }
        .stat-box .value { font-size: 26px; font-weight: bold; color: #2c7be5; }

        .table-wrapper {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
        }

        .table-top h3 { margin: 0; font-size: 15px; color: #333; }

        .delete-all-btn {
            background: none;
            border: 1px solid #ccc;
            padding: 5px 12px;
            border-radius: 4px;
            color: #666;
            cursor: pointer;
            font-size: 13px;
        }

        .delete-all-btn:hover { border-color: red; color: red; }

        table { width: 100%; border-collapse: collapse; }

        thead th {
            background-color: #f8f9fa;
            padding: 10px 12px;
            text-align: left;
            font-size: 13px;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        tbody td { padding: 10px 12px; font-size: 13px; border-bottom: 1px solid #f0f0f0; }

        tbody tr:hover { background-color: #f9f9f9; }

        .badge { padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }

        .btn {
            border: 1px solid #ccc;
            background: none;
            color: #999;
            padding: 3px 8px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-right: 3px;
        }

        .btn:hover { border-color: #2c7be5; color: #2c7be5; }
        .btn.delete:hover { border-color: red; color: red; }

        .empty-msg { padding: 40px; text-align: center; color: #999; font-size: 14px; }
        .empty-msg a { color: #2c7be5; }

        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.4);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active { display: flex; }

        .modal {
            background: white;
            border-radius: 8px;
            padding: 25px 30px;
            width: 360px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }

        .modal h3 { margin-top: 0; color: #2c7be5; font-size: 16px; }

        .modal label { display: block; margin-top: 12px; font-size: 13px; font-weight: bold; color: #333; }

        .modal input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            margin-top: 4px;
        }

        .modal input:focus { border-color: #2c7be5; outline: none; }

        .modal-error { color: red; font-size: 12px; margin-top: 4px; min-height: 14px; }

        .modal-buttons { display: flex; gap: 10px; margin-top: 18px; }

        .modal-buttons button { flex: 1; padding: 9px; border: none; border-radius: 4px; cursor: pointer; font-size: 14px; }

        .btn-save { background-color: #2c7be5; color: white; }
        .btn-save:hover { background-color: #1a5dc8; }
        .btn-cancel { background-color: #f0f0f0; color: #333; }
        .btn-cancel:hover { background-color: #ddd; }
    </style>
</head>
<body>

<nav>
    <span>BMI Calculator</span>
    <div>
        <span style="color:white; font-size:14px;">Welcome, <?php echo htmlspecialchars($username); ?></span>
        <a href="index.php">Calculator</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>My BMI History</h2>
    <p style="color:#666; font-size:14px;">All your saved BMI records are shown below.</p>

    <div class="stats">
        <div class="stat-box">
            <div class="label">Total Records</div>
            <div class="value"><?php echo $total; ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Average BMI</div>
            <div class="value">
                <?php
                if ($total > 0) {
                    $bmis = array_column($records, 'bmi');
                    echo number_format(array_sum($bmis) / $total, 1);
                } else { echo '—'; }
                ?>
            </div>
        </div>
        <div class="stat-box">
            <div class="label">Lowest BMI</div>
            <div class="value"><?php echo $total > 0 ? number_format(min(array_column($records,'bmi')), 1) : '—'; ?></div>
        </div>
        <div class="stat-box">
            <div class="label">Highest BMI</div>
            <div class="value"><?php echo $total > 0 ? number_format(max(array_column($records,'bmi')), 1) : '—'; ?></div>
        </div>
    </div>

    <div class="table-wrapper">
        <div class="table-top">
            <h3>All Records (<?php echo $total; ?>)</h3>
            <?php if ($total > 0): ?>
            <form method="POST" onsubmit="return confirm('Delete ALL records?');">
                <input type="hidden" name="delete_all" value="1"/>
                <button type="submit" class="delete-all-btn">Delete All</button>
            </form>
            <?php endif; ?>
        </div>

        <?php if ($total == 0): ?>
            <div class="empty-msg">No records yet. <a href="index.php">Calculate your BMI now!</a></div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>BMI</th>
                    <th>Category</th>
                    <th>Age</th>
                    <th>Weight (kg)</th>
                    <th>Height (m)</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $count = $total;
                foreach ($records as $row):
                    $bmi = $row['bmi'];
                    if ($bmi < 18.5)      { $color = '#004085'; $bg = '#cce5ff'; }
                    elseif ($bmi < 25)    { $color = '#155724'; $bg = '#d4edda'; }
                    elseif ($bmi < 30)    { $color = '#856404'; $bg = '#fff3cd'; }
                    else                  { $color = '#721c24'; $bg = '#f8d7da'; }
                ?>
                <tr>
                    <td><?php echo $count--; ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><strong style="color:<?php echo $color; ?>"><?php echo number_format($row['bmi'], 1); ?></strong></td>
                    <td>
                        <span class="badge" style="color:<?php echo $color; ?>; background-color:<?php echo $bg; ?>">
                            <?php echo htmlspecialchars($row['category']); ?>
                        </span>
                    </td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo $row['weight']; ?></td>
                    <td><?php echo $row['height']; ?></td>
                    <td><?php echo date('d M Y, H:i', strtotime($row['created_at'])); ?></td>
                    <td>
                        <button type="button" class="btn"
                            onclick="openEdit(<?php echo $row['id']; ?>, <?php echo $row['weight']; ?>, <?php echo $row['height']; ?>)">
                            Edit
                        </button>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this record?');">
                            <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>"/>
                            <button type="submit" class="btn delete">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- Edit / Update Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>Edit BMI Record</h3>
        <p style="font-size:13px; color:#666; margin-top:0;">Update weight and height — BMI will be recalculated.</p>

        <form method="POST" action="history.php" onsubmit="return validateEdit()">
            <input type="hidden" name="update_id" id="edit_id"/>

            <label for="edit_weight">Weight (kg)</label>
            <input type="text" id="edit_weight" name="new_weight" placeholder="e.g. 72"/>
            <div class="modal-error" id="err_edit_weight"></div>

            <label for="edit_height">Height (m)</label>
            <input type="text" id="edit_height" name="new_height" placeholder="e.g. 1.75"/>
            <div class="modal-error" id="err_edit_height"></div>

            <div class="modal-buttons">
                <button type="submit" class="btn-save">Save Changes</button>
                <button type="button" class="btn-cancel" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openEdit(id, weight, height) {
        document.getElementById('edit_id').value     = id;
        document.getElementById('edit_weight').value = weight;
        document.getElementById('edit_height').value = height;
        document.getElementById('err_edit_weight').textContent = '';
        document.getElementById('err_edit_height').textContent = '';
        document.getElementById('editModal').classList.add('active');
    }

    function closeEdit() {
        document.getElementById('editModal').classList.remove('active');
    }

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEdit();
    });

    function validateEdit() {
        var weight  = document.getElementById('edit_weight').value;
        var height  = document.getElementById('edit_height').value;
        var isValid = true;
        var numPattern = /^\d+(\.\d+)?$/;

        document.getElementById('err_edit_weight').textContent = '';
        document.getElementById('err_edit_height').textContent = '';

        if (!numPattern.test(weight) || parseFloat(weight) <= 0) {
            document.getElementById('err_edit_weight').textContent = 'Please enter a valid weight.';
            isValid = false;
        }

        if (!numPattern.test(height) || parseFloat(height) <= 0 || parseFloat(height) > 3) {
            document.getElementById('err_edit_height').textContent = 'Enter height in metres (e.g. 1.75).';
            isValid = false;
        }

        return isValid;
    }
</script>

</body>
</html>