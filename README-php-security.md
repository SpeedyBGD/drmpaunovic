# PHP Security Implementation

This guide explains the PHP-based security implementation for protection against SQL injection and XSS attacks.

## What's Been Implemented

1. **Security Helper Functions** (`security.php`): Comprehensive validation and sanitization functions
2. **PHP API Endpoints**: Secure endpoints for form processing
3. **Rate Limiting**: File-based rate limiting to prevent abuse
4. **Input Validation**: Email, password, and message validation
5. **XSS Protection**: HTML escaping for all user inputs
6. **Security Logging**: Logs all security events for monitoring

## Security Features

### Input Validation
- **Email Validation**: Format, length, and normalization
- **Password Validation**: Strong password requirements
- **Message Validation**: Length limits and XSS prevention
- **Subject Validation**: Length limits and HTML escaping

### Rate Limiting
- **Newsletter Subscriptions**: 5 attempts per 15 minutes
- **Contact Form**: 3 submissions per 15 minutes
- **Admin Login**: 5 attempts per 15 minutes

### Security Logging
- All security events are logged to `security.log`
- Includes IP address, user agent, and event details
- Helps monitor for suspicious activity

## File Structure

```
├── security.php              # Security helper functions
├── api/
│   ├── newsletter.php        # Newsletter subscription endpoint
│   ├── contact.php          # Contact form endpoint
│   └── admin-login.php      # Admin login endpoint
└── security.log             # Security event log (auto-created)
```

## How to Use

### 1. Upload Files
Upload all files to your web hosting:
- `security.php` to the root directory
- `api/` folder with all PHP endpoints

### 2. Test the Endpoints

#### Newsletter Subscription
```bash
curl -X POST https://yourdomain.com/api/newsletter.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","status":"active"}'
```

#### Contact Form
```bash
curl -X POST https://yourdomain.com/api/contact.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","message":"Hello"}'
```

#### Admin Login
```bash
curl -X POST https://yourdomain.com/api/admin-login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"Test123"}'
```

## Validation Rules

### Email Validation
- Must be valid email format
- Length between 5-254 characters
- Automatically normalized (lowercase)

### Password Validation
- Length between 6-128 characters
- Must contain uppercase letter
- Must contain lowercase letter
- Must contain number

### Message Validation
- Length between 1-5000 characters
- Automatically escaped to prevent XSS
- Trimmed of whitespace

## Security Features

### XSS Protection
- All user inputs are escaped using `htmlspecialchars()`
- Prevents script injection in messages and subjects

### Rate Limiting
- File-based rate limiting using temporary files
- Prevents brute force attacks
- Configurable limits per endpoint

### Input Sanitization
- Email addresses are normalized
- Messages are HTML-escaped
- All inputs are trimmed

### Error Handling
- Structured JSON responses
- No sensitive information leaked
- Proper HTTP status codes

## Integration with Existing Setup

The PHP endpoints can be integrated with your existing Supabase setup:

### Newsletter Integration
In `api/newsletter.php`, replace the success response with:
```php
// Connect to Supabase and insert the validated data
// Use the validated email from $validation['data']['email']
```

### Contact Form Integration
In `api/contact.php`, replace the success response with:
```php
// Send email using your existing email service
// Use the validated data from $validation['data']
```

### Admin Login Integration
In `api/admin-login.php`, replace the success response with:
```php
// Verify against your Supabase admin_users table
// Use bcrypt to verify the password
```

## Monitoring

### Security Log
Check `security.log` for security events:
```
[2024-01-15 10:30:45] newsletter_subscription_attempt - IP: 192.168.1.1 - UA: Mozilla/5.0... - Data: {"email":"test@example.com","status":"active"}
[2024-01-15 10:31:00] rate_limit_exceeded - IP: 192.168.1.1 - UA: Mozilla/5.0... - Data: {"action":"newsletter_subscription","ip":"192.168.1.1"}
```

### Rate Limit Files
Rate limit data is stored in temporary files:
- Location: `sys_get_temp_dir()`
- Format: `rate_limit_[hash].txt`
- Auto-cleanup after window expires

## Security Best Practices

1. **Keep security.log private**: Don't expose it publicly
2. **Monitor logs regularly**: Check for suspicious activity
3. **Update PHP**: Keep PHP version updated
4. **HTTPS only**: Use HTTPS in production
5. **Regular backups**: Backup security logs

## Testing Security

You can test the security features:

### SQL Injection Test
```bash
curl -X POST https://yourdomain.com/api/newsletter.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com\"; DROP TABLE users; --"}'
```
Should be rejected with validation error.

### XSS Test
```bash
curl -X POST https://yourdomain.com/api/contact.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","message":"<script>alert(\"xss\")</script>"}'
```
Should escape the script tags.

### Rate Limiting Test
Submit the same form multiple times rapidly to trigger rate limiting.

## Error Responses

All endpoints return structured JSON responses:

### Success Response
```json
{
  "success": true,
  "message": "Successfully subscribed to newsletter",
  "data": {
    "email": "test@example.com",
    "status": "active"
  }
}
```

### Error Response
```json
{
  "success": false,
  "message": "Validation failed",
  "data": {
    "errors": [
      "Please provide a valid email address"
    ]
  }
}
```

## Files Created

- ✅ `security.php` - Security helper functions
- ✅ `api/newsletter.php` - Newsletter subscription endpoint
- ✅ `api/contact.php` - Contact form endpoint
- ✅ `api/admin-login.php` - Admin login endpoint
- ✅ `README-php-security.md` - This documentation

## Next Steps

1. **Upload files** to your web hosting
2. **Test all endpoints** to ensure they work
3. **Integrate with Supabase** for database operations
4. **Monitor security logs** for suspicious activity
5. **Configure HTTPS** for production use 