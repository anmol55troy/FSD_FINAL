<?php
/**
 * Ajax endpoint to check username and email availability
 * Used for live validation during registration
 */

require_once '../config/db.php';

header('Content-Type: application/json');

$response = ['available' => false];

if (isset($_GET['username'])) {
    $username = trim($_GET['username']);
    
    if (!empty($username)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $response['available'] = !$stmt->fetch();
    }
} elseif (isset($_GET['email'])) {
    $email = trim($_GET['email']);
    
    if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $response['available'] = !$stmt->fetch();
    }
}

echo json_encode($response);
?>