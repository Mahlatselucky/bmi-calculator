# 🧮 BMI Calculator WebApp

A full-stack BMI Calculator with user authentication, MySQL storage, and history tracking.

## ✨ Features
- ✅ User Register & Login (password hashing with bcrypt)
- ✅ Metric & Imperial unit toggle
- ✅ Real-time BMI classification (Underweight → Obese III)
- ✅ Visual BMI scale with animated needle
- ✅ Healthy weight range, BMI Prime, Ponderal Index
- ✅ Inline form validation with helpful error messages
- ✅ History page — view, track, and delete past records
- ✅ Stats summary (total, avg, min, max BMI)
- ✅ Results auto-saved to MySQL per user

## 🛠️ Tech Stack
| Layer          | Technology                  |
|----------------|-----------------------------|
| Frontend       | HTML5, CSS3, JavaScript     |
| Backend        | PHP 8+                      |
| Database       | MySQL (via XAMPP)           |
| Dev Tools      | VS Code, XAMPP              |
| Version Control| Git + GitHub                |

## 📁 Project Structure
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

## 🚀 Setup (XAMPP)

### 1. Clone the repo
```bash
git clone https://github.com/YOUR_USERNAME/bmi-calculator.git
```

### 2. Move to htdocs
- Windows: `C:\xampp\htdocs\bmi-calculator\`
- Mac/Linux: `/Applications/XAMPP/htdocs/bmi-calculator/`

### 3. Create the database
1. Start XAMPP → Start **Apache** and **MySQL**
2. Open `http://localhost/phpmyadmin`
3. Click the **SQL** tab → paste contents of `schema.sql` → click **Go**

### 4. Open in browser
```
http://localhost/bmi-calculator/login.php
```

### 5. Register & use
- Register a new account at `register.php`
- Log in and start calculating!

## 🔒 Security Notes
- Passwords hashed with `password_hash()` / `PASSWORD_BCRYPT`
- All inputs sanitized with `htmlspecialchars()` + prepared statements
- Session-based authentication on all protected pages
- Add `db.php` to `.gitignore` before pushing publicly

## 📌 BMI Classification
| BMI Range | Category     |
|-----------|--------------|
| < 18.5    | Underweight  |
| 18.5–24.9 | Normal       |
| 25–29.9   | Overweight   |
| 30–34.9   | Obese I      |
| 35–39.9   | Obese II     |
| ≥ 40      | Obese III    |

---
> ⚠️ BMI is a screening tool, not a medical diagnosis. Always consult a healthcare professional.
