<?php
header('Content-Type: application/json');

// Include DB Connection
require_once 'db_connect.php';

$response = array('status' => 'error', 'message' => 'Unknown error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
        exit;
    }

    $db = new Database();
    $conn = $db->getMysqlConnection();

    // Check if email already exists using Prepared Statement
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $response['message'] = 'Email already registered.';
    } else {
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Insert new user using Prepared Statement
        $insertStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $insertStmt->bind_param("sss", $username, $email, $hashedPassword);

        if ($insertStmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Registration successful! Redirecting to login...';
        } else {
            $response['message'] = 'Registration failed: ' . $insertStmt->error;
        }
        $insertStmt->close();
    }
    $checkStmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
