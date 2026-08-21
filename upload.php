<?php
require_once 'config.php';
require_once 'auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'You must be logged in to upload images.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No image provided.']);
    exit();
}

$file = $_FILES['image'];

// Check for errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload failed. Error code: ' . $file['error']]);
    exit();
}

// Check size
if ($file['size'] > MAX_UPLOAD_SIZE) {
    echo json_encode(['success' => false, 'error' => 'File is too large. Maximum size is 5MB.']);
    exit();
}

// Validate file type
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.']);
    exit();
}

// Generate unique filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
if (empty($extension)) {
    $parts = explode('/', $mimeType);
    $extension = $parts[1] ?? 'jpg';
}
$safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
$filename = uniqid() . '_' . $safeName . '.' . $extension;
$destination = UPLOAD_DIR . $filename;

// Ensure upload directory exists
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

if (move_uploaded_file($file['tmp_name'], $destination)) {
    echo json_encode(['success' => true, 'url' => 'uploads/' . $filename]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save the uploaded file.']);
}
?>
