# Password Migration to MD5

The application has been updated to use MD5 password hashing instead of bcrypt.

## For New Installations

If you're installing fresh, the `database_schema.sql` file already contains the MD5 hash for the default admin password. No additional steps needed.

## For Existing Installations

If you already have a database with bcrypt passwords, you need to update them to MD5 format.

### Option 1: Update via SQL (Recommended)

Run the provided SQL script:

```sql
-- Update admin password to MD5 hash of 'admin123'
UPDATE users 
SET password = '0192023a7bbd73250516f069df18b500' 
WHERE username = 'admin';
```

Or use the provided file:
```bash
mysql -u root -p product_proposal_db < update_password_to_md5.sql
```

### Option 2: Update via phpMyAdmin

1. Open phpMyAdmin
2. Select `product_proposal_db` database
3. Go to `users` table
4. Click "Browse" or "SQL" tab
5. Run:
   ```sql
   UPDATE users 
   SET password = '0192023a7bbd73250516f069df18b500' 
   WHERE username = 'admin';
   ```

### Option 3: Update Other Users

If you have other users, you can update their passwords using:

```sql
-- Update password for a specific user
UPDATE users 
SET password = MD5('new_password') 
WHERE username = 'username';
```

Or in PHP:
```php
$newPasswordHash = md5('new_password');
// Then update in database
```

## Verify the Update

After updating, you should be able to login with:
- **Username:** `admin`
- **Password:** `admin123`

## Code Changes

The following files have been updated:

1. **`database_schema.sql`** - Default admin password now uses MD5
2. **`controllers/AuthController.php`** - Uses MD5 verification instead of `password_verify()`
3. **`includes/helpers.php`** - Added `hashPassword()` and `verifyPassword()` helper functions

## Helper Functions

Two new helper functions are available:

```php
// Hash a password
$hash = hashPassword('mypassword');
// Returns: MD5 hash string

// Verify a password
if (verifyPassword('mypassword', $storedHash)) {
    // Password is correct
}
```

## Notes

- MD5 is a one-way hash function
- The MD5 hash of "admin123" is: `0192023a7bbd73250516f069df18b500`
- All passwords are now stored as 32-character MD5 hexadecimal strings
- If you need to create new users, use: `hashPassword('password')` or `md5('password')`

