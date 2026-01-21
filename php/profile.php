<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Composer autoloader for MongoDB library
require_once 'vendor/autoload.php'; 

$response = array('status' => 'error', 'message' => 'Unknown error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $action = $_POST['action'] ?? '';

    if (empty($token)) {
        echo json_encode(['status' => 'error', 'message' => 'No token provided']);
        exit;
    }

    $db = new Database();
    
    // Validate Session with Redis
    try {
        $redis = $db->getRedisConnection();
        if (!$redis) {
             throw new Exception("Redis connection failed");
        }
        $userId = $redis->get("session:" . $token); // Check if token exists
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Session Validation Error: ' . $e->getMessage()]);
        exit;
    }

    if (!$userId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired session']); // Triggers logout on frontend
        exit;
    }

    // Connect to MongoDB
    $mongoDb = $db->getMongoConnection();
    if (!$mongoDb) {
        echo json_encode(['status' => 'error', 'message' => 'MongoDB Connection Failed. Ensure mongo extension/library is installed.']);
        exit;
    }
    
    $collection = $mongoDb->user_profiles;

    if ($action === 'fetch') {
        // Fetch profile
        $profile = $collection->findOne(['user_id' => (int)$userId]);
        
        $response['status'] = 'success';
        $response['data'] = $profile ? [
            'age' => $profile['age'],
            'dob' => $profile['dob'],
            'contact' => $profile['contact'],
            'address' => $profile['address']
        ] : null;

    } elseif ($action === 'update') {
        // Update profile
        $age = $_POST['age'] ?? '';
        $dob = $_POST['dob'] ?? '';
        $contact = $_POST['contact'] ?? '';
        $address = $_POST['address'] ?? '';

        $updateResult = $collection->updateOne(
            ['user_id' => (int)$userId],
            ['$set' => [
                'user_id' => (int)$userId, // Ensure ID is set
                'age' => $age,
                'dob' => $dob,
                'contact' => $contact,
                'address' => $address,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]],
            ['upsert' => true] // Create if not exists
        );

        if ($updateResult->getMatchedCount() > 0 || $updateResult->getUpsertedCount() > 0) {
            $response['status'] = 'success';
            $response['message'] = 'Profile updated.';
        } else {
             // It might be that data didn't change
             $response['status'] = 'success';
             $response['message'] = 'No changes made.';
        }
    } else {
        $response['message'] = 'Invalid action.';
    }

} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>
