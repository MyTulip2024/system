<?php
session_start();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forgot_password') {
    handleForgotPassword($pdo);
}

function handleForgotPassword($pdo) {
    $employee_number = $_POST['employee_number'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($employee_number)) {
        $_SESSION['forgot_error'] = 'Employee number is required';
        return;
    }
    
    if (empty($new_password)) {
        $_SESSION['forgot_error'] = 'New password is required';
        return;
    }
    
    if (strlen($new_password) < 6) {
        $_SESSION['forgot_error'] = 'Password must be at least 6 characters';
        return;
    }
    
    if ($new_password !== $confirm_password) {
        $_SESSION['forgot_error'] = 'Passwords do not match';
        return;
    }
    
    // Check if employee exists
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_no = ?");
    $stmt->execute([$employee_number]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$employee) {
        $_SESSION['forgot_error'] = 'Employee number not found in system.';
        return;
    }
    
    // Check if user account exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE employee_no = ?");
    $stmt->execute([$employee_number]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        $_SESSION['forgot_error'] = 'No account found for this employee number. Please sign up first.';
        return;
    }
    
    // Hash the new password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    try {
        // Update password in users table
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE employee_no = ?");
        $stmt->execute([$hashed_password, $employee_number]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['reset_success'] = 'Password reset successfully! You can now login with your new password.';
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['forgot_error'] = 'Failed to update password. Please try again.';
        }
        
    } catch(PDOException $e) {
        $_SESSION['forgot_error'] = 'Error updating password: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Yazaki Torres</title>
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
            min-height: 100vh;
        }

        .forgot-container {
            width: 100%;
            max-width: 400px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .forgot-form {
            background: linear-gradient(180deg, #07132a 0%, #0b2140 100%);
            border-radius: 16px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255,255,255,0.04);
            padding: 35px 25px;
            width: 100%;
            position: relative;
            color: #e6eef9;
        }

        .logo-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .company-logo {
            width: 260px;
            height: auto;
            margin: 0 auto 15px;
            max-width: 100%;
            display: block;
        }

        .forgot-title {
            font-size: 24px;
            font-weight: bold;
            color: #f10b1e;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .forgot-subtitle {
            font-size: 14px;
            color: rgba(230,238,249,0.9);
            font-weight: 500;
            margin-bottom: 25px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: rgba(230,238,249,0.95);
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
            color: rgba(230,238,249,0.6);
        }

        .textbox {
            width: 100%;
            padding: 14px 14px 14px 40px;
            border: 2px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.04);
            height: 48px;
            color: #e6eef9;
        }

        .textbox:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
            transform: translateY(-2px);
        }

        .textbox.error {
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

        .global-error {
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #fca5a5;
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

        .success-message {
            background: rgba(16,185,129,0.1);
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #a7f3d0;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .success-message::before {
            content: '✓';
            font-size: 16px;
        }

        .reset-btn {
            width: 100%;
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            color: white;
            font-weight: 700;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 10px 25px -5px rgba(30, 64, 175, 0.3);
            height: 48px;
            margin-top: 10px;
        }

        .reset-btn:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            box-shadow: 0 20px 35px -5px rgba(30, 64, 175, 0.4);
            transform: translateY(-3px);
        }

        .reset-btn:disabled {
            background: #93c5fd;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .back-to-login {
            text-align: center;
            margin-top: 25px;
        }

        .back-to-login a {
            color: #bfdbfe;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: color 0.2s;
        }

        .back-to-login a:hover {
            color: #dbeafe;
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: rgba(230,238,249,0.6);
            font-weight: 500;
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

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .password-requirements {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #64748b;
        }

        .password-requirements h4 {
            color: #374151;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .password-requirements ul {
            padding-left: 20px;
            margin: 5px 0;
        }

        .password-requirements li {
            margin-bottom: 3px;
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .forgot-container {
                max-width: 100%;
            }
            
            .forgot-form {
                padding: 30px 20px;
                border-radius: 14px;
            }
            
            .company-logo {
                width: 200px;
                margin-bottom: 12px;
            }
            
            .forgot-title {
                font-size: 22px;
                letter-spacing: 1px;
            }
            
            .form-group {
                margin-bottom: 18px;
            }
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-form">
            <div class="logo-section">
                <h1 class="forgot-title">RESET PASSWORD</h1>
                <p class="forgot-subtitle">Enter your employee number and new password</p>
            </div>

            <?php if (isset($_SESSION['forgot_error'])): ?>
                <div class="global-error" id="global-error">
                    <?php 
                    echo htmlspecialchars($_SESSION['forgot_error']);
                    unset($_SESSION['forgot_error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['reset_success'])): ?>
                <div class="success-message" id="success-message">
                    <?php 
                    echo htmlspecialchars($_SESSION['reset_success']);
                    unset($_SESSION['reset_success']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="password-requirements">
                <h4>Password Requirements:</h4>
                <ul>
                    <li>At least 6 characters long</li>
                    <li>Use a combination of letters and numbers</li>
                    <li>Avoid using common passwords</li>
                </ul>
            </div>

            <form id="forgot-form" method="POST" action="">
                <input type="hidden" name="action" value="forgot_password">
                
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
                            class="textbox" 
                            maxlength="8"
                            required
                            value="<?php echo isset($_POST['employee_number']) ? htmlspecialchars($_POST['employee_number']) : ''; ?>"
                        >
                    </div>
                    <div id="employee-error" class="error-message" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <circle cx="12" cy="16" r="1"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            placeholder="Enter new password" 
                            class="textbox" 
                            required
                            minlength="6"
                        >
                    </div>
                    <div id="password-error" class="error-message" style="display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <circle cx="12" cy="16" r="1"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm new password" 
                            class="textbox" 
                            required
                            minlength="6"
                        >
                    </div>
                    <div id="confirm-error" class="error-message" style="display: none;"></div>
                </div>

                <button type="submit" class="reset-btn" id="reset-btn" name="reset_password">
                    <span id="reset-text">Reset Password</span>
                    <div class="loading" id="loading">
                        <div class="spinner"></div>
                        Resetting...
                    </div>
                </button>
            </form>

            <div class="back-to-login">
                <a href="Login.php">Back to Login</a>
            </div>
        </div>

        <div class="footer">
            <p>© 2024 Yazaki Torres. All rights reserved.</p>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const employeeInput = document.getElementById('employee_number');
            const newPasswordInput = document.getElementById('new_password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const resetBtn = document.getElementById('reset-btn');
            const resetText = document.getElementById('reset-text');
            const loading = document.getElementById('loading');
            const globalError = document.getElementById('global-error');
            const successMessage = document.getElementById('success-message');

            // Format employee number to XX-XXXXX
            function formatEmployeeNumber(value) {
                const numericValue = value.replace(/[^0-9]/g, '');
                const limitedValue = numericValue.slice(0, 7);
                if (limitedValue.length > 2) {
                    return limitedValue.slice(0, 2) + '-' + limitedValue.slice(2);
                }
                return limitedValue;
            }

            employeeInput.addEventListener('input', function(e) {
                const formatted = formatEmployeeNumber(e.target.value);
                e.target.value = formatted;
                clearError('employee');
                hideMessages();
            });

            employeeInput.addEventListener('keypress', function(e) {
                if (!/[0-9]/.test(e.key) && e.key !== 'Backspace' && e.key !== 'Delete' && 
                    e.key !== 'Tab' && e.key !== 'Enter' && e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') {
                    e.preventDefault();
                }
            });

            // Password validation
            newPasswordInput.addEventListener('input', function() {
                clearError('password');
                hideMessages();
                
                // Validate password length
                if (this.value.length > 0 && this.value.length < 6) {
                    showError('password', 'Password must be at least 6 characters');
                }
                
                // Check password confirmation
                validatePasswordMatch();
            });

            confirmPasswordInput.addEventListener('input', function() {
                clearError('confirm');
                hideMessages();
                validatePasswordMatch();
            });

            function validatePasswordMatch() {
                const password = newPasswordInput.value;
                const confirm = confirmPasswordInput.value;
                
                if (password && confirm && password !== confirm) {
                    showError('confirm', 'Passwords do not match');
                }
            }

            function validateForm() {
                let isValid = true;
                const employeeNumber = employeeInput.value.trim();
                const password = newPasswordInput.value.trim();
                const confirm = confirmPasswordInput.value.trim();

                // Validate employee number
                if (!employeeNumber) {
                    showError('employee', 'Employee number is required');
                    isValid = false;
                } else if (!/^\d{2}-\d{5}$/.test(employeeNumber)) {
                    showError('employee', 'Employee number must be in XX-XXXXX format');
                    isValid = false;
                }

                // Validate password
                if (!password) {
                    showError('password', 'New password is required');
                    isValid = false;
                } else if (password.length < 6) {
                    showError('password', 'Password must be at least 6 characters');
                    isValid = false;
                }

                // Validate password confirmation
                if (!confirm) {
                    showError('confirm', 'Please confirm your password');
                    isValid = false;
                } else if (password !== confirm) {
                    showError('confirm', 'Passwords do not match');
                    isValid = false;
                }

                return isValid;
            }

            function showError(field, message) {
                const errorElement = document.getElementById(field + '-error');
                const inputElement = document.getElementById(field === 'employee' ? 'employee_number' : 
                                                            field === 'password' ? 'new_password' : 'confirm_password');
                
                errorElement.textContent = message;
                errorElement.style.display = 'block';
                inputElement.classList.add('error');
            }

            function clearError(field) {
                const errorElement = document.getElementById(field + '-error');
                const inputElement = document.getElementById(field === 'employee' ? 'employee_number' : 
                                                            field === 'password' ? 'new_password' : 'confirm_password');
                
                errorElement.style.display = 'none';
                inputElement.classList.remove('error');
            }

            function hideMessages() {
                if (globalError) globalError.style.display = 'none';
                if (successMessage) successMessage.style.display = 'none';
            }

            // Form submission
            document.getElementById('forgot-form').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                if (!validateForm()) return;
                
                // Show loading state
                resetBtn.disabled = true;
                resetText.style.display = 'none';
                loading.style.display = 'flex';
                
                // Submit the form
                this.submit();
            });

            // Clear errors when inputs gain focus
            const inputs = [employeeInput, newPasswordInput, confirmPasswordInput];
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    const field = this.id === 'employee_number' ? 'employee' : 
                                 this.id === 'new_password' ? 'password' : 'confirm';
                    clearError(field);
                    hideMessages();
                });
            });

            // Show password requirements when password field is focused
            newPasswordInput.addEventListener('focus', function() {
                document.querySelector('.password-requirements').style.display = 'block';
            });
        });
    </script>
</body>
</html>