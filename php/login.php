<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$response = array('status' => 'error', 'message' => 'Unknown error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => 'All fields required.']);
        exit;
    }

    $db = new Database();
    $conn = $db->getMysqlConnection();

    // Fetch user by email
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify Password
        if (password_verify($password, $user['password'])) {
            // Generate Session Token
            $token = bin2hex(random_bytes(32));
            
            // Store Session in Redis
            // Requirement: "Use Redis to store the session information in the backend"
            try {
                $redis = $db->getRedisConnection();
                if ($redis) {
                    // Set token key with user_id value, expiry 24 hours (86400s)
                    $redis->setex("session:" . $token, 86400, $user['id']);
                    
                    $response['status'] = 'success';
                    $response['message'] = 'Login successful';
                    $response['token'] = $token;
                } else {
                    $response['message'] = 'Server Error: Could not connect to Session Store (Redis).';
                }
            } catch (Exception $e) {
                $response['message'] = 'Redis Error: ' . $e->getMessage();
            }

        } else {
            $response['message'] = 'Invalid password.';
        }
    } else {
        $response['message'] = 'User not found.';
    }

    $stmt->close();
    $conn->close();
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
