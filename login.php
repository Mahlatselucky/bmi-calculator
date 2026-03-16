<?php
// login.php

session_start();
require_once 'db.php';

// If already logged in, go to calculator
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username and password.';
    } else {
        // Look up user in database
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param('ss', $username, $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $uname, $hash);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hash)) {
            // Login successful - save to session
            $_SESSION['user_id']  = $id;
            $_SESSION['username'] = $uname;
            header('Location: index.php');
            exit();
        } else {
            $error = 'Incorrect username or password.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - BMI Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 400px;
            margin: 0 auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c7be5;
        }

        label {
            display: block;
            margin-top: 12px;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 18px;
            background-color: #2c7be5;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover {
            background-color: #1a5dc8;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .bottom-link {
            text-align: center;
            margin-top: 15px;
            font-size: 13px;
        }

        .bottom-link a {
            color: #2c7be5;
            text-decoration: none;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>BMI Calculator</h2>
    <p style="text-align:center; color:#666; font-size:14px;">Log in to your account</p>

    <?php if ($error): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">

        <label for="username">Username or Email</label>
        <input type="text" id="username" name="username" placeholder="Enter username or email"
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"/>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter your password"/>

        <button type="submit">Log In</button>
    </form>

    <div class="bottom-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>
