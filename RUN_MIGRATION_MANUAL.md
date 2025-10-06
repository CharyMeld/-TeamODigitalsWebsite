# Manual Migration Guide

Since `php artisan migrate` isn't working due to PHP version mismatch, here are alternative ways to create the `login_activities` table.

---

## Option 1: Using Docker Sail (Recommended)

If you're using Laravel Sail:

```bash
# Access the Sail MySQL container
./vendor/bin/sail mysql

# Then paste the SQL from create_login_activities_table.sql
# Or run the file directly:
```

Or run the file directly:
```bash
./vendor/bin/sail mysql < create_login_activities_table.sql
```

---

## Option 2: Using Docker Compose Directly

```bash
# Access MySQL container
docker-compose exec mysql mysql -u sail -ppassword teamo_digital_solutions

# Then paste the SQL from create_login_activities_table.sql
```

Or import the file:
```bash
docker-compose exec -T mysql mysql -u sail -ppassword teamo_digital_solutions < create_login_activities_table.sql
```

---

## Option 3: Using MySQL Command Line

```bash
mysql -h mysql -u sail -ppassword teamo_digital_solutions < create_login_activities_table.sql
```

---

## Option 4: Copy-Paste SQL (Easiest)

1. **Access your MySQL database** using any MySQL client (phpMyAdmin, DBeaver, etc.)

2. **Select database**: `teamo_digital_solutions`

3. **Run this SQL**:

```sql
CREATE TABLE IF NOT EXISTS `login_activities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NULL,
    `email` VARCHAR(255) NOT NULL,
    `status` ENUM('success', 'failed') NOT NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `device_type` VARCHAR(255) NULL,
    `browser` VARCHAR(255) NULL,
    `platform` VARCHAR(255) NULL,
    `location` VARCHAR(255) NULL,
    `failure_reason` VARCHAR(255) NULL,
    `attempted_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,

    INDEX `login_activities_user_id_index` (`user_id`),
    INDEX `login_activities_email_index` (`email`),
    INDEX `login_activities_status_index` (`status`),
    INDEX `login_activities_user_id_attempted_at_index` (`user_id`, `attempted_at`),
    INDEX `login_activities_email_status_attempted_at_index` (`email`, `status`, `attempted_at`),

    CONSTRAINT `login_activities_user_id_foreign`
        FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

4. **Verify**:
```sql
SHOW TABLES LIKE 'login_activities';
DESCRIBE login_activities;
```

---

## Option 5: Using phpMyAdmin

If you have phpMyAdmin:

1. Open phpMyAdmin in browser (usually http://localhost:8080/phpmyadmin)
2. Login with credentials:
   - **User**: sail
   - **Password**: password
3. Select database: `teamo_digital_solutions`
4. Click **SQL** tab
5. Paste the SQL from above
6. Click **Go**

---

## Option 6: Temporary Workaround (Disable Activity Logging)

If you can't create the table right now, temporarily disable login activity logging:

### Edit: `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

**Comment out lines 48, 66** (the two `logLoginActivity` calls):

```php
// Line 48 - Comment this out
// $this->logLoginActivity($request, null, 'failed', 'Invalid credentials');

// Line 66 - Comment this out
// $this->logLoginActivity($request, $request->user(), 'success');
```

This will let you login without errors, but won't track login activity until the table is created.

---

## Verification

After creating the table, test:

```bash
# Access MySQL
./vendor/bin/sail mysql

# Check table exists
USE teamo_digital_solutions;
SHOW TABLES LIKE 'login_activities';

# Check structure
DESCRIBE login_activities;

# Check it's empty
SELECT COUNT(*) FROM login_activities;
```

---

## After Table is Created

1. **Refresh your browser**
2. **Try logging in again**
3. **Check the table**:
```sql
SELECT * FROM login_activities ORDER BY attempted_at DESC LIMIT 10;
```

You should see your login activity!

---

## Record Migration in Laravel

After manually creating the table, add a record to the migrations table so Laravel knows it's been run:

```sql
INSERT INTO migrations (migration, batch)
VALUES ('2025_10_03_000001_create_login_activities_table',
        (SELECT COALESCE(MAX(batch), 0) + 1 FROM migrations m));
```

This prevents Laravel from trying to run it again when you eventually run `php artisan migrate`.

---

## Need Help?

**Error: "Access denied"**
→ Check your database credentials in `.env`

**Error: "Unknown database"**
→ Make sure database `teamo_digital_solutions` exists

**Error: "Foreign key constraint fails"**
→ Make sure `users` table exists with `id` column

**Still stuck?**
→ Use Option 6 (temporary workaround) to disable logging for now
