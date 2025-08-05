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
$validation = validateNewsletterSubscription($input);

if (!$validation['valid']) {
    sendJsonResponse(false, 'Validation failed', ['errors' => $validation['errors']], 400);
}

// Log successful subscription attempt
logSecurityEvent('newsletter_subscription_attempt', [
    'email' => $validation['data']['email'],
    'status' => $validation['data']['status']
]);

// Here you would typically save to your database
// For now, we'll just return success
// You can integrate with your existing Supabase setup here

sendJsonResponse(true, 'Successfully subscribed to newsletter', $validation['data']);
?> 