Quick Setup Guide - Inkbloom
Local Development Setup (XAMPP / WAMP / MAMP / Native PHP)
1. Prerequisites
PHP 7.4+ (with MySQLi extension)
MySQL 5.7+ or MariaDB
Web server (Apache/Nginx) or use PHP built-in server
2. Database Setup
> **Important:** this app needs all three SQL files, in order. `schema.sql` creates the base `user` and `blogPost` tables. `migration.sql` adds everything the redesigned site uses — topics, cover images, likes, bookmarks, newsletter signups, and user avatars/bio. `migration_oauth.sql` adds the columns needed for "Continue with Google/GitHub" sign-in. Skipping any of them will break the related pages (topic filters, likes, bookmarks, search, social login, etc.).
Option A: Using phpMyAdmin (Recommended for beginners)
Open phpMyAdmin (usually http://localhost/phpmyadmin)
Click "New" to create a database named: `blog_app`
Select the new database
Go to "Import" tab → choose file: `sql/schema.sql` → Click "Go"
Go to "Import" again → choose file: `sql/migration.sql` → Click "Go"
Go to "Import" once more → choose file: `sql/migration_oauth.sql` → Click "Go"
Option B: Using MySQL command line
```bash
mysql -u root -p
```
```sql
CREATE DATABASE blog_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_app;
SOURCE /path/to/blog-application/sql/schema.sql;
SOURCE /path/to/blog-application/sql/migration.sql;
SOURCE /path/to/blog-application/sql/migration_oauth.sql;
EXIT;
```
Option C: Using the command line directly
```bash
mysql -u root -p blog_app < sql/schema.sql
mysql -u root -p blog_app < sql/migration.sql
mysql -u root -p blog_app < sql/migration_oauth.sql
```
3. Configure Database Connection
Edit `config.php` and update these lines:
```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'blog_app');       // Database name you created
```
4. Start the Application
Using PHP built-in server (quickest):
```bash
cd blog-application
php -S localhost:8000
```
Then open: http://localhost:8000
Using XAMPP/WAMP:
Copy the `blog-application` folder to your web root:
XAMPP: `C:\xampp\htdocs\blog-application`
WAMP: `C:\wamp64\www\blog-application`
Start Apache and MySQL from the control panel
Open: http://localhost/blog-application
5. Test the Application
Visit the site
Click "Sign Up" and create an account
Login with your new account
Click "+ New Post" to write a blog
Try editing and deleting your own posts
Open another browser/incognito window, register as a different user
Verify that you CANNOT edit or delete posts from the first user
Hosting Setup (InfinityFree - Free)
Step-by-step:
Sign up at https://infinityfree.net/
Verify your email and log in to the control panel (vPanel)
Create a website (or use the default one)
Create a MySQL database:
Go to "MySQL Databases"
Create new database
Note down: Database name, Username, Password, Host
Upload your files:
Download FileZilla (free FTP client)
Get your FTP credentials from vPanel (FTP Accounts)
Connect and upload ALL files from `blog-application/` to the `htdocs/` folder
Update config.php (via FileZilla edit or re-upload):
```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');  // From your database info
   define('DB_USER', 'epiz_XXXXXXX');             // Your database username
   define('DB_PASS', 'your_database_password');
   define('DB_NAME', 'epiz_XXXXXXX_blog_app');    // Your database name
   define('APP_URL', 'http://yourdomain.rf.gd');  // Your actual domain
   ```
Import the database schema:
Go to phpMyAdmin from vPanel
Select your database
Import → Choose `sql/schema.sql` → Go
Test your site at your public URL
Other Free Hosting Options:
000WebHost (similar process)
AwardSpace
Hostinger free tier
Enabling "Continue with Google / GitHub" (optional)
Social login works out of the box in the UI, but stays gracefully disabled
(with a friendly message on the login page) until you add real credentials:
Google — create OAuth credentials at
https://console.cloud.google.com/apis/credentials and set the authorized
redirect URI to `APP_URL/oauth.php?provider=google`.
GitHub — create an OAuth App at
https://github.com/settings/developers and set the callback URL to
`APP_URL/oauth.php?provider=github`.
Paste the client ID/secret pairs into `config.php`:
```php
   define('GOOGLE_CLIENT_ID', '...');
   define('GOOGLE_CLIENT_SECRET', '...');
   define('GITHUB_CLIENT_ID', '...');
   define('GITHUB_CLIENT_SECRET', '...');
   ```
Make sure `APP_URL` in `config.php` matches the URL you're actually
running the site on (including port), since it's used to build the
redirect URI.
Common Issues & Solutions
Problem	Solution
"Connection failed"	Check DB credentials in config.php, make sure MySQL is running
"Table doesn't exist"	Import sql/schema.sql into your database
Blank page	Enable PHP error display temporarily: add `ini_set('display_errors', 1);` at top of config.php
"Headers already sent"	Make sure no spaces or BOM before `<?php` in config.php
Cannot login after registration	Check that password hashing is working (PHP 7.4+)
Markdown not rendering	Check browser console for JS errors; preview uses client-side conversion
Styles not loading	Clear browser cache; check file permissions on CSS/JS
Security Checklist Before Hosting
[ ] Change default DB credentials
[ ] Never commit config.php with real passwords to GitHub
[ ] Use HTTPS if your host provides it (free hosts usually do)
[ ] Test that users cannot delete each other's posts
[ ] Test login with special characters in username/password
[ ] Verify session works across page navigation
Next Steps After Setup
Create a few test posts
Test all CRUD operations
Record your 3-minute demo video
Push to GitHub
Host online
Create the submission PDF with links
Zip everything with your index number
Need Help?
Check the main README.md for feature documentation
All PHP errors will show if you temporarily add to config.php:
```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
Test with multiple users to verify authorization logic
Good luck with your submission!