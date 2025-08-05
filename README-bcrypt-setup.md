# Bcrypt Password Hashing Setup

This guide explains how to set up secure password hashing for admin authentication using bcrypt.

## What's Been Implemented

1. **Bcrypt Library Added**: The bcrypt library has been downloaded locally (`bcrypt.min.js`) and added to both `index.html` and `admin.html`
2. **Authentication Updated**: Admin login now uses bcrypt to verify passwords instead of plain text comparison
3. **Password Hasher Utility**: Created `password-hasher.html` to generate secure password hashes

## How to Use

### Step 1: Generate Hashed Password

1. Open `password-hasher.html` in your browser
2. Enter the admin password you want to use
3. Click "Generate Hash"
4. Copy the generated hash

### Step 2: Update Database

1. Go to your Supabase dashboard
2. Navigate to the `admin_users` table
3. Update the `password_hash` field for your admin user with the generated hash
4. Make sure the `email` field matches your admin email

### Step 3: Test Authentication

1. Try logging in with the admin credentials
2. The system will now use bcrypt to verify the password
3. If successful, you'll be redirected to the admin panel

## Security Features

- **Salt Rounds**: Using 10 salt rounds for optimal security/performance balance
- **Async Verification**: Password verification is done asynchronously
- **No Plain Text**: Passwords are never stored or compared in plain text

## Database Schema

Your `admin_users` table should have this structure:

```sql
CREATE TABLE admin_users (
  id SERIAL PRIMARY KEY,
  email VARCHAR(255) UNIQUE NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT NOW()
);
```

## Example Admin User

```sql
INSERT INTO admin_users (email, password_hash) 
VALUES ('admin@drmpaunovic.rs', '$2a$10$your_hashed_password_here');
```

## Important Notes

1. **Delete the hasher utility**: After setting up your passwords, delete `password-hasher.html` for security
2. **Backup your hashes**: Keep a secure backup of your password hashes
3. **Strong passwords**: Use strong, unique passwords for admin accounts
4. **HTTPS only**: Always use HTTPS in production to protect password transmission

## Troubleshooting

### Password Not Working
- Make sure the hash in the database matches exactly what was generated
- Check that the email address matches exactly
- Verify the bcrypt library is loading properly (check browser console)

### Bcrypt Not Loading
- Check that `bcrypt.min.js` file exists in the project directory
- Verify the file path is correct in the HTML files
- Check browser console for any JavaScript errors

## Migration from Plain Text

If you're migrating from plain text passwords:

1. Generate hashes for all existing admin passwords
2. Update the database with the new hashes
3. Test login functionality
4. Remove any plain text password references from your code

## Security Best Practices

1. **Never store plain text passwords**
2. **Use strong, unique passwords**
3. **Regularly rotate admin passwords**
4. **Monitor login attempts**
5. **Use HTTPS in production**
6. **Consider implementing rate limiting**
7. **Log failed login attempts**

## Files Modified

- `index.html`: Added bcrypt library and updated authentication logic
- `admin.html`: Added bcrypt library and updated authentication logic
- `password-hasher.html`: New utility for generating password hashes
- `README-bcrypt-setup.md`: This documentation file 