<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); exit();
}
require_once 'db.php';

$user_id  = (int)$_SESSION['user_id'];
$username = htmlspecialchars($_SESSION['username']);

// Handle delete single record
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $del = (int)$_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM bmi_records WHERE id=? AND user_id=?");
    $stmt->bind_param('ii', $del, $user_id);
    $stmt->execute();
    $stmt->close();
}

// Handle delete all
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $stmt = $conn->prepare("DELETE FROM bmi_records WHERE user_id=?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->close();
}

// Fetch records
$stmt = $conn->prepare(
    "SELECT * FROM bmi_records WHERE user_id=? ORDER BY created_at DESC"
);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result  = $stmt->get_result();
$records = [];
while ($row = $result->fetch_assoc()) { $records[] = $row; }
$stmt->close();

// Stats
$total   = count($records);
$avgBmi  = $total > 0 ? array_sum(array_column($records, 'bmi')) / $total : 0;
$minBmi  = $total > 0 ? min(array_column($records, 'bmi')) : 0;
$maxBmi  = $total > 0 ? max(array_column($records, 'bmi')) : 0;

function categoryColor($cat) {
    $map = [
        'Underweight' => '#4dc3ff',
        'Normal'      => '#2dff7f',
        'Overweight'  => '#f5e642',
        'Obese I'     => '#ff8c42',
        'Obese II'    => '#ff6b35',
        'Obese III'   => '#ff4d4d',
    ];
    return $map[$cat] ?? '#d4ede0';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>History — BMI Calculator</title>
  <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet"/>
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg: #0a0f0d; --surface: #111a16; --card: #162019; --border: #1f3328;
      --green: #2dff7f; --green-dim: #1a9448; --red: #ff4d4d;
      --text: #d4ede0; --muted: #5a7a68;
      --font-display: 'Syne', sans-serif; --font-mono: 'Space Mono', monospace;
    }
    body {
      background: var(--bg); color: var(--text); font-family: var(--font-mono);
      min-height: 100vh; display: flex; flex-direction: column;
      align-items: center; padding: 0 1rem 3rem; overflow-x: hidden;
    }
    body::before {
      content: ''; position: fixed; top: -20%; left: 50%; transform: translateX(-50%);
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(45,255,127,0.07) 0%, transparent 70%);
      pointer-events: none; z-index: 0;
    }
    nav {
      width: 100%; max-width: 700px; display: flex; align-items: center;
      justify-content: space-between; padding: 1.2rem 0; position: relative; z-index: 1;
    }
    .nav-logo { font-family: var(--font-display); font-size: 1.1rem; font-weight: 800; color: #fff; }
    .nav-logo span { color: var(--green); }
    .nav-right { display: flex; align-items: center; gap: 1rem; }
    .nav-user { font-size: 0.65rem; color: var(--muted); }
    .nav-user strong { color: var(--green); }
    .nav-links { display: flex; gap: 0.5rem; }
    .nav-links a {
      font-size: 0.65rem; letter-spacing: 0.1em; text-transform: uppercase;
      color: var(--muted); text-decoration: none; padding: 5px 10px;
      border: 1px solid var(--border); border-radius: 3px; transition: all 0.2s;
    }
    .nav-links a:hover, .nav-links a.active { color: var(--green); border-color: var(--green-dim); }

    .wrapper { position: relative; z-index: 1; width: 100%; max-width: 700px; }

    .page-header { margin-bottom: 2rem; }
    .page-header h1 { font-family: var(--font-display); font-size: 2rem; font-weight: 800; }
    .page-header h1 span { color: var(--green); }
    .page-header p { font-size: 0.7rem; color: var(--muted); margin-top: 0.3rem; }

    /* Stats */
    .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0.75rem; margin-bottom: 2rem; }
    .stat-card {
      background: var(--card); border: 1px solid var(--border); border-radius: 6px;
      padding: 1rem; text-align: center;
    }
    .stat-label { font-size: 0.6rem; color: var(--muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.4rem; }
    .stat-value { font-family: var(--font-display); font-size: 1.5rem; font-weight: 800; color: var(--green); }

    /* Table card */
    .table-card {
      background: var(--card); border: 1px solid var(--border); border-radius: 8px;
      overflow: hidden; position: relative;
    }
    .table-card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, var(--green-dim), transparent);
    }
    .table-header {
      display: flex; align-items: center; justify-content: space-between;
      padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);
    }
    .table-header h3 { font-family: var(--font-display); font-size: 0.9rem; font-weight: 700; }
    .del-all-btn {
      background: none; border: 1px solid var(--border); color: var(--muted);
      padding: 4px 12px; border-radius: 3px; font-family: var(--font-mono);
      font-size: 0.6rem; cursor: pointer; letter-spacing: 0.08em; transition: all 0.2s;
    }
    .del-all-btn:hover { border-color: var(--red); color: var(--red); }

    table { width: 100%; border-collapse: collapse; }
    thead th {
      text-align: left; font-size: 0.6rem; letter-spacing: 0.12em;
      text-transform: uppercase; color: var(--muted); padding: 0.75rem 1.5rem;
      border-bottom: 1px solid var(--border); font-weight: 400;
    }
    tbody tr { transition: background 0.15s; }
    tbody tr:hover { background: rgba(45,255,127,0.03); }
    tbody tr + tr { border-top: 1px solid var(--border); }
    td { padding: 0.8rem 1.5rem; font-size: 0.8rem; vertical-align: middle; }
    .bmi-cell { font-family: var(--font-display); font-size: 1.1rem; font-weight: 800; }
    .cat-badge {
      font-size: 0.6rem; letter-spacing: 0.1em; text-transform: uppercase;
      padding: 3px 8px; border-radius: 2px; font-weight: 700;
    }
    .del-btn {
      background: none; border: 1px solid var(--border); color: var(--muted);
      padding: 3px 8px; border-radius: 3px; font-family: var(--font-mono);
      font-size: 0.6rem; cursor: pointer; transition: all 0.2s;
    }
    .del-btn:hover { border-color: var(--red); color: var(--red); }

    .empty-state {
      padding: 3rem; text-align: center;
    }
    .empty-state p { color: var(--muted); font-size: 0.8rem; margin-bottom: 1rem; }
    .empty-state a {
      color: var(--green); text-decoration: none; font-size: 0.75rem;
      border: 1px solid var(--green-dim); padding: 6px 16px; border-radius: 4px;
    }

    @media (max-width: 600px) {
      .stats-grid { grid-template-columns: 1fr 1fr; }
      td, thead th { padding: 0.7rem 0.8rem; }
    }
  </style>
