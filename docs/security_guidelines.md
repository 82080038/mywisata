# SECURITY GUIDELINES
# Tour Guide Application

## OVERVIEW

This document provides security guidelines for developers and administrators working on the Tour Guide Application.

## PASSWORD POLICY

### Requirements
- **Minimum Length:** 8 characters
- **Uppercase Letters:** At least 1 (A-Z)
- **Lowercase Letters:** At least 1 (a-z)
- **Numbers:** At least 1 (0-9)
- **Special Characters:** At least 1 (!@#$%^&* etc.)

### Implementation
```php
$validator = new Validator($data);
$validator->passwordComplexity('password');

if ($validator->fails()) {
    // Handle validation error
}
```

## ACCOUNT LOCKOUT

### Policy
- **Maximum Attempts:** 5 failed login attempts
- **Lockout Duration:** 15 minutes
- **Lockout Reset:** After successful login or lockout duration expires

### Implementation
```php
// Check lockout before login
$lockoutStatus = Security::checkAccountLockout($email);

if ($lockoutStatus['locked']) {
    // Show lockout message
    echo $lockoutStatus['message'];
    exit;
}

// On failed login
Security::recordFailedAttempt($email);

// On successful login
Security::clearFailedAttempts($email);
```

## RATE LIMITING

### Policy
- **Per Minute:** 60 requests
- **Per Hour:** 1000 requests
- **Window:** Sliding window

### Implementation
```php
// Check rate limit
$rateLimit = Security::checkRateLimit($identifier, 60, 60);

if (!$rateLimit['allowed']) {
    http_response_code(429);
    echo json_encode(['error' => 'Rate limit exceeded']);
    exit;
}
```

## DATA ENCRYPTION

### Policy
- **Algorithm:** AES-256-CBC
- **Key Management:** Environment variable (ENCRYPTION_KEY)
- **Use Cases:** Sensitive data at rest

### Implementation
```php
// Encrypt data
$encrypted = Security::encrypt($sensitiveData);

// Decrypt data
$decrypted = Security::decrypt($encrypted);
```

## FILE UPLOAD SECURITY

### Policy
- **Max Size:** 5MB
- **Allowed Types:** JPEG, PNG, GIF, WebP, PDF
- **Validation:** MIME type, extension, size
- **Storage:** Random filename, outside web root

### Implementation
```php
$validation = Security::validateFileUpload($_FILES['file']);

if (!$validation['valid']) {
    // Handle validation error
    echo $validation['message'];
    exit;
}
```

## SESSION SECURITY

### Configuration
- **Timeout:** 30 minutes
- **Cookie Security:** HttpOnly, Secure (production), SameSite Strict
- **Regeneration:** Every 30 minutes
- **Fixation Prevention:** Regenerate on login

### Implementation
```php
// Start secure session
Session::start();

// Check session timeout
if (!Session::has('user_id')) {
    // Redirect to login
}
```

## CSRF PROTECTION

### Policy
- **Token Generation:** Per session
- **Token Validation:** On all POST requests
- **Token Expiration:** Session-based

### Implementation
```php
// Generate token
$token = CSRF_TOKEN;

// Validate token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    // Invalid CSRF token
}
```

## XSS PREVENTION

### Policy
- **Output Encoding:** htmlspecialchars with ENT_QUOTES
- **Content Security Policy:** Configured in .htaccess
- **Input Sanitization:** Security::sanitizeXSS()

### Implementation
```php
// Sanitize input
$sanitized = Security::sanitizeXSS($userInput);

// In views, use htmlspecialchars
<?= htmlspecialchars($data, ENT_QUOTES, 'UTF-8') ?>
```

## SQL INJECTION PREVENTION

### Policy
- **Query Method:** PDO prepared statements
- **Parameter Binding:** Always use bindParam/bindValue
- **Dynamic SQL:** Avoid user input in query structure

### Implementation
```php
// Use prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->bindParam(':email', $email);
$stmt->execute();
```

## SECURITY HEADERS

### Implemented Headers
- **X-XSS-Protection:** 1; mode=block
- **X-Content-Type-Options:** nosniff
- **X-Frame-Options:** SAMEORIGIN
- **Referrer-Policy:** strict-origin-when-cross-origin
- **Content-Security-Policy:** Configured for self and trusted CDNs
- **Strict-Transport-Security:** max-age=31536000 (HTTPS only)
- **Permissions-Policy:** Restricts geolocation, microphone, camera

## LOGGING

### Security Events to Log
- Failed login attempts
- Account lockouts
- Suspicious activity
- Rate limit violations
- Permission changes
- Data access attempts

### Log Files
- **error.log:** Application errors
- **audit.log:** Security events
- **access.log:** Access logs (if configured)

## MONITORING

### Automated Monitoring
- **Log Rotation:** Daily (scripts/maintenance/log_rotation.php)
- **Security Monitoring:** Hourly (scripts/maintenance/security_monitor.php)
- **Alerts:** Email on threshold exceeded

### Thresholds
- Failed logins: 20/hour
- Suspicious activity: 50/hour
- Rate limit violations: 10/hour

## INCIDENT RESPONSE

### Steps
1. Identify the incident
2. Contain the threat
3. Eradicate the cause
4. Recover systems
5. Document lessons learned

### Contacts
- Security Team: [Contact Information]
- System Administrator: [Contact Information]
- Management: [Contact Information]

## BEST PRACTICES

### Development
1. Never hardcode credentials
2. Use environment variables for sensitive data
3. Validate all user inputs
4. Sanitize all outputs
5. Use prepared statements for database queries
6. Implement proper error handling
7. Log security events
8. Regular security audits

### Deployment
1. Change default passwords
2. Update ENCRYPTION_KEY
3. Configure SSL/TLS certificates
4. Enable HSTS
5. Set up log rotation
6. Configure monitoring
7. Test security measures
8. Document security setup

### Maintenance
1. Regular dependency updates
2. Security patch application
3. Log file review
4. Security audit reviews
5. User access reviews
6. Password policy enforcement
7. Backup verification
8. Incident response drills

## COMPLIANCE

### Standards
- OWASP Top 10
- PCI DSS (if payment processing)
- GDPR (if EU users)
- Local data protection laws

### Documentation
- Security policy
- Incident response plan
- Data classification
- Access control matrix
- Risk assessment

---

**Version:** 1.0  
**Last Updated:** 2026-07-18  
**Status:** Active
