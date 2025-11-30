-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 29, 2025 at 03:25 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `employee_managements`
--

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `icon` varchar(10) DEFAULT '?',
  `text` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `icon`, `text`, `created_at`) VALUES
(1, '?', 'Added new employee: Shaira Dadap (22-06554) (Status: Probationary - will auto-promote after 6 months)', '2025-11-29 12:38:33'),
(2, '?', 'Updated employee: Shaira Dadap (22-06554)', '2025-11-29 12:38:58'),
(3, '?', 'Updated employee: Shaira Dadap (22-06554)', '2025-11-29 12:39:56');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `username`, `password`, `created_at`) VALUES
(1, 'admin', 'admin123', '2025-11-29 09:12:01');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `time_in` time DEFAULT NULL,
  `time_out` time DEFAULT NULL,
  `status` enum('Present','Absent','Late') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_no`, `date`, `time_in`, `time_out`, `status`, `created_at`) VALUES
(2, '22-06554', '2025-11-24', '06:30:00', '17:00:00', 'Present', '2025-11-29 12:55:40'),
(3, '22-06554', '2025-11-25', '06:25:00', '17:00:00', 'Present', '2025-11-29 12:55:58'),
(4, '22-06554', '2025-11-26', '06:43:00', '17:00:00', 'Present', '2025-11-29 12:56:16'),
(5, '22-06554', '2025-11-27', '06:10:00', '18:00:00', 'Present', '2025-11-29 12:56:32'),
(6, '22-06554', '2025-11-28', '06:30:00', '17:00:00', 'Present', '2025-11-29 12:56:48'),
(7, '22-06554', '2025-11-29', '06:44:00', '17:30:00', 'Present', '2025-11-29 12:57:07');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `created_at`) VALUES
(1, 'Ford 1', '2025-11-29 12:38:04'),
(2, 'Ford 2', '2025-11-29 12:38:04'),
(3, 'Nissan 1', '2025-11-29 12:38:04'),
(4, 'Nissan 3', '2025-11-29 12:38:04'),
(5, 'Nissan 4', '2025-11-29 12:38:04'),
(6, 'Ytmci', '2025-11-29 12:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `department_name` varchar(50) NOT NULL,
  `date_hired` date NOT NULL,
  `basic_pay` decimal(10,2) NOT NULL,
  `fixed_allowance` decimal(10,2) DEFAULT 0.00,
  `fixed_deduction` decimal(10,2) DEFAULT 0.00,
  `profile_picture` varchar(255) DEFAULT NULL,
  `shift` varchar(20) DEFAULT 'Morning',
  `status` enum('Probationary','Regular','Resigned','Terminated') DEFAULT 'Probationary',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `employee_no`, `name`, `department_name`, `date_hired`, `basic_pay`, `fixed_allowance`, `fixed_deduction`, `profile_picture`, `shift`, `status`, `created_at`) VALUES
(4, '22-06554', 'Shaira Dadap', 'Ford 1', '2025-11-24', 479.00, 0.00, 0.00, NULL, 'Shift B', 'Probationary', '2025-11-29 12:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `employee_pay_rates`
--

CREATE TABLE `employee_pay_rates` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(50) NOT NULL,
  `daily_rate` decimal(10,2) DEFAULT 645.00,
  `hourly_rate` decimal(10,2) DEFAULT 80.64,
  `ot_rate` decimal(10,2) DEFAULT 80.64
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `date` date NOT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otps`
--

CREATE TABLE `password_reset_otps` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `week_period` varchar(50) NOT NULL,
  `working_hours` decimal(5,2) NOT NULL,
  `days_worked` int(11) NOT NULL,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `basic_salary` decimal(10,2) NOT NULL,
  `overtime_pay` decimal(10,2) DEFAULT 0.00,
  `allowances` decimal(10,2) DEFAULT 0.00,
  `deductions` decimal(10,2) DEFAULT 0.00,
  `net_salary` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','Paid') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_history`
--

