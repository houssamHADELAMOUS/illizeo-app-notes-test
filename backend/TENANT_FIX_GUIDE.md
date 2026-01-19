# TENANT DATABASE CREATION: Root Cause Analysis & Fix

## The Problem

Your tenant creation endpoint returns success, but:
- ❌ No separate tenant database is created
- ❌ Users are saved to the central database
- ❌ `$tenant->run()` fails with "No database selected"

## Root Cause

The **stancl/tenancy package does NOT automatically create tenant databases**. 

Looking at your `TenantController.php`:

```php
$tenant->run(function () use ($request) {
    \Artisan::call('migrate', [...]);
    \App\Models\User::create([...]);
});
```

This code has a critical flaw: **it attempts to run migrations BEFORE the database exists**.

### What Should Happen (What Doesn't)
1. Create tenant record in central DB ✓
2. **CREATE the tenant database** ← MISSING
3. Run migrations in tenant DB
4. Create admin user in tenant DB

### What Actually Happens
1. Create tenant record in central DB ✓
2. Try to run `$tenant->run()` ← Database doesn't exist yet
3. `Artisan::call('migrate')` fails silently or connects to central DB
4. Users created in central DB ✗

## The Fix

### Option 1: Proper Database Creation (RECOMMENDED)

Replace your `TenantController.php` with the logic in `TenantControllerFixed.php`:

**Key changes:**

```php
// STEP 1: Create tenant record
$tenant = Tenant::create([...]);

// STEP 2: Create domain
$tenant->domains()->create([...]);

// STEP 3: **EXPLICITLY CREATE DATABASE** (this is what was missing!)
$tenantDbName = config('tenancy.database.prefix') . $tenant->id;
$this->createTenantDatabase($tenantDbName);

// STEP 4: Now run migrations
$tenant->run(function () use ($request) {
    \Artisan::call('migrate', [
        '--database' => 'tenant',  // ← Add explicit database
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);

    \App\Models\User::create([...]);
});

// Helper method
private function createTenantDatabase(string $databaseName): void
{
    \DB::connection('mysql')->statement(
        "CREATE DATABASE IF NOT EXISTS `$databaseName` 
         COLLATE '" . config('database.connections.mysql.collation') . "'"
    );
}
```

### Option 2: Using DatabaseManager (Alternative)

```php
use Stancl\Tenancy\DatabaseManagers\DatabaseManager;

$databaseManager = app(DatabaseManager::class);

// Create the database
$databaseManager->create($tenant);

// Then run your migrations
$tenant->run(function () {
    \Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
});
```

## Step-by-Step Implementation

### 1. Update TenantController.php

Copy the fixed version from `TenantControllerFixed.php`:

```bash
cp app/Http/Controllers/Api/TenantControllerFixed.php app/Http/Controllers/Api/TenantController.php
```

Or manually apply these changes to your existing controller:

```php
// At the top, add this import
use Stancl\Tenancy\Tenancy;

// In the store() method, add after creating domain:
$tenantDbName = config('tenancy.database.prefix') . $tenant->id;
$this->createTenantDatabase($tenantDbName);

// At the bottom, add this helper method:
private function createTenantDatabase(string $databaseName): void
{
    $connection = config('database.connections.mysql');
    
    try {
        \DB::connection('mysql')->statement(
            "CREATE DATABASE IF NOT EXISTS `$databaseName` 
             COLLATE '" . ($connection['collation'] ?? 'utf8mb4_0900_ai_ci') . "'"
        );
    } catch (\Exception $e) {
        throw new \Exception("Failed to create tenant database: " . $e->getMessage());
    }
}
```

### 2. Verify Configuration

Run the diagnostic command:

```bash
php artisan diagnose:tenant-creation
```

This should show all green ✓ checkmarks.

### 3. Test Database Creation Capability

```bash
php artisan diagnose:tenant-creation --test-db-creation
```

This verifies Laravel can create databases on your MySQL server.

### 4. Test Full Tenant Creation Flow

```bash
php artisan diagnose:tenant-creation --trace-creation
```

This will:
- Create a test tenant
- Verify the database is created
- Check if migrations work
- Clean up

### 5. Manual Testing

Create a tenant via your API:

```bash
curl -X POST http://localhost:8000/api/tenants \
  -H "Content-Type: application/json" \
  -d '{
    "company_name": "Test Company",
    "company_email": "test@example.com",
    "domain": "test.local",
    "admin_name": "Admin User",
    "admin_email": "admin@test.com",
    "admin_password": "password123",
    "admin_password_confirmation": "password123"
  }'
```

Then verify in MySQL:

```sql
-- Check if tenant database was created
SHOW DATABASES LIKE 'tenant_%';

-- Check tenant record in central DB
SELECT * FROM tenants WHERE id = 'test_company';

-- Check if users are in tenant database (not central!)
USE tenant_test_company;
SELECT * FROM users;
```

## Debugging Commands

If something still doesn't work, use these diagnostic commands:

```bash
# Full diagnosis
php artisan diagnose:tenant-creation

# Test database creation
php artisan diagnose:tenant-creation --test-db-creation

# Trace a test tenant creation
php artisan diagnose:tenant-creation --trace-creation

# Check current tenant state
php artisan tinker
>>> Tenant::all()->map(fn($t) => ['id' => $t->id, 'db' => 'tenant_' . $t->id])

# Manually create database for existing tenant
>>> $tenant = Tenant::first();
>>> DB::statement("CREATE DATABASE IF NOT EXISTS `tenant_{$tenant->id}`")
>>> $tenant->run(fn() => Artisan::call('migrate', ['--database' => 'tenant']))
```

## Common Issues & Solutions

### Issue 1: "Unknown database 'tenant_X'"

**Cause:** Database doesn't exist
**Solution:** Ensure `createTenantDatabase()` is called before `$tenant->run()`

### Issue 2: "SQLSTATE[42S02]: Table doesn't exist"

**Cause:** Migrations haven't run in tenant database
**Solution:** Add `--database` flag to migration command:
```php
\Artisan::call('migrate', [
    '--database' => 'tenant',  // ← Add this
    '--path' => 'database/migrations/tenant',
    '--force' => true,
]);
```

### Issue 3: Users still in central DB

**Cause:** `$tenant->run()` not properly switching database
**Solution:** 
- Verify User model has correct connection
- Check if User::create() is using central connection instead of tenant

Add to User model:
```php
protected $connection = null; // Uses default from bootstrapper
```

### Issue 4: Permission denied creating database

**Cause:** MySQL user doesn't have CREATE DATABASE permission
**Solution:** Grant permissions:
```sql
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

## Verification Checklist

- [ ] TenantController creates database explicitly
- [ ] Diagnostic command shows all green
- [ ] Test database creation works
- [ ] Trace test creates actual database
- [ ] API endpoint creates tenant successfully
- [ ] `SHOW DATABASES` shows `tenant_` databases
- [ ] `SELECT * FROM tenant_X.users` works
- [ ] Central DB doesn't have tenant users

## Files Modified

- ✏️ `app/Http/Controllers/Api/TenantController.php` - Added database creation
- ✨ `app/Console/Commands/DiagnoseTenantCreation.php` - Diagnostic command

## Additional Resources

- [stancl/tenancy Documentation](https://tenancyforlaravel.com/docs/v3/)
- [DatabaseManager API](https://tenancyforlaravel.com/docs/v3/database-managers/)
- [Database Isolation Guide](https://tenancyforlaravel.com/docs/v3/database-isolation/)
