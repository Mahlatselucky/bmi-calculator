# BMI Calculator WebApp

A full-stack BMI Calculator with user authentication, MySQL storage, and history tracking.

## Features
-  User Register & Login (password hashing with bcrypt)
-  Metric & Imperial unit toggle
-  Real-time BMI classification (Underweight → Obese III)
-  Visual BMI scale with animated needle
-  Healthy weight range, BMI Prime, Ponderal Index
-  Inline form validation with helpful error messages
-  History page — view, track, and delete past records
-  Stats summary (total, avg, min, max BMI)
-  Results auto-saved to MySQL per user

##Tech Stack
| Layer          | Technology                  |
|----------------|-----------------------------|
| Frontend       | HTML5, CSS3, JavaScript     |
| Backend        | PHP 8+                      |
| Database       | MySQL (via XAMPP)           |
| Dev Tools      | VS Code, XAMPP              |
| Version Control| Git + GitHub                |

## Project Structure
```
bmi-calculator/
├── index.php       ← BMI Calculator (protected, requires login)
├── login.php       ← Login page
├── register.php    ← Register page
├── logout.php      ← Session logout
├── history.php     ← View all saved BMI records
├── bmi.php         ← PHP API (save & fetch records)
├── db.php          ← MySQL connection
├── schema.sql      ← Database setup script
├── .gitignore
└── README.md
```

