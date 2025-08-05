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
$validation = validateContactForm($input);

if (!$validation['valid']) {
    sendJsonResponse(false, 'Validation failed', ['errors' => $validation['errors']], 400);
}

// Log successful contact form submission
logSecurityEvent('contact_form_submission', [
    'email' => $validation['data']['email'],
    'message_length' => strlen($validation['data']['message'])
]);

// Here you would typically send the email
// For now, we'll just return success
// You can integrate with your existing email sending setup here

sendJsonResponse(true, 'Message sent successfully', $validation['data']);
?> 