</head>
<body>

<nav>
  <div class="nav-logo">BMI <span>Calc</span></div>
  <div class="nav-right">
    <span class="nav-user">Hello, <strong><?= $username ?></strong></span>
    <div class="nav-links">
      <a href="index.php">Calculator</a>
      <a href="history.php" class="active">History</a>
      <a href="logout.php">Logout</a>
    </div>
  </div>
</nav>

<div class="wrapper">
  <div class="page-header">
    <h1>Your <span>History</span></h1>
    <p>All BMI records saved to your account</p>
  </div>

  <!-- Stats -->
  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-label">Total Records</div>
      <div class="stat-value"><?= $total ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg BMI</div>
      <div class="stat-value"><?= $total > 0 ? number_format($avgBmi, 1) : '—' ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Lowest BMI</div>
      <div class="stat-value"><?= $total > 0 ? number_format($minBmi, 1) : '—' ?></div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Highest BMI</div>
      <div class="stat-value"><?= $total > 0 ? number_format($maxBmi, 1) : '—' ?></div>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-header">
      <h3>All Records</h3>
      <?php if ($total > 0): ?>
        <form method="POST" onsubmit="return confirm('Delete ALL records? This cannot be undone.');">
          <input type="hidden" name="delete_all" value="1"/>
          <button type="submit" class="del-all-btn">Delete All</button>
        </form>
      <?php endif; ?>
    </div>

    <?php if ($total === 0): ?>
      <div class="empty-state">
        <p>No records yet. Calculate your first BMI!</p>
        <a href="index.php">Go to Calculator →</a>
      </div>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>BMI</th>
            <th>Category</th>
            <th>Age</th>
            <th>Weight</th>
            <th>Height</th>
            <th>Unit</th>
            <th>Date</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $i => $r):
            $color = categoryColor($r['category']);
            $unit  = $r['unit'] === 'metric' ? 'kg / cm' : 'lbs / in';
          ?>
          <tr>
            <td style="color:var(--muted)"><?= $total - $i ?></td>
            <td><span class="bmi-cell" style="color:<?= $color ?>"><?= number_format($r['bmi'],1) ?></span></td>
            <td>
              <span class="cat-badge"
                style="color:<?= $color ?>;background:<?= $color ?>22;border:1px solid <?= $color ?>44">
                <?= htmlspecialchars($r['category']) ?>
              </span>
            </td>
            <td><?= $r['age'] ?></td>
            <td><?= $r['weight'] ?></td>
            <td><?= $r['height'] ?></td>
            <td style="color:var(--muted)"><?= $unit ?></td>
            <td style="color:var(--muted);font-size:0.65rem"><?= date('M d, Y H:i', strtotime($r['created_at'])) ?></td>
            <td>
              <form method="POST" onsubmit="return confirm('Delete this record?');">
                <input type="hidden" name="delete_id" value="<?= $r['id'] ?>"/>
                <button type="submit" class="del-btn">✕</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

</body>
</html>
