-- Update existing admin password to MD5 format
-- Run this SQL if you already have the database with bcrypt password

-- Update admin password to MD5 hash of 'admin123'
UPDATE users 
SET password = '0192023a7bbd73250516f069df18b500' 
WHERE username = 'admin';

-- If you have other users, update them too:
-- UPDATE users SET password = MD5('your_password') WHERE username = 'username';

