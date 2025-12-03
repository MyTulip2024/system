<?php
// Start session
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'employee_managements';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'login') {
            handleLogin($pdo);
        }
    }
}

function handleLogin($pdo) {
    $employee_number = $_POST['employee_number'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate input
    if (empty($employee_number) || empty($password)) {
        $_SESSION['login_error'] = 'Please fill in all fields';
        return;
    }
    
    // Check if employee exists in users table
    $stmt = $pdo->prepare("SELECT * FROM users WHERE employee_no = ?");
    $stmt->execute([$employee_number]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        // Login successful - redirect to dashboard
        $_SESSION['employee_no'] = $employee_number;
        
        // Get employee details
        $stmt = $pdo->prepare("SELECT name FROM employees WHERE employee_no = ?");
        $stmt->execute([$employee_number]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($employee) {
            $_SESSION['employee_name'] = $employee['name'];
        }
        
        header("Location: Dashboard.php");
        exit();
    } else {
        $_SESSION['login_error'] = 'Invalid employee number or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yazaki Torres Payslip Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #f0f4f8 0%, #d9e2ec 100%);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
            min-height: 100vh;
            width: 100%;
        }

        .login-container {
            width: 100%;
            max-width: 1200px;
            height: auto;
            display: flex;
            flex-direction: row;
            justify-content: stretch;
            align-items: stretch;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }

        .login-form-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 50px;
            background: #fafbfc;
            min-height: 600px;
        }

        .login-form {
            background: linear-gradient(180deg, #07132a 0%, #0b2140 100%);
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(2, 6, 23, 0.6);
            border: none;
            padding: 36px 30px;
            width: 100%;
            max-width: 380px;
            position: relative;
            color: #fff;
        }

        .login-image-section {
            flex: 1;
            background: linear-gradient(180deg, #07132a 0%, #0b2140 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            min-height: 600px;
        }

        .login-image-section::before {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            background: rgba(255, 255, 255, 0.03);
            border-radius: 50%;
            top: -100px;
            right: -100px;
            box-shadow: inset 0 30px 60px rgba(255,255,255,0.01);
        }

        .login-image-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }

        .company-logo {
            width: 220px;
            height: auto;
            margin: 0 auto 18px auto;
            display: block;
            max-width: 80%;
            filter: drop-shadow(0 18px 36px rgba(0, 0, 0, 0.6));
        }

        /* Right-side hero logo - larger and centered */
        .company-logo-hero {
            width: 320px;
            max-width: 88%;
            height: auto;
            display: block;
            margin: 0 auto 18px auto;
            filter: drop-shadow(0 22px 48px rgba(0,0,0,0.6));
        }

        .hero-title {
            color: rgba(255,255,255,0.95);
            font-size: 22px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 6px;
        }

        .hero-subtitle {
            color: rgba(255,255,255,0.75);
            text-align: center;
            font-size: 14px;
            max-width: 340px;
            margin: 0 auto;
            line-height: 1.4;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 18px;
        }

        .logo-box {
            display: none; /* left logo removed; keep class in case needed later */
        }

        .avatar-circle {
            width: 84px;
            height: 84px;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 14px auto;
            overflow: hidden;
            box-shadow: inset 0 -6px 12px rgba(255,255,255,0.02);
        }

        .avatar-icon {
            width: 44px;
            height: 44px;
            color: rgba(255,255,255,0.95);
            display: block;
        }

        .payslip-title {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: 1px;
            margin-bottom: 6px;
        }

        .subtitle {
            font-size: 13px;
            color: rgba(255,255,255,0.75);
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 22px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: rgba(255,255,255,0.8);
        }

        .textbox1, .textbox2 {
            width: 100%;
            padding: 14px 14px 14px 44px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.2s ease;
            background: rgba(255,255,255,0.04);
            color: #fff;
            -webkit-appearance: none;
            appearance: none;
            height: 48px;
        }

        .textbox1::placeholder, .textbox2::placeholder {
            color: rgba(255,255,255,0.65);
        }

        .textbox1:focus, .textbox2:focus {
            outline: none;
            box-shadow: 0 8px 20px rgba(2,6,23,0.6);
            transform: translateY(-2px);
            background: rgba(255,255,255,0.05);
        }

        .textbox1.error, .textbox2.error {
            border-color: #ef4444;
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
        }

        .error-message {
            color: #ef4444;
            font-size: 14px;
            margin-top: 6px;
            font-weight: 500;
            display: none;
        }

        .password-toggle {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.03);
            border: none;
            cursor: pointer;
            color: rgba(255,255,255,0.85);
            padding: 8px;
            border-radius: 8px;
            transition: all 0.15s;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #6b7280;
            background: #f3f4f6;
        }

        .password-toggle svg {
            width: 16px;
            height: 16px;
        }

        .FP {
            display: block;
            text-align: right;
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            transition: color 0.15s;
            opacity: 0.95;
        }

        .FP:hover {
            color: #dbeafe;
            text-decoration: underline;
        }

        .login-button {
            width: 100%;
            background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
            color: white;
            font-weight: 800;
            padding: 14px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.18s ease;
            box-shadow: 0 12px 30px rgba(37,99,235,0.28);
            position: relative;
            overflow: hidden;
            height: 48px;
        }

        .login-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 18px 36px rgba(37,99,235,0.32);
        }

        .login-button:active {
            transform: translateY(-1px);
        }

        .login-button:disabled {
            background: #93c5fd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .divider {
            position: relative;
            margin: 25px 0;
            text-align: center;
        }

        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #d1d5db;
        }

        .divider-text {
            background: white;
            padding: 0 20px;
            color: #6b7280;
            font-weight: 600;
            font-size: 14px;
        }

        .signup-btn {
            width: 100%;
            background: white;
            color: #374151;
            font-weight: 700;
            padding: 16px;
            border: 2px solid #d1d5db;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            height: 48px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .signup-btn:hover {
            background: #f8fafc;
            border-color: #9ca3af;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transform: translateY(-3px);
        }

        .signup-btn:active {
            transform: translateY(-1px);
        }

        .footer {
            text-align: center;
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            font-size: 12px;
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
            width: auto;
        }

        .loading {
            display: none;
            align-items: center;
            justify-content: center;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid transparent;
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 10px;
        }

        .global-error {
            background: #fef3f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #dc2626;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .global-error::before {
            content: '⚠';
            font-size: 16px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 100%;
                border-radius: 0;
                box-shadow: none;
                min-height: 100vh;
            }

            .login-form-section {
                flex: 1;
                background: white;
                padding: 50px 30px;
                min-height: auto;
            }

            .login-image-section {
                flex: 1;
                min-height: 300px;
                padding: 30px;
            }

            body {
                padding: 0;
            }
            
            .login-form {
                max-width: 100%;
            }
            
            .company-logo {
                width: 200px;
            }
            
            .payslip-title {
                font-size: 24px;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
            
            .textbox1, .textbox2 {
                font-size: 14px;
            }
            
            .login-button, .signup-btn {
                padding: 15px;
                font-size: 15px;
            }

            .footer {
                position: static;
                transform: none;
                color: #6b7280;
                margin-top: 20px;
            }
        }

        /* Very small screens */
        @media (max-width: 480px) {
            .login-form-section {
                padding: 40px 20px;
            }

            .login-image-section::before {
                width: 250px;
                height: 250px;
                top: -50px;
                right: -50px;
            }
            
            .company-logo {
                width: 150px;
            }
            
            .payslip-title {
                font-size: 22px;
            }

            .form-group {
                margin-bottom: 15px;
            }

            .logo-section {
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Form Section (Left) -->
        <div class="login-form-section">
            <div class="login-form">
                <!-- Logo Section -->
                <div class="logo-section">
                    <div class="avatar-circle" aria-hidden="true">
                        <svg class="avatar-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path d="M12 12c2.761 0 5-2.239 5-5s-2.239-5-5-5-5 2.239-5 5 2.239 5 5 5z" fill="currentColor" />
                            <path d="M4 20c0-2.667 4-4 8-4s8 1.333 8 4v1H4v-1z" fill="currentColor" />
                        </svg>
                    </div>
                    <h1 class="payslip-title">Login</h1>
                    <p class="subtitle">Enter your details below</p>
                </div>

                <!-- Display error message if exists -->
                <?php if (isset($_SESSION['login_error'])): ?>
                    <div class="global-error" id="global-error">
                        <?php 
                        echo htmlspecialchars($_SESSION['login_error']);
                        unset($_SESSION['login_error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form id="login-form" method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    
                    <!-- Employee Number Input -->
                    <div class="form-group">
                        <label for="employee_number" class="form-label">Employee Number</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                            <input 
                                type="text" 
                                id="employee_number" 
                                name="employee_number" 
                                placeholder="XX-XXXXX" 
                                class="textbox1" 
                                maxlength="8"
                                required
                                value="<?php echo isset($_POST['employee_number']) ? htmlspecialchars($_POST['employee_number']) : ''; ?>"
                            >
                        </div>
                        <div id="employee-error" class="error-message" style="display: none;"></div>
                    </div>

                    <!-- Password Input -->
                    <div class="form-group">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-wrapper">
                            <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <circle cx="12" cy="16" r="1"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Enter password" 
                                class="textbox2" 
                                required
                            >
                            <button type="button" class="password-toggle" id="password-toggle">
                                <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                        <div id="password-error" class="error-message" style="display: none;"></div>
                    </div>

                    <!-- Forgot Password Link -->
                    <a class="FP" href="ForgotPassword.php">Forgot Password?</a>

                    <!-- Login Button -->
                    <button type="submit" class="login-button" id="login-btn" name="login">
                        <span id="login-text">Login</span>
                        <div class="loading" id="loading">
                            <div class="spinner"></div>
                            Logging In...
                        </div>
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span class="divider-text">or</span>
                </div>

                <!-- Create Account Button -->
                <a href="Signup.php" class="signup-btn">Create Account</a>
            </div>
        </div>

        <!-- Image Section (Right) -->
        <div class="login-image-section">
            <div class="login-image-content">
                <img src="YAZAKI-TORRES-LOGO.webp" alt="Yazaki Torres Logo" class="company-logo-hero">
                <h2 class="hero-title">Welcome to Yazaki Torres</h2>
                <p class="hero-subtitle">Employee Portal — access payslips, download history, and manage your profile.</p>
            </div>
            <!-- Footer in image section -->
            <div class="footer">
                <p>© 2024 Yazaki Torres. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        // Wait for DOM to be fully loaded
        document.addEventListener('DOMContentLoaded', function() {
            // Employee number validation and formatting
            const employeeInput = document.getElementById('employee_number');
            const passwordInput = document.getElementById('password');
            const passwordToggle = document.getElementById('password-toggle');
            const eyeIcon = document.getElementById('eye-icon');
            const loginForm = document.getElementById('login-form');
            const loginBtn = document.getElementById('login-btn');
            const loginText = document.getElementById('login-text');
            const loading = document.getElementById('loading');
            const globalError = document.getElementById('global-error');

            // Format employee number to XX-XXXXX
            function formatEmployeeNumber(value) {
                // Remove all non-numeric characters
                const numericValue = value.replace(/[^0-9]/g, '');
                
                // Limit to 7 digits maximum (XX-XXXXX format)
                const limitedValue = numericValue.slice(0, 7);
                
                // Add dash after 2 digits if there are more than 2 digits
                if (limitedValue.length > 2) {
                    return limitedValue.slice(0, 2) + '-' + limitedValue.slice(2);
                }
                
                return limitedValue;
            }

            // Employee number input handling
            employeeInput.addEventListener('input', function(e) {
                const formatted = formatEmployeeNumber(e.target.value);
                e.target.value = formatted;
                
                // Clear error when user starts typing
                clearError('employee');
                if (globalError) globalError.style.display = 'none';
            });

            // Prevent non-numeric characters from being typed (except dash)
            employeeInput.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && e.key !== 'Tab' && e.key !== 'Enter' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                    e.preventDefault();
                }
            });

            // Password input validation
            passwordInput.addEventListener('input', function() {
                clearError('password');
                if (globalError) globalError.style.display = 'none';
            });

            // Password toggle functionality
            passwordToggle.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                if (type === 'text') {
                    eyeIcon.innerHTML = `
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                        <line x1="1" y1="1" x2="23" y2="23"/>
                    `;
                } else {
                    eyeIcon.innerHTML = `
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                        <circle cx="12" cy="12" r="3"/>
                    `;
                }
            });

            // Form validation
            function validateForm() {
                let isValid = true;
                const employeeNumber = employeeInput.value.trim();
                const password = passwordInput.value.trim();

                // Validate employee number (should be in XX-XXXXX format)
                if (!employeeNumber) {
                    showError('employee', 'Employee number is required');
                    isValid = false;
                } else if (!/^\d{2}-\d{5}$/.test(employeeNumber)) {
                    showError('employee', 'Employee number must be in XX-XXXXX format');
                    isValid = false;
                }

                // Validate password
                if (!password) {
                    showError('password', 'Password is required');
                    isValid = false;
                } else if (password.length < 6) {
                    showError('password', 'Password must be at least 6 characters');
                    isValid = false;
                }

                return isValid;
            }

            function showError(field, message) {
                const errorElement = document.getElementById(field + '-error');
                const inputElement = document.getElementById(field === 'employee' ? 'employee_number' : 'password');
                
                errorElement.textContent = message;
                errorElement.style.display = 'block';
                inputElement.classList.add('error');
            }

            function clearError(field) {
                const errorElement = document.getElementById(field + '-error');
                const inputElement = document.getElementById(field === 'employee' ? 'employee_number' : 'password');
                
                errorElement.style.display = 'none';
                inputElement.classList.remove('error');
            }

            // Form submission
            loginForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!validateForm()) return;
                
                // Show loading state
                loginBtn.disabled = true;
                loginText.style.display = 'none';
                loading.style.display = 'flex';
                
                // Submit the form
                loginForm.submit();
            });
        });
    </script>
</body>
</html>