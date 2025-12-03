<?php
// save_profile_picture.php
session_start();

// Set header to JSON
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['employee_no'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
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
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit();
}

// Get employee number from session
$employee_no = $_SESSION['employee_no'];

// Check if file was uploaded
if (!isset($_FILES['profile_picture'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit();
}

$file = $_FILES['profile_picture'];

// Check for errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'Upload error: ' . $file['error']]);
    exit();
}

// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($file['type'], $allowed_types)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit();
}

// Validate file size (max 5MB)
$max_size = 5 * 1024 * 1024;
if ($file['size'] > $max_size) {
    echo json_encode(['success' => false, 'message' => 'File is too large. Maximum size is 5MB.']);
    exit();
}

// Create upload directory if it doesn't exist
$upload_dir = 'uploads/profile_pictures/';
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Generate unique filename
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$filename = 'profile_' . $employee_no . '_' . time() . '.' . $file_extension;
$destination = $upload_dir . $filename;

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $destination)) {
    // Update database
    try {
        // First, get old profile picture to delete it later
        $stmt = $pdo->prepare("SELECT profile_picture FROM employees WHERE employee_no = ?");
        $stmt->execute([$employee_no]);
        $old_profile = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Update with new profile picture path
        $stmt = $pdo->prepare("UPDATE employees SET profile_picture = ? WHERE employee_no = ?");
        $stmt->execute([$destination, $employee_no]);
        
        // Delete old profile picture if exists
        if ($old_profile && !empty($old_profile['profile_picture']) && file_exists($old_profile['profile_picture'])) {
            unlink($old_profile['profile_picture']);
        }
        
        // Update session
        $_SESSION['profile_picture'] = $destination;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Profile picture updated successfully',
            'path' => $destination
        ]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file']);
}
?>