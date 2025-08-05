<?php
/**
 * Security Helper Functions for drmpaunovic website
 * Provides protection against SQL injection and XSS attacks
 */

// Prevent direct access
if (!defined('SECURITY_INCLUDED')) {
    http_response_code(403);
    exit('Direct access not allowed');
}

/**
 * Sanitize and validate email address
 * @param string $email
 * @return array ['valid' => bool, 'email' => string, 'error' => string]
 */
function validateEmail($email) {
    $email = trim($email);
    
    // Check length
    if (strlen($email) < 5 || strlen($email) > 254) {
        return ['valid' => false, 'email' => '', 'error' => 'Email must be between 5 and 254 characters'];
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['valid' => false, 'email' => '', 'error' => 'Please provide a valid email address'];
    }
    
    // Normalize email (lowercase)
    $email = strtolower($email);
    
    return ['valid' => true, 'email' => $email, 'error' => ''];
}

/**
 * Sanitize and validate message content
 * @param string $message
 * @return array ['valid' => bool, 'message' => string, 'error' => string]
 */
function validateMessage($message) {
    $message = trim($message);
    
    // Check length
    if (strlen($message) < 1) {
        return ['valid' => false, 'message' => '', 'error' => 'Message cannot be empty'];
    }
    
    if (strlen($message) > 5000) {
        return ['valid' => false, 'message' => '', 'error' => 'Message must be less than 5000 characters'];
    }
    
    // Escape HTML to prevent XSS
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    
    return ['valid' => true, 'message' => $message, 'error' => ''];
}

/**
 * Validate password strength
 * @param string $password
 * @return array ['valid' => bool, 'error' => string]
 */
function validatePassword($password) {
    if (strlen($password) < 6) {
        return ['valid' => false, 'error' => 'Password must be at least 6 characters long'];
    }
    
    if (strlen($password) > 128) {
        return ['valid' => false, 'error' => 'Password must be less than 128 characters'];
    }
    
    // Check for strong password requirements
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/', $password)) {
        return ['valid' => false, 'error' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number'];
    }
    
    return ['valid' => true, 'error' => ''];
}

/**
 * Validate subject line
 * @param string $subject
 * @return array ['valid' => bool, 'subject' => string, 'error' => string]
 */
function validateSubject($subject) {
    $subject = trim($subject);
    
    if (strlen($subject) < 1) {
        return ['valid' => false, 'subject' => '', 'error' => 'Subject cannot be empty'];
    }
    
    if (strlen($subject) > 200) {
        return ['valid' => false, 'subject' => '', 'error' => 'Subject must be less than 200 characters'];
    }
    
    // Escape HTML to prevent XSS
    $subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
    
    return ['valid' => true, 'subject' => $subject, 'error' => ''];
}

/**
 * Rate limiting function
 * @param string $identifier (IP address or user ID)
 * @param string $action
 * @param int $maxAttempts
 * @param int $windowMinutes
 * @return bool
 */
function checkRateLimit($identifier, $action, $maxAttempts = 5, $windowMinutes = 15) {
    $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($identifier . $action) . '.txt';
    $now = time();
    $window = $windowMinutes * 60;
    
    // Read existing attempts
    $attempts = [];
    if (file_exists($cacheFile)) {
        $attempts = json_decode(file_get_contents($cacheFile), true) ?: [];
    }
    
    // Remove old attempts outside the window
    $attempts = array_filter($attempts, function($timestamp) use ($now, $window) {
        return ($now - $timestamp) < $window;
    });
    
    // Check if limit exceeded
    if (count($attempts) >= $maxAttempts) {
        return false;
    }
    
    // Add current attempt
    $attempts[] = $now;
    file_put_contents($cacheFile, json_encode($attempts));
    
    return true;
}

/**
 * Get client IP address
 * @return string
 */
function getClientIP() {
    $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
    
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

/**
 * Log security events
 * @param string $event
 * @param array $data
 */
function logSecurityEvent($event, $data = []) {
    $logFile = __DIR__ . '/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = getClientIP();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $logEntry = sprintf(
        "[%s] %s - IP: %s - UA: %s - Data: %s\n",
        $timestamp,
        $event,
        $ip,
        $userAgent,
        json_encode($data)
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
}

/**
 * Validate newsletter subscription
 * @param array $data
 * @return array ['valid' => bool, 'errors' => array, 'data' => array]
 */
function validateNewsletterSubscription($data) {
    $errors = [];
    $validData = [];
    
    // Validate email
    $emailResult = validateEmail($data['email'] ?? '');
    if (!$emailResult['valid']) {
        $errors[] = $emailResult['error'];
    } else {
        $validData['email'] = $emailResult['email'];
    }
    
    // Validate status (optional)
    if (isset($data['status'])) {
        $status = trim($data['status']);
        if (!in_array($status, ['active', 'inactive'])) {
            $errors[] = 'Status must be either active or inactive';
        } else {
            $validData['status'] = $status;
        }
    } else {
        $validData['status'] = 'active';
    }
    
    // Check rate limiting
    $ip = getClientIP();
    if (!checkRateLimit($ip, 'newsletter_subscription', 5, 15)) {
        $errors[] = 'Too many newsletter subscription attempts. Please try again later.';
        logSecurityEvent('rate_limit_exceeded', ['action' => 'newsletter_subscription', 'ip' => $ip]);
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validData
    ];
}

/**
 * Validate contact form
 * @param array $data
 * @return array ['valid' => bool, 'errors' => array, 'data' => array]
 */
function validateContactForm($data) {
    $errors = [];
    $validData = [];
    
    // Validate email
    $emailResult = validateEmail($data['email'] ?? '');
    if (!$emailResult['valid']) {
        $errors[] = $emailResult['error'];
    } else {
        $validData['email'] = $emailResult['email'];
    }
    
    // Validate message
    $messageResult = validateMessage($data['message'] ?? '');
    if (!$messageResult['valid']) {
        $errors[] = $messageResult['error'];
    } else {
        $validData['message'] = $messageResult['message'];
    }
    
    // Check rate limiting
    $ip = getClientIP();
    if (!checkRateLimit($ip, 'contact_form', 3, 15)) {
        $errors[] = 'Too many contact form submissions. Please try again later.';
        logSecurityEvent('rate_limit_exceeded', ['action' => 'contact_form', 'ip' => $ip]);
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validData
    ];
}

/**
 * Validate admin login
 * @param array $data
 * @return array ['valid' => bool, 'errors' => array, 'data' => array]
 */
function validateAdminLogin($data) {
    $errors = [];
    $validData = [];
    
    // Validate email
    $emailResult = validateEmail($data['email'] ?? '');
    if (!$emailResult['valid']) {
        $errors[] = $emailResult['error'];
    } else {
        $validData['email'] = $emailResult['email'];
    }
    
    // Validate password
    $passwordResult = validatePassword($data['password'] ?? '');
    if (!$passwordResult['valid']) {
        $errors[] = $passwordResult['error'];
    } else {
        $validData['password'] = $data['password']; // Don't sanitize password for bcrypt
    }
    
    // Check rate limiting
    $ip = getClientIP();
    if (!checkRateLimit($ip, 'admin_login', 5, 15)) {
        $errors[] = 'Too many login attempts. Please try again later.';
        logSecurityEvent('rate_limit_exceeded', ['action' => 'admin_login', 'ip' => $ip]);
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'data' => $validData
    ];
}

/**
 * Send JSON response
 * @param bool $success
 * @param string $message
 * @param array $data
 * @param int $statusCode
 */
function sendJsonResponse($success, $message = '', $data = [], $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

/**
 * Handle CORS preflight requests
 */
function handleCorsPreflight() {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
        http_response_code(200);
        exit;
    }
}

// Define security constant
define('SECURITY_INCLUDED', true);
?> 