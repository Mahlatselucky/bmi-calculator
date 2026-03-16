<?php
// register.php
// Allows a new user to create an account
// Uses Regular Expressions (regex) to validate all inputs
// Student: Mahlatse Mphelo
// Module: WEDE6021

session_start();
require_once 'db.php';

// If already logged in, go to calculator
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$errors  = [];
$success = '';

// Process form when submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    // -------------------------------------------------------
    // REGULAR EXPRESSION VALIDATION
    // preg_match() returns 1 if the pattern matches, 0 if not
    // -------------------------------------------------------

    // USERNAME: only letters and numbers, 3 to 20 characters
    // ^ means start, $ means end
    // [a-zA-Z0-9] means any letter or number
    // {3,20} means between 3 and 20 characters
    if (!preg_match('/^[a-zA-Z0-9]{3,20}$/', $username)) {
        $errors[] = 'Username must be 3-20 characters and contain only letters and numbers.';
    }

    // EMAIL: must follow standard email format (e.g. name@domain.com)
    // [\w.-]+ matches letters, digits, dots, hyphens before the @
    // [\w.-]+ matches the domain name
    // [a-zA-Z]{2,} matches the extension like .com or .co.za
    if (!preg_match('/^[\w.-]+@[\w.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors[] = 'Please enter a valid email address (e.g. name@email.com).';
    }

    // PASSWORD: at least 6 characters, must contain at least one letter and one number
    // (?=.*[A-Za-z]) means must contain at least one letter
    // (?=.*\d) means must contain at least one digit/number
    // .{6,} means at least 6 characters total
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{6,}$/', $password)) {
        $errors[] = 'Password must be at least 6 characters and include at least one letter and one number.';
    }

    // Confirm password matches
    if ($password != $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    // If no regex errors, check if user already exists
    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->bind_param('ss', $username, $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors[] = 'Username or email already exists. Please choose another.';
        } else {
            // Hash the password before saving (security)
            $hashed = password_hash($password, PASSWORD_BCRYPT);

            // Save new user to database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $username, $email, $hashed);

            if ($stmt->execute()) {
                $success = 'Account created successfully! You can now log in.';
            } else {
                $errors[] = 'Something went wrong. Please try again.';
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - BMI Calculator</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 420px;
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

        /* Small hint text shown under each field */
        .hint {
            font-size: 11px;
            color: #888;
            margin-bottom: 4px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
        }

        input:focus {
            border-color: #2c7be5;
            outline: none;
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
            padding: 8px 12px;
            border-radius: 4px;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
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
    <h2>Create Account</h2>

    <!-- Show any validation errors -->
    <?php foreach ($errors as $err): ?>
        <div class="error">⚠ <?php echo $err; ?></div>
    <?php endforeach; ?>

    <!-- Show success message -->
    <?php if ($success): ?>
        <div class="success">✓ <?php echo $success; ?> <a href="login.php">Login here</a></div>
    <?php endif; ?>

    <form method="POST" action="register.php">

        <label for="username">Username</label>
        <div class="hint">Only letters and numbers, 3-20 characters</div>
        <input type="text" id="username" name="username"
               placeholder="e.g. mahlatse123"
               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"/>

        <label for="email">Email Address</label>
        <div class="hint">Must be a valid email (e.g. name@email.com)</div>
        <input type="email" id="email" name="email"
               placeholder="e.g. mahlatse@email.com"
               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"/>

        <label for="password">Password</label>
        <div class="hint">At least 6 characters, must include a letter and a number</div>
        <input type="password" id="password" name="password"
               placeholder="e.g. pass123"/>

        <label for="confirm">Confirm Password</label>
        <input type="password" id="confirm" name="confirm"
               placeholder="Repeat your password"/>

        <button type="submit">Register</button>
    </form>

    <div class="bottom-link">
        Already have an account? <a href="login.php">Log in here</a>
    </div>
</div>

</body>
</html>
