# Mailjet Email Logging & Troubleshooting Guide

## Overview
Enhanced logging has been added to diagnose Mailjet email sending issues, particularly when emails work locally but fail on the server.

## What Was Added

### 1. Enhanced Logging in `CustomerAuthController::sendEmailOtp()`

The method now logs:

#### **Before Sending (INFO level)**
- Recipient email address
- Configured mailer (should be 'mailjet')
- From address and name
- Whether Mailjet credentials are configured

#### **On Success (INFO level)**
- Recipient email
- Timestamp of successful send

#### **On Failure (ERROR level)**
- Recipient email
- Error message
- Error code
- Error class name
- File and line where error occurred
- Full stack trace
- Complete mail configuration:
  - Mailer type
  - From address/name
  - Mailjet key/secret status (set or not)
- Environment (local/production)
- Timestamp

### 2. Test Command: `test:mailjet`

A new Artisan command to test Mailjet configuration:

```bash
php artisan test:mailjet your-email@example.com
```

**What it does:**
- Displays current mail configuration
- Checks if Mailjet credentials are set
- Sends a test email
- Logs all attempts and results
- Shows detailed error information if sending fails

## How to Use

### On Local Environment

1. **Test the configuration:**
   ```bash
   php artisan test:mailjet test@example.com
   ```

2. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Look for these log entries:**
   - `Attempting to send OTP email`
   - `OTP email sent successfully`
   - `Failed to send OTP email via Mailjet`

### On Server Environment

1. **SSH into your server**

2. **Navigate to project directory:**
   ```bash
   cd /path/to/xelnova
   ```

3. **Run test command:**
   ```bash
   php artisan test:mailjet your-email@example.com
   ```

4. **Check logs:**
   ```bash
   tail -100 storage/logs/laravel.log | grep -A 20 "Mailjet"
   ```

5. **Check for specific errors:**
   ```bash
   grep "Failed to send OTP email" storage/logs/laravel.log
   ```

## Common Issues & Solutions

### Issue 1: Mailjet Credentials Not Set
**Log Entry:**
```json
{
  "mailjet_configured": false
}
```

**Solution:**
- Check `.env` file has `MAILJET_APIKEY` and `MAILJET_APISECRET`
- Run `php artisan config:clear` after updating `.env`

### Issue 2: Wrong Mailer Selected
**Log Entry:**
```json
{
  "mailer": "smtp"  // Should be "mailjet"
}
```

**Solution:**
- Set `MAIL_MAILER=mailjet` in `.env`
- Run `php artisan config:clear`

### Issue 3: Invalid From Address
**Log Entry:**
```json
{
  "from_address": "hello@example.com"  // Generic default
}
```

**Solution:**
- Set `MAIL_FROM_ADDRESS` to a verified sender in Mailjet
- Mailjet requires sender verification

### Issue 4: API Authentication Failed
**Error Message:**
```
"error_message": "Client error: 401 Unauthorized"
```

**Solution:**
- Verify Mailjet API credentials are correct
- Check if API keys are active in Mailjet dashboard
- Ensure no extra spaces in `.env` values

### Issue 5: Rate Limiting
**Error Message:**
```
"error_message": "Too many requests"
```

**Solution:**
- Check Mailjet account limits
- Implement queue for email sending
- Add delays between sends

### Issue 6: Sender Not Verified
**Error Message:**
```
"error_message": "Sender email not verified"
```

**Solution:**
- Log into Mailjet dashboard
- Verify the sender email address
- Wait for verification email and confirm

## Log File Locations

### Local Environment
```
storage/logs/laravel.log
```

### Server Environment (typical)
```
/var/www/html/xelnova/storage/logs/laravel.log
```

## Monitoring Logs in Real-Time

### Local
```bash
tail -f storage/logs/laravel.log | grep -E "(Attempting to send|sent successfully|Failed to send)"
```

### Server
```bash
tail -f storage/logs/laravel.log | grep -E "(Attempting to send|sent successfully|Failed to send)"
```

## Example Log Entries

### Successful Send
```
[2026-01-15 07:35:00] local.INFO: Attempting to send OTP email
{
  "to": "user@example.com",
  "mailer": "mailjet",
  "from_address": "skumar.sujaa@gmail.com",
  "from_name": "Xelnova",
  "mailjet_configured": true
}

[2026-01-15 07:35:02] local.INFO: OTP email sent successfully
{
  "to": "user@example.com",
  "timestamp": "2026-01-15 07:35:02"
}
```

### Failed Send
```
[2026-01-15 07:35:00] local.INFO: Attempting to send OTP email
{
  "to": "user@example.com",
  "mailer": "mailjet",
  "from_address": "skumar.sujaa@gmail.com",
  "from_name": "Xelnova",
  "mailjet_configured": true
}

[2026-01-15 07:35:02] local.ERROR: Failed to send OTP email via Mailjet
{
  "to": "user@example.com",
  "error_message": "Connection timeout",
  "error_code": 0,
  "error_class": "Swift_TransportException",
  "error_file": "/vendor/swiftmailer/lib/classes/Swift/Transport/StreamBuffer.php",
  "error_line": 269,
  "mail_config": {
    "mailer": "mailjet",
    "from_address": "skumar.sujaa@gmail.com",
    "from_name": "Xelnova",
    "mailjet_key_set": true,
    "mailjet_secret_set": true
  },
  "environment": "production",
  "timestamp": "2026-01-15 07:35:02"
}
```

## Debugging Checklist

When email fails on server but works locally:

- [ ] Check `.env` file has correct Mailjet credentials
- [ ] Run `php artisan config:clear` on server
- [ ] Verify sender email in Mailjet dashboard
- [ ] Check server firewall allows outbound SMTP connections
- [ ] Verify Mailjet API keys are active
- [ ] Check server PHP version matches local
- [ ] Ensure `mailjet/mailjet-apiv3-php` package is installed
- [ ] Check server has internet connectivity
- [ ] Review server logs for network errors
- [ ] Test with `php artisan test:mailjet` command

## Additional Commands

### Clear all caches
```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Check Mailjet package installation
```bash
composer show mailjet/mailjet-apiv3-php
```

### View recent logs
```bash
tail -100 storage/logs/laravel.log
```

### Search for specific errors
```bash
grep -r "Failed to send" storage/logs/
```

## Support

If issues persist after checking logs:

1. Copy the full error log entry
2. Check Mailjet status page: https://status.mailjet.com/
3. Review Mailjet documentation: https://dev.mailjet.com/
4. Contact Mailjet support with log details
