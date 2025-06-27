# Email Verification Setup

## Overview
This application now includes email verification functionality. When users register, they will receive an email with a verification link. Users must verify their email before they can log in.

## Email Configuration

To enable email sending, you need to configure your `.env` file with the following settings:

### For Gmail SMTP:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="SIPELEM FUTSAL"
```

### For Mailtrap (Testing):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=from@example.com
MAIL_FROM_NAME="SIPELEM FUTSAL"
```

### For Development (Log to File):
```env
MAIL_MAILER=log
MAIL_FROM_ADDRESS=hello@example.com
MAIL_FROM_NAME="SIPELEM FUTSAL"
```

## Features Implemented

1. **Email Verification on Registration**: Users receive a verification email when they register
2. **Login Protection**: Users cannot log in until their email is verified
3. **Custom Email Template**: Professional email template in Indonesian
4. **Resend Verification**: Users can request a new verification email
5. **User-Friendly Notifications**: Clear error messages and success notifications

## Routes Added

- `GET /email/verify` - Show verification notice page
- `GET /email/verify/{id}/{hash}` - Verify email link
- `POST /email/verification-notification` - Resend verification email

## Views Created

- `resources/views/auth/verify.blade.php` - Email verification notice page

## How It Works

1. User registers with email and password
2. System sends verification email with secure link
3. User clicks link in email to verify
4. User can now log in normally
5. If user tries to log in without verification, they see a clear error message

## Testing

For testing purposes, you can use:
- **Mailtrap.io** - Free email testing service
- **Log driver** - Emails are written to `storage/logs/laravel.log`

## Security Features

- Signed URLs with expiration (60 minutes)
- Rate limiting on resend requests (6 per minute)
- Secure hash verification
- CSRF protection on all forms 