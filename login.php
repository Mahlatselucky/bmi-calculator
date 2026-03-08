<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username=? OR email=?");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $uname, $hash);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hash)) {
            $_SESSION['user_id']  = $id;
            $_SESSION['username'] = $uname;
            header('Location: index.php'); exit();
        } else {
            $error = 'Invalid username/email or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — BMI Calculator</title>
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
      min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem;
    }
    body::before {
      content: ''; position: fixed; top: -20%; left: 50%; transform: translateX(-50%);
      width: 600px; height: 600px;
      background: radial-gradient(circle, rgba(45,255,127,0.07) 0%, transparent 70%);
      pointer-events: none; z-index: 0;
    }
    .wrapper { position: relative; z-index: 1; width: 100%; max-width: 400px; }
    header { text-align: center; margin-bottom: 2rem; }
    .logo { font-family: var(--font-display); font-size: 2rem; font-weight: 800; color: #fff; }
    .logo span { color: var(--green); }
    header p { margin-top: 0.4rem; font-size: 0.7rem; color: var(--muted); letter-spacing: 0.08em; }
    .card {
      background: var(--card); border: 1px solid var(--border); border-radius: 8px;
      padding: 2rem; position: relative; overflow: hidden;
    }
    .card::before {
      content: ''; position: absolute; top: 0; left: 0; right: 0; height: 1px;
      background: linear-gradient(90deg, transparent, var(--green-dim), transparent);
    }
    .card h2 { font-family: var(--font-display); font-size: 1.3rem; font-weight: 800; margin-bottom: 1.5rem; }
    .field { margin-bottom: 1.1rem; }
    label { display: block; font-size: 0.65rem; letter-spacing: 0.15em; text-transform: uppercase; color: var(--muted); margin-bottom: 0.4rem; }
    .input-wrap {
      display: flex; align-items: center; background: var(--surface);
      border: 1px solid var(--border); border-radius: 4px; overflow: hidden; transition: border-color 0.2s;
    }
    .input-wrap:focus-within { border-color: var(--green-dim); }
    input[type="text"], input[type="password"] {
      flex: 1; background: none; border: none; outline: none; color: var(--text);
      font-family: var(--font-mono); font-size: 0.9rem; padding: 0.65rem 0.8rem; width: 100%;
    }
    .btn {
      width: 100%; margin-top: 0.5rem; padding: 0.85rem;
      background: var(--green); color: #000; border: none; border-radius: 4px;
      font-family: var(--font-display); font-size: 1rem; font-weight: 800;
      cursor: pointer; transition: all 0.15s;
    }
    .btn:hover { background: #50ffaa; transform: translateY(-1px); }
    .error-msg {
      background: rgba(255,77,77,0.1); border: 1px solid rgba(255,77,77,0.3);
      color: var(--red); padding: 0.65rem 0.9rem; border-radius: 4px;
      font-size: 0.72rem; margin-bottom: 1rem;
    }
    .footer-link { text-align: center; margin-top: 1.2rem; font-size: 0.7rem; color: var(--muted); }
    .footer-link a { color: var(--green); text-decoration: none; }
    .footer-link a:hover { text-decoration: underline; }
  </style>
</head>
<body>
<div class="wrapper">
  <header>
    <div class="logo">BMI <span>Calc</span></div>
    <p>Track your health over time</p>
  </header>
  <div class="card">
    <h2>Log In</h2>

    <?php if ($error): ?>
      <div class="error-msg">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php" novalidate>
      <div class="field">
        <label for="username">Username or Email</label>
        <div class="input-wrap">
          <input type="text" id="username" name="username" placeholder="yourname or email" required
            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"/>
        </div>
      </div>
      <div class="field">
        <label for="password">Password</label>
        <div class="input-wrap">
          <input type="password" id="password" name="password" placeholder="••••••••" required/>
        </div>
      </div>
      <button type="submit" class="btn">Log In →</button>
    </form>

    <div class="footer-link">Don't have an account? <a href="register.php">Register</a></div>
  </div>
</div>
</body>
</html>
