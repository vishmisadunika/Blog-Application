# Quick Setup Guide - Blog Application

## Local Development Setup (XAMPP / WAMP / MAMP / Native PHP)

### 1. Prerequisites
- PHP 7.4+ (with MySQLi extension)
- MySQL 5.7+ or MariaDB
- Web server (Apache/Nginx) or use PHP built-in server

### 2. Database Setup

**Option A: Using phpMyAdmin (Recommended for beginners)**

1. Open phpMyAdmin (usually http://localhost/phpmyadmin)
2. Click "New" to create a database named: `blog_app`
3. Select the new database
4. Go to "Import" tab
5. Choose file: `sql/schema.sql`
6. Click "Go"

**Option B: Using MySQL command line**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE blog_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE blog_app;
SOURCE /path/to/blog-application/sql/schema.sql;
EXIT;
```

**Option C: Using the command line directly**

```bash
mysql -u root -p blog_app < sql/schema.sql
```

### 3. Configure Database Connection

Edit `config.php` and update these lines:

```php
define('DB_HOST', 'localhost');      // Usually 'localhost'
define('DB_USER', 'root');           // Your MySQL username
define('DB_PASS', '');               // Your MySQL password
define('DB_NAME', 'blog_app');       // Database name you created
```

### 4. Start the Application

**Using PHP built-in server (quickest):**

```bash
cd blog-application
php -S localhost:8000
```

Then open: http://localhost:8000

**Using XAMPP/WAMP:**

1. Copy the `blog-application` folder to your web root:
   - XAMPP: `C:\xampp\htdocs\blog-application`
   - WAMP: `C:\wamp64\www\blog-application`
2. Start Apache and MySQL from the control panel
3. Open: http://localhost/blog-application

### 5. Test the Application

1. Visit the site
2. Click "Sign Up" and create an account
3. Login with your new account
4. Click "+ New Post" to write a blog
5. Try editing and deleting your own posts
6. Open another browser/incognito window, register as a different user
7. Verify that you CANNOT edit or delete posts from the first user

## Hosting Setup (InfinityFree - Free)

### Step-by-step:

1. **Sign up** at https://infinityfree.net/
2. **Verify your email** and log in to the control panel (vPanel)
3. **Create a website** (or use the default one)
4. **Create a MySQL database**:
   - Go to "MySQL Databases"
   - Create new database
   - Note down: Database name, Username, Password, Host
5. **Upload your files**:
   - Download FileZilla (free FTP client)
   - Get your FTP credentials from vPanel (FTP Accounts)
   - Connect and upload ALL files from `blog-application/` to the `htdocs/` folder
6. **Update config.php** (via FileZilla edit or re-upload):
   ```php
   define('DB_HOST', 'sqlXXX.infinityfree.com');  // From your database info
   define('DB_USER', 'epiz_XXXXXXX');             // Your database username
   define('DB_PASS', 'your_database_password');
   define('DB_NAME', 'epiz_XXXXXXX_blog_app');    // Your database name
   define('APP_URL', 'http://yourdomain.rf.gd');  // Your actual domain
   ```
7. **Import the database schema**:
   - Go to phpMyAdmin from vPanel
   - Select your database
   - Import → Choose `sql/schema.sql` → Go
8. **Test your site** at your public URL

### Other Free Hosting Options:
- 000WebHost (similar process)
- AwardSpace
- Hostinger free tier

## Common Issues & Solutions

| Problem | Solution |
|---------|----------|
| "Connection failed" | Check DB credentials in config.php, make sure MySQL is running |
| "Table doesn't exist" | Import sql/schema.sql into your database |
| Blank page | Enable PHP error display temporarily: add `ini_set('display_errors', 1);` at top of config.php |
| "Headers already sent" | Make sure no spaces or BOM before `<?php` in config.php |
| Cannot login after registration | Check that password hashing is working (PHP 7.4+) |
| Markdown not rendering | Check browser console for JS errors; preview uses client-side conversion |
| Styles not loading | Clear browser cache; check file permissions on CSS/JS |

## Security Checklist Before Hosting

- [ ] Change default DB credentials
- [ ] Never commit config.php with real passwords to GitHub
- [ ] Use HTTPS if your host provides it (free hosts usually do)
- [ ] Test that users cannot delete each other's posts
- [ ] Test login with special characters in username/password
- [ ] Verify session works across page navigation

## Next Steps After Setup

1. Create a few test posts
2. Test all CRUD operations
3. Record your 3-minute demo video
4. Push to GitHub
5. Host online
6. Create the submission PDF with links
7. Zip everything with your index number

## Need Help?

- Check the main README.md for feature documentation
- All PHP errors will show if you temporarily add to config.php:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
- Test with multiple users to verify authorization logic

Good luck with your submission!
}