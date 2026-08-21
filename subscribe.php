<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['email'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request.']);
    exit();
}

$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    exit();
}

$conn = getDBConnection();

// Check if already subscribed
$stmt = $conn->prepare("SELECT id FROM newsletter WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'You are already subscribed!']);
    $stmt->close();
    exit();
}
$stmt->close();

// Insert new subscriber
$stmt = $conn->prepare("INSERT INTO newsletter (email) VALUES (?)");
$stmt->bind_param("s", $email);
if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Successfully subscribed to the newsletter!']);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to subscribe. Please try again later.']);
}
$stmt->close();
?>