CREATE TABLE `payroll_history` (
  `id` int(11) NOT NULL,
  `payroll_record_id` int(11) NOT NULL,
  `approved_by` varchar(100) NOT NULL,
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_records`
--

CREATE TABLE `payroll_records` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(50) NOT NULL,
  `employee_name` varchar(100) NOT NULL,
  `department` varchar(100) DEFAULT NULL,
  `shift` varchar(50) DEFAULT NULL,
  `week_period` varchar(50) NOT NULL,
  `working_hours` decimal(10,2) NOT NULL,
  `days_worked` varchar(10) NOT NULL,
  `overtime_hours` decimal(10,2) NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `overtime_pay` decimal(10,2) NOT NULL,
  `allowances` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pagibig` decimal(10,2) NOT NULL,
  `sss` decimal(10,2) NOT NULL,
  `philhealth` decimal(10,2) NOT NULL,
  `gross_pay` decimal(10,2) NOT NULL,
  `total_deductions` decimal(10,2) NOT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `deleted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_records`
--

INSERT INTO `payroll_records` (`id`, `employee_no`, `employee_name`, `department`, `shift`, `week_period`, `working_hours`, `days_worked`, `overtime_hours`, `basic_salary`, `overtime_pay`, `allowances`, `pagibig`, `sss`, `philhealth`, `gross_pay`, `total_deductions`, `net_salary`, `created_at`, `deleted`) VALUES
(1, '22-06554', 'Shaira Dadap', 'Ford 1', 'Shift B', '2025-11-24 to 2025-11-30', 48.00, '6', 7.50, 3600.00, 562.50, 30.00, 156.00, 283.14, 214.50, 4192.50, 653.64, 3538.86, '2025-11-29 13:43:22', 0);

-- --------------------------------------------------------

--
-- Table structure for table `payroll_settings`
--

CREATE TABLE `payroll_settings` (
  `id` int(11) NOT NULL,
  `sss_rate` decimal(5,2) DEFAULT 3.63,
  `philhealth_rate` decimal(5,2) DEFAULT 2.75,
  `pagibig_rate` decimal(5,2) DEFAULT 2.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_settings`
--

INSERT INTO `payroll_settings` (`id`, `sss_rate`, `philhealth_rate`, `pagibig_rate`) VALUES
(1, 3.63, 2.75, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `thirteenth_month_pay`
--

CREATE TABLE `thirteenth_month_pay` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `year` int(11) NOT NULL,
  `months_worked` int(11) NOT NULL,
  `basic_pay` decimal(10,2) NOT NULL,
  `thirteenth_pay` decimal(10,2) NOT NULL,
  `status` enum('Pending','Approved','Paid') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `employee_no`, `password`, `created_at`) VALUES
(1, '22-06554', '$2y$10$u6j9FfwWllvRldZBv8kojOypDKEcJcu53Wtl00aPLM8Idyy4X.QpK', '2025-11-29 13:54:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_attendance` (`employee_no`,`date`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD KEY `department_name` (`department_name`);

--
-- Indexes for table `employee_pay_rates`
--
ALTER TABLE `employee_pay_rates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_employee_no` (`employee_no`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `date` (`date`);

--
-- Indexes for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_no` (`employee_no`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_payroll` (`employee_no`,`week_period`);

--
-- Indexes for table `payroll_history`
--
ALTER TABLE `payroll_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payroll_record_id` (`payroll_record_id`);

--
-- Indexes for table `payroll_records`
--
ALTER TABLE `payroll_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_payroll_records_employee` (`employee_no`);

--
-- Indexes for table `payroll_settings`
--
ALTER TABLE `payroll_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `thirteenth_month_pay`
--
ALTER TABLE `thirteenth_month_pay`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_thirteenth_month` (`employee_no`,`year`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `employee_pay_rates`
--
ALTER TABLE `employee_pay_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_history`
--
ALTER TABLE `payroll_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_records`
--
ALTER TABLE `payroll_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payroll_settings`
--
ALTER TABLE `payroll_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `thirteenth_month_pay`
--
ALTER TABLE `thirteenth_month_pay`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `fk_attendance_employee` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`) ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`department_name`) REFERENCES `departments` (`name`);

--
-- Constraints for table `employee_pay_rates`
--
ALTER TABLE `employee_pay_rates`
  ADD CONSTRAINT `fk_employee_pay_rates` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`) ON DELETE CASCADE;

--
-- Constraints for table `password_reset_otps`
--
ALTER TABLE `password_reset_otps`
  ADD CONSTRAINT `password_reset_otps_ibfk_1` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `fk_payroll_employee` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`);

--
-- Constraints for table `payroll_history`
--
ALTER TABLE `payroll_history`
  ADD CONSTRAINT `fk_payroll_history` FOREIGN KEY (`payroll_record_id`) REFERENCES `payroll_records` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll_records`
--
ALTER TABLE `payroll_records`
  ADD CONSTRAINT `fk_payroll_records_employee` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`);

--
-- Constraints for table `thirteenth_month_pay`
--
ALTER TABLE `thirteenth_month_pay`
  ADD CONSTRAINT `fk_thirteenth_month_pay` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_employee` FOREIGN KEY (`employee_no`) REFERENCES `employees` (`employee_no`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
