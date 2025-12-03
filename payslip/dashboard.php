<?php
// Start session and check if user is logged in
session_start();
if (!isset($_SESSION['employee_no'])) {
    header("Location: Login.php");
    exit();
}

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

// Get employee data
$employee_no = $_SESSION['employee_no'];
$employee = null;
$latest_payslip = null;
$attendance_stats = null;

try {
    // Get employee basic info
    $stmt = $pdo->prepare("SELECT * FROM employees WHERE employee_no = ?");
    $stmt->execute([$employee_no]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($employee) {
        // Get latest payslip
        $stmt = $pdo->prepare("SELECT * FROM payroll_records WHERE employee_no = ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute([$employee_no]);
        $latest_payslip = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get attendance statistics
        $stmt = $pdo->prepare("
            SELECT 
                COUNT(*) as days_present,
                SUM(TIME_TO_SEC(TIMEDIFF(time_out, time_in))/3600) as hours_worked
            FROM attendance 
            WHERE employee_no = ?
            AND status = 'Present'
            AND date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ");
        $stmt->execute([$employee_no]);
        $attendance_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    }
} catch(PDOException $e) {
    error_log("Database error: " . $e->getMessage());
}

// Format currency function
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// Calculate attendance rate
if ($attendance_stats && $attendance_stats['days_present'] > 0) {
    $days_present = $attendance_stats['days_present'];
    $hours_worked = round($attendance_stats['hours_worked'] ?? 0);
    $attendance_rate = round(($days_present / 22) * 100);
} else {
    $days_present = 22;
    $hours_worked = 176;
    $attendance_rate = 98;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Employee Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #07132a 0%, #023173ff 50%, #07132a 100%);
            color: #e6eef9;
        }
        .dashboard {
            max-width: 400px; /* narrow to match the Login card */
            width: 100%;
            min-height: 100vh;
            margin: 24px auto;
            background: linear-gradient(180deg, #07132a 0%, #0b2140 100%);
            box-shadow: 0 20px 60px rgba(2,6,23,0.35);
            border-radius: 18px;
            overflow: hidden;
            color: #e6eef9;
        }
        
        .header {
            background: linear-gradient(180deg, #07132a 0%, #0b2140 100%);
            padding: 18px 20px;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 6px 24px rgba(2,6,23,0.12);
            color: #fff;
        }
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 5px;
        }
        .nav-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255,255,255,0.06);
            border: none;
            cursor: pointer;
            transition: transform 0.15s, background 0.15s;
        }
        .nav-icon:hover {
            transform: translateY(-2px);
            background: rgba(255,255,255,0.1);
        }
        .nav-icon i {
            font-size: 18px;
            color: #e6eef9;
        }
        .title {
            text-align: center;
            flex: 1;
        }
        .title h1 {
            font-size: 20px;
            font-weight: 700;
            color: #e6eef9;
            margin-bottom: 4px;
        }
        .title .time-display {
            color: rgba(230,238,249,0.8);
        }
        .time-display {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            color: rgba(230,238,249,0.9);
            font-family: 'Courier New', monospace;
        }
        /* Main Content */
        .main-content {
            padding: 16px 8px 24px 8px;
        }
        .date-display {
            text-align: center;
            color: rgba(230,238,249,0.9);
            font-size: 14px;
            margin-bottom: 2px;
        }
        /* Profile Card */
        .profile-card {
            background: rgba(255,255,255,0.03);
            border-radius: 14px;
            padding: 26px;
            box-shadow: 0 10px 30px rgba(2,6,23,0.25);
            margin-bottom: 24px;
            transition: box-shadow 0.3s, transform 0.18s;
            border: 1px solid rgba(255,255,255,0.04);
        }
        .profile-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .profile-content {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .profile-avatar {
            position: relative;
        }
        .avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            border: 4px solid #fff;
            box-shadow: 0 10px 30px rgba(2,6,23,0.12);
            object-fit: cover;
            background: #f8fafc;
        }
        .status-indicator {
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 15px;
            height: 15px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }
        .profile-info {
            flex: 1;
            padding: 0px;
        }
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #e6eef9;
            margin-bottom: 4px;
        }
        .profile-role {
            color: rgba(230,238,249,0.95);
            margin-bottom: 12px;
        }
        .badges {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 12px;
        }
        .badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .badge-blue {
            background: linear-gradient(135deg, rgba(219,234,254,0.1), rgba(191,219,254,0.1));
            color: #ffffff;
        }
        .badge-green {
            background: linear-gradient(135deg, rgba(220,252,231,0.1), rgba(187,247,208,0.1));
            color: #ffffff;
        }
        .edit-btn {
            background: rgba(255, 255, 255, 0.5);
            border: 1px solid #e2e8f0;
            padding: 4px 8px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .edit-btn:hover {
            background: rgba(255, 255, 255, 0.8);
            border-color: #cbd5e1;
        }
        /* Action Buttons */
        .action-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .action-card {
            border-radius: 14px;
            padding: 14px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.18s, box-shadow 0.18s;
            border: none;
            box-shadow: 0 10px 30px rgba(2,6,23,0.2);
            background: rgba(255,255,255,0.02);
            color: #e6eef9;
        }
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 18px 44px rgba(2,6,23,0.08);
        }
        .history-card {
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: white;
        }
        .deductions-card {
            background: linear-gradient(135deg, #7c3aed, #a78bfa);
            color: white;
        }
        .action-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            transition: background-color 0.2s;
        }
        .action-card:hover .action-icon {
            background: rgba(255, 255, 255, 0.3);
        }
        .action-icon i {
            font-size: 16px;
        }
        .action-title {
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 2px;
            color: #e6eef9;
        }
        .action-subtitle {
            font-size: 11px;
            opacity: 1;
            color: rgba(230,238,249,0.95);
        }
        /* Payslip Section */
        .salary-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.04);
            box-shadow: 0 10px 30px rgba(2,6,23,0.22);
            border-radius: 14px;
            margin-bottom: 16px;
            overflow: hidden;
        }
        .salary-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-bottom: 2px solid #e5e7eb;
        }
        .salary-title {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .salary-title i {
            font-size: 20px;
            color: #bfdbfe;
            background: rgba(59,130,246,0.08);
            padding: 8px;
            border-radius: 10px;
        }
        .salary-title h2 {
            font-size: 18px;
            font-weight: 700;
            color: #e6eef9;
            margin: 0;
        }
        .new-badge {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 4px;
            animation: bounce 2s infinite;
        }
        @keyframes bounce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-2px);
            }
        }
        /* Payslip Table */
        .payslip-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            background: transparent;
            padding: 16px;
        }
        .payslip-table td {
            padding: 14px 0;
            border-bottom: 1px solid rgba(255,255,255,0.03);
        }
        .payslip-table td:first-child {
            color: rgba(230,238,249,0.95);
            width: 50%;
        }
        .payslip-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #e6eef9;
        }
        .payslip-table tr:nth-child(1) td {
            text-align: center;
            font-size: 18px;
            padding: 12px 0 20px 0;
            font-weight: 700;
            color: #e6eef9;
            border: none;
        }
        .payslip-table tr.amount-row td {
            background: rgba(255,255,255,0.02);
            font-weight: 700;
        }
        .payslip-table tr.net-pay-row td {
            padding: 18px 0;
            border-top: 2px solid rgba(59,130,246,0.18);
            border-bottom: none;
            font-size: 17px;
        }
        .payslip-table tr.net-pay-row td:last-child {
            color: #bbf7d0;
        }
        /* Separator */
        .section-separator {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 24px 0 20px 0;
            position: relative;
        }
        .separator-line {
            flex: 1;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, transparent);
        }
        .separator-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 16px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        .separator-icon i {
            color: white;
            font-size: 16px;
        }
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
        }
        .stat-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.03);
            border-radius: 12px;
            padding: 16px;
            text-align: center;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #e6eef9;
            margin-bottom: 4px;
        }
        .stat-value.green {
            color: #bbf7d0;
        }
        .stat-label {
            font-size: 12px;
            color: rgba(230,238,249,0.95);
        }
        /* Download Button */
        .download-btn-wrapper {
            position: relative;
        }
        .download-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            width: 18px;
            height: 18px;
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            color: white;
            font-weight: bold;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            }
            50% {
                transform: scale(1.1);
                box-shadow: 0 3px 12px rgba(239, 68, 68, 0.6);
            }
            100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
            }
        }
        @media (max-width: 360px) {
            .profile-content {
                flex-direction: column;
                text-align: center;
                gap: 16px;
            }
            .action-buttons {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 480px) {
            .dashboard {
                max-width: 100vw;
                min-height: 100vh;
                border-radius: 0;
                margin: 0;
                box-shadow: none;
            }
            .main-content {
                padding: 8px 2px 16px 2px;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <button class="nav-icon" onclick="confirmLogout()">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <div class="title">
                    <h1>Employee Dashboard</h1>
                    <div class="time-display">
                        <i class="fas fa-clock"></i>
                        <span id="current-time">12:02:00</span>
                    </div>
                </div>
                <div class="download-btn-wrapper">
                    <button class="nav-icon" id="download-payslip-btn">
                        <i class="fas fa-download"></i>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Date Display -->
            <div class="date-display" id="current-date">
                Monday, January 15, 2024
            </div>

            <!-- Profile Card -->
            <div class="profile-card">
                <div class="profile-content">
                    <div class="profile-avatar">
                        <img id="profile-avatar" src="<?php echo htmlspecialchars($employee['profile_picture'] ?? 'https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=150&h=150&fit=crop'); ?>" alt="Profile" class="avatar">
                        <div class="status-indicator"></div>
                    </div>
                    <div class="profile-info">
                        <h2 class="profile-name" id="profile-name"><?php echo htmlspecialchars($employee['name'] ?? 'Employee Name'); ?></h2>
                        <p class="profile-role"><?php echo htmlspecialchars($employee['department_name'] ?? 'Department'); ?></p>
                        <div class="badges">
                            <span class="badge badge-blue">
                                <i class="fas fa-hashtag"></i>
                                <span id="employee-number"><?php echo htmlspecialchars($employee['employee_no'] ?? 'EMP-XXXX-XXX'); ?></span>
                            </span>
                            <span class="badge badge-green">
                                <i class="fas fa-building"></i>
                                <span id="department"><?php echo htmlspecialchars($employee['status'] ?? 'Status'); ?></span>
                            </span>
                        </div>
                        <button class="edit-btn" onclick="window.location.href='ChangeProfile.php'">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="action-card history-card" onclick="window.location.href='History.php'">
                    <div class="action-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3 class="action-title">History</h3>
                    <p class="action-subtitle">View past payslips</p>
                </button>
                <button class="action-card deductions-card" onclick="window.location.href='Deductions.php'">
                    <div class="action-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="action-title">Deductions</h3>
                    <p class="action-subtitle">View deductions</p>
                </button>
            </div>

            <!-- Decorative Separator -->
            <div class="section-separator">
                <div class="separator-line"></div>
                <div class="separator-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="separator-line"></div>
            </div>

            <!-- Payslip Section -->
            <div class="salary-card">
                <div class="salary-card-header">
                    <div class="salary-title">
                        <i class="fas fa-file-invoice"></i>
                        <h2>Weekly Payslip</h2>
                    </div>
                    <div class="new-badge">
                        <i class="fas fa-star"></i>
                        NEW
                    </div>
                </div>
                <table class="payslip-table">
                    <tbody>
                        <tr>
                            <td colspan="2">YAZAKI TORRES MANUFACTURING INCORPORATED</td>
                        </tr>
                        <tr>
                            <td>Employee Name:</td>
                            <td id="payslip-employee-name"><strong><?php echo htmlspecialchars($employee['name'] ?? 'Employee Name'); ?></strong></td>
                        </tr>
                        <tr>
                            <td>Employee ID:</td>
                            <td id="payslip-employee-id"><?php echo htmlspecialchars($employee['employee_no'] ?? 'EMP-XXXX-XXX'); ?></td>
                        </tr>
                        <tr>
                            <td>Position:</td>
                            <td id="payslip-position"><?php echo htmlspecialchars($employee['department_name'] ?? 'Position'); ?></td>
                        </tr>
                        <tr>
                            <td>Pay Week:</td>
                            <td id="payslip-payweek"><?php echo htmlspecialchars($latest_payslip['week_period'] ?? 'YYYY-MM-DD to YYYY-MM-DD'); ?></td>
                        </tr>
                        <tr class="amount-row">
                            <td>Basic Salary:</td>
                            <td id="payslip-basic-salary"><?php echo formatCurrency($latest_payslip['basic_salary'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td>Overtime Pay:</td>
                            <td id="payslip-overtime"><?php echo formatCurrency($latest_payslip['overtime_pay'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td>Allowances:</td>
                            <td id="payslip-allowances"><?php echo formatCurrency($latest_payslip['allowances'] ?? 0); ?></td>
                        </tr>
                        <tr class="amount-row">
                            <td>Gross Pay:</td>
                            <td id="payslip-gross"><?php echo formatCurrency($latest_payslip['gross_pay'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td>Pag-ibig:</td>
                            <td id="payslip-pagibig"><?php echo formatCurrency($latest_payslip['pagibig'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td>SSS:</td>
                            <td id="payslip-sss"><?php echo formatCurrency($latest_payslip['sss'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td>PhilHealth:</td>
                            <td id="payslip-philhealth"><?php echo formatCurrency($latest_payslip['philhealth'] ?? 0); ?></td>
                        </tr>
                        <tr class="net-pay-row">
                            <td><strong>Net Pay:</strong></td>
                            <td id="payslip-netpay"><?php echo formatCurrency($latest_payslip['net_salary'] ?? 0); ?></td>
                        </tr>
                        <tr>
                            <td>Generated on:</td>
                            <td id="payslip-generated"><?php echo date('m/d/Y'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?php echo $days_present; ?></div>
                    <div class="stat-label">Days Present</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?php echo $hours_worked; ?></div>
                    <div class="stat-label">Hours Worked</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value green"><?php echo $attendance_rate; ?>%</div>
                    <div class="stat-label">Attendance Rate</div>
                </div>
            </div>
        </main>
    </div>

    <!-- Add jsPDF libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script>
        // Confirm logout
        function confirmLogout() {
            if (confirm('Are you sure you want to log out?')) {
                window.location.href = 'Logout.php';
            }
        }

        // Download salary as PDF
        function downloadSalaryPDF() {
            try {
                const { jsPDF } = window.jspdf;
                const doc = new jsPDF();

                // Get current date and time
                const now = new Date();
                const dateStr = now.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });

                // Header (company and payslip title)
                doc.setFontSize(16);
                doc.text('YAZAKI TORRES MANUFACTURING INCORPORATED', 105, 18, { align: 'center' });
                doc.setFontSize(13);
                doc.text('Weekly Payslip', 105, 28, { align: 'center' });

                // Gather payslip data
                const getVal = id => {
                    const el = document.getElementById(id);
                    if (!el) throw new Error('Missing element: ' + id);
                    return el.textContent;
                };

                // Function to convert peso sign for PDF
                const convertPeso = (value) => {
                    return value.replace('₱', 'P');
                };

                const details = [
                    ['Employee Name:', getVal('payslip-employee-name')],
                    ['Employee ID:', getVal('payslip-employee-id')],
                    ['Position:', getVal('payslip-position')],
                    ['Pay Week:', getVal('payslip-payweek')],
                    ['Basic Salary:', convertPeso(getVal('payslip-basic-salary'))],
                    ['Overtime Pay:', convertPeso(getVal('payslip-overtime'))],
                    ['Allowances:', convertPeso(getVal('payslip-allowances'))],
                    ['Gross Pay:', convertPeso(getVal('payslip-gross'))],
                    ['Pag-ibig:', convertPeso(getVal('payslip-pagibig'))],
                    ['SSS:', convertPeso(getVal('payslip-sss'))],
                    ['PhilHealth:', convertPeso(getVal('payslip-philhealth'))]
                ];

                let y = 42;
                doc.setFontSize(12);
                details.forEach(([label, value]) => {
                    doc.text(label, 14, y);
                    doc.text(value, 70, y);
                    y += 9;
                });

                // Draw a horizontal line before Net Pay
                y += 4;
                doc.setDrawColor(37, 99, 235);
                doc.setLineWidth(0.8);
                doc.line(14, y, 196, y);
                y += 10;

                // Net Pay (bold, green)
                doc.setFontSize(13);
                doc.setFont(undefined, 'bold');
                doc.setTextColor(5, 150, 105);
                doc.text('Net Pay:', 14, y);
                doc.text(convertPeso(getVal('payslip-netpay')), 70, y);
                doc.setFont(undefined, 'normal');
                doc.setTextColor(33, 37, 41);
                y += 12;

                // Generated on
                doc.setFontSize(11);
                doc.text('Generated on:', 14, y);
                doc.text(getVal('payslip-generated'), 70, y);
                y += 10;

                // Footer
                const pageCount = doc.internal.getNumberOfPages();
                for (let i = 1; i <= pageCount; i++) {
                    doc.setPage(i);
                    doc.setFontSize(8);
                    doc.text(
                        'This is a computer-generated document. No signature is required.',
                        14,
                        doc.internal.pageSize.height - 10
                    );
                    doc.text(
                        'Page ' + i + ' of ' + pageCount,
                        doc.internal.pageSize.width - 20,
                        doc.internal.pageSize.height - 10,
                        { align: 'right' }
                    );
                }

                // Download the PDF
                doc.save('weekly_payslip_' + dateStr.replace(/,/g, '') + '.pdf');
            } catch (err) {
                alert('Error generating PDF: ' + err.message);
            }
        }

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('en-US', {
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
            document.getElementById('current-time').textContent = timeString;
        }

        // Update current date
        function updateDate() {
            const now = new Date();
            const dateString = now.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            document.getElementById('current-date').textContent = dateString;
        }

        // Initialize time and date
        updateTime();
        updateDate();
        setInterval(updateTime, 1000);

        // Enhanced download button
        document.addEventListener('DOMContentLoaded', function() {
            const downloadBtn = document.getElementById('download-payslip-btn');
            if (downloadBtn) {
                downloadBtn.addEventListener('click', async function() {
                    try {
                        const originalHTML = this.innerHTML;
                        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                        this.style.transform = 'scale(0.9)';
                        this.style.background = 'rgba(59, 130, 246, 0.1)';

                        await new Promise(resolve => setTimeout(resolve, 500));
                        downloadSalaryPDF();

                        setTimeout(() => {
                            this.innerHTML = '<i class="fas fa-check"></i>';
                            this.style.background = 'rgba(16, 185, 129, 0.1)';

                            const badge = document.querySelector('.download-badge');
                            if (badge) {
                                badge.style.display = 'none';
                            }

                            setTimeout(() => {
                                this.innerHTML = originalHTML;
                                this.style.background = '';
                                this.style.transform = '';
                            }, 2000);
                        }, 1000);
                    } catch (error) {
                        console.error('Error downloading payslip:', error);
                        this.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
                        this.style.background = 'rgba(239, 68, 68, 0.1)';

                        setTimeout(() => {
                            this.innerHTML = '<i class="fas fa-download"></i>';
                            this.style.background = '';
                            this.style.transform = '';
                        }, 2000);

                        alert('Failed to download payslip. Please try again later.');
                    }
                });
            }
        });

        // Prevent zoom on double-tap for mobile
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Handle orientation changes
        window.addEventListener('orientationchange', function() {
            setTimeout(function() {
                window.dispatchEvent(new Event('resize'));
            }, 100);
        });
    </script>
</body>
</html>