<?php
// Include security functions
require_once '../security.php';

// Handle CORS
handleCorsPreflight();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendJsonResponse(false, 'Method not allowed', [], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    sendJsonResponse(false, 'Invalid JSON input', [], 400);
}

// Validate the input
$validation = validateAdminLogin($input);

if (!$validation['valid']) {
    sendJsonResponse(false, 'Validation failed', ['errors' => $validation['errors']], 400);
}

// Log login attempt
logSecurityEvent('admin_login_attempt', [
    'email' => $validation['data']['email']
]);

// Here you would typically verify against your database
// For now, we'll just return success
// You can integrate with your existing Supabase admin verification here

sendJsonResponse(true, 'Login successful', $validation['data']);
?> 