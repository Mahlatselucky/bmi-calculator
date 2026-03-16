<?php
// index.php
// Main BMI Calculator page - only accessible when logged in
// Uses Regular Expressions (regex) to validate name and age inputs
// Student: Mahlatse Mphelo
// Module: WEDE6021

session_start();

// Send user to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMI Calculator</title>
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

        nav span {
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
        }

        nav a:hover { text-decoration: underline; }

        .container {
            max-width: 500px;
            margin: 30px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c7be5;
            margin-bottom: 5px;
        }

        p.subtitle {
            text-align: center;
            color: #666;
            font-size: 13px;
            margin-bottom: 25px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .field { flex: 1; }

        label {
            display: block;
            margin-bottom: 4px;
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 9px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 14px;
            margin-bottom: 4px;
        }

        input:focus {
            border-color: #2c7be5;
            outline: none;
        }

        .field-error {
            color: red;
            font-size: 12px;
            margin-bottom: 10px;
            min-height: 16px;
        }

        button {
            width: 100%;
            padding: 11px;
            margin-top: 10px;
            background-color: #2c7be5;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
        }

        button:hover { background-color: #1a5dc8; }

        .result-box {
            display: none;
            margin-top: 25px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f8f9fa;
            text-align: center;
        }

        .result-box h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .bmi-score {
            font-size: 52px;
            font-weight: bold;
            margin: 8px 0;
        }

        .bmi-label {
            display: inline-block;
            font-size: 16px;
            font-weight: bold;
            padding: 5px 18px;
            border-radius: 20px;
            margin-bottom: 15px;
        }

        .result-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 14px;
            margin-top: 10px;
        }

        .result-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }

        .result-table td:first-child {
            font-weight: bold;
            color: #555;
            width: 45%;
        }

        .saved-msg {
            display: none;
            color: green;
            font-size: 13px;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<nav>
    <span>BMI Calculator</span>
    <div>
        <span style="color:white; font-size:14px;">Welcome, <?php echo htmlspecialchars($username); ?></span>
        <a href="history.php">View History</a>
        <a href="logout.php">Logout</a>
    </div>
</nav>

<div class="container">
    <h2>BMI Calculator</h2>
    <p class="subtitle">Fill in your details below to calculate your Body Mass Index (BMI)</p>

    <div class="form-row">
        <div class="field">
            <label for="first_name">First Name</label>
            <input type="text" id="first_name" placeholder="e.g. John"/>
            <div class="field-error" id="err_first_name"></div>
        </div>
        <div class="field">
            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" placeholder="e.g. Doe"/>
            <div class="field-error" id="err_last_name"></div>
        </div>
    </div>

    <label for="age">Age (years)</label>
    <input type="number" id="age" placeholder="e.g. 25"/>
    <div class="field-error" id="err_age"></div>

    <div class="form-row">
        <div class="field">
            <label for="weight">Weight (kg)</label>
            <input type="number" id="weight" placeholder="e.g. 70"/>
            <div class="field-error" id="err_weight"></div>
        </div>
        <div class="field">
            <label for="height">Height (m)</label>
            <input type="number" id="height" placeholder="e.g. 1.75" step="0.01"/>
            <div class="field-error" id="err_height"></div>
        </div>
    </div>

    <button onclick="calculateBMI()">Calculate BMI</button>

    <div class="result-box" id="result-box">
        <h3 id="result-name"></h3>
        <div class="bmi-score" id="bmi-score"></div>
        <div class="bmi-label" id="bmi-label"></div>

        <table class="result-table">
            <tr><td>Age</td><td id="r-age"></td></tr>
            <tr><td>Weight</td><td id="r-weight"></td></tr>
            <tr><td>Height</td><td id="r-height"></td></tr>
            <tr><td>Healthy Weight Range</td><td id="r-range"></td></tr>
        </table>

        <div class="saved-msg" id="saved-msg">✓ Result saved to your history.</div>
    </div>
</div>

<script>
    // -------------------------------------------------------
    // REGULAR EXPRESSION VALIDATION (JavaScript side)
    // These run in the browser BEFORE sending data to PHP
    // -------------------------------------------------------

    function clearErrors() {
        var fields = ['first_name', 'last_name', 'age', 'weight', 'height'];
        for (var i = 0; i < fields.length; i++) {
            document.getElementById('err_' + fields[i]).textContent = '';
        }
    }

    function calculateBMI() {
        clearErrors();

        var firstName = document.getElementById('first_name').value.trim();
        var lastName  = document.getElementById('last_name').value.trim();
        var age       = document.getElementById('age').value;
        var weight    = parseFloat(document.getElementById('weight').value);
        var height    = parseFloat(document.getElementById('height').value);
        var isValid   = true;

        // REGEX: Name must only contain letters and spaces (no numbers or symbols)
        // ^ = start, [a-zA-Z ] = letters and space only, + = one or more, $ = end
        var namePattern = /^[a-zA-Z ]+$/;

        if (!firstName || !namePattern.test(firstName)) {
            document.getElementById('err_first_name').textContent = 'First name must contain letters only.';
            isValid = false;
        }

        if (!lastName || !namePattern.test(lastName)) {
            document.getElementById('err_last_name').textContent = 'Last name must contain letters only.';
            isValid = false;
        }

        // REGEX: Age must be 1-3 digits, between 2 and 120
        // ^\d{1,3}$ means only digits, 1 to 3 characters
        var agePattern = /^\d{1,3}$/;

        if (!age || !agePattern.test(age) || parseInt(age) < 2 || parseInt(age) > 120) {
            document.getElementById('err_age').textContent = 'Age must be a number between 2 and 120.';
            isValid = false;
        }

        if (!weight || weight <= 0) {
            document.getElementById('err_weight').textContent = 'Please enter a valid weight.';
            isValid = false;
        }

        // REGEX: Height must be a decimal number like 1.75
        // ^\d+(\.\d+)?$ means digits, optionally followed by a dot and more digits
        var heightPattern = /^\d+(\.\d+)?$/;

        if (!height || !heightPattern.test(document.getElementById('height').value) || height <= 0 || height > 3) {
            document.getElementById('err_height').textContent = 'Enter height in meters (e.g. 1.75).';
            isValid = false;
        }

        if (!isValid) return;

        // BMI Formula: weight divided by height squared
        var bmi = weight / (height * height);

        // Determine category based on BMI value
        var category, textColor, bgColor;

        if (bmi < 18.5) {
            category  = 'Underweight';
            textColor = '#004085';
            bgColor   = '#cce5ff';
        } else if (bmi < 25) {
            category  = 'Normal Weight';
            textColor = '#155724';
            bgColor   = '#d4edda';
        } else if (bmi < 30) {
            category  = 'Overweight';
            textColor = '#856404';
            bgColor   = '#fff3cd';
        } else {
            category  = 'Obese';
            textColor = '#721c24';
            bgColor   = '#f8d7da';
        }

        // Calculate healthy weight range
        var minWeight = (18.5 * height * height).toFixed(1);
        var maxWeight = (24.9 * height * height).toFixed(1);

        // Display the results on screen
        document.getElementById('result-name').textContent         = 'Results for: ' + firstName + ' ' + lastName;
        document.getElementById('bmi-score').textContent           = bmi.toFixed(1);
        document.getElementById('bmi-score').style.color           = textColor;
        document.getElementById('bmi-label').textContent           = category;
        document.getElementById('bmi-label').style.color           = textColor;
        document.getElementById('bmi-label').style.backgroundColor = bgColor;
        document.getElementById('r-age').textContent               = age + ' years';
        document.getElementById('r-weight').textContent            = weight + ' kg';
        document.getElementById('r-height').textContent            = height + ' m';
        document.getElementById('r-range').textContent             = minWeight + ' kg - ' + maxWeight + ' kg';

        document.getElementById('result-box').style.display = 'block';
        document.getElementById('saved-msg').style.display  = 'none';

        // Save to database
        saveRecord(firstName, lastName, parseInt(age), weight, height, bmi.toFixed(2), category);
    }

    function saveRecord(firstName, lastName, age, weight, height, bmi, category) {
        fetch('bmi.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                first_name : firstName,
                last_name  : lastName,
                age        : age,
                weight     : weight,
                height     : height,
                bmi        : bmi,
                category   : category
            })
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.status === 'success') {
                document.getElementById('saved-msg').style.display = 'block';
            }
        })
        .catch(function(error) {
            console.log('Error saving: ' + error);
        });
    }
</script>

</body>
</html>
