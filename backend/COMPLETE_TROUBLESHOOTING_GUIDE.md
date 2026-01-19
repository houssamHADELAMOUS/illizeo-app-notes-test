# TENANT DATABASE CREATION - COMPLETE TROUBLESHOOTING GUIDE

## Executive Summary

**The Problem:** Tenant databases are not being created, so users end up in the central database.

**The Root Cause:** The code calls `$tenant->run()` before explicitly creating the database. The stancl/tenancy package does NOT auto-create databases—you must create them explicitly.

**The Fix:** Add database creation between domain creation and `$tenant->run()`.

---

## 🔍 Diagnosis Steps

Follow these steps to identify EXACTLY where the problem occurs:

### Step 1: Verify Configuration

```bash
php artisan diagnose:tenant-creation
```

**What to look for:**
- ✓ All checks should pass
- ✗ If any fail, that's your first problem

**Expected Output:**
```
✓ Connection 'mysql' defined
✓ Connection 'tenant' defined
✓ Implements TenantWithDatabase
✓ Uses HasDatabase
✓ Uses HasDomains
```

### Step 2: Test Database Creation Capability

```bash
php artisan diagnose:tenant-creation --test-db-creation
```

**What this does:**
1. Creates a temporary test database
2. Verifies it was created
3. Cleans up

**Expected Output:**
```
✓ Database created successfully
✓ Database confirmed to exist
✓ Database creation capability: WORKING
```

**If it fails:** Your MySQL user lacks CREATE DATABASE permission
```sql
GRANT ALL PRIVILEGES ON *.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Step 3: Test Full Tenant Creation

```bash
php artisan diagnose:tenant-creation --trace-creation
```

**What this does:**
1. Creates a complete test tenant
2. Creates the database
3. Runs migrations
4. Verifies everything
5. Cleans up

**Expected Output:**
```
✓ Tenant record created
✓ Database created: tenant_test_tenant_XXXXX
✓ Domain created
✓ Tenant context execution successful
✓ Test data cleaned up
```

**If database shows as MISSING:** The creation code isn't being called

### Step 4: Check Current Tenant State

```bash
php artisan tinker

# List all tenants
Tenant::all()->map(fn($t) => $t->id);

# Check if databases exist
DB::select("SHOW DATABASES LIKE 'tenant_%'");

# For each tenant, check if database exists
$tenants = Tenant::all();
foreach ($tenants as $t) {
    $db = 'tenant_' . $t->id;
    $exists = DB::table('information_schema.schemata')
        ->where('schema_name', $db)
        ->exists();
    echo "{$t->id}: " . ($exists ? 'EXISTS' : 'MISSING') . "\n";
}
```

---

## 🐛 Common Issues & Solutions

### Issue 1: "No database selected" Error

**Symptoms:**
- API returns error: "No database selected"
- Or migrations fail silently
- Users not created

**Root Cause:**
Tenant database doesn't exist when `$tenant->run()` is called

**Fix:**
```php
// In TenantController.php, add BEFORE $tenant->run():

$tenantDbName = config('tenancy.database.prefix') . $tenant->id;
DB::connection('mysql')->statement(
    "CREATE DATABASE IF NOT EXISTS `$tenantDbName` 
     COLLATE 'utf8mb4_0900_ai_ci'"
);
```

**Verify:**
```bash
php artisan diagnose:tenant-creation --trace-creation
```

---

### Issue 2: Users Still in Central Database

**Symptoms:**
```bash
# In central DB
SELECT COUNT(*) FROM users;  # Returns > 0 (WRONG!)

# In tenant DB
SELECT COUNT(*) FROM tenant_X.users;  # Returns 0 (WRONG!)
```

**Root Cause:**
1. Database doesn't exist → `$tenant->run()` fails → falls back to central connection
2. Or User model is not switching to tenant connection

**Fix:**

**Option A: Create database (see Issue 1)**

**Option B: Verify User model uses correct connection**
```php
// In app/Models/User.php, make sure it doesn't force central connection:

class User extends Model
{
    // DO NOT add this if using tenancy:
    // protected $connection = 'mysql';
    
    // Instead, use default (which tenancy bootstrapper changes):
    protected $connection = null;  // or just omit this line
}
```

**Option C: Explicitly set connection in controller**
```php
$tenant->run(function () use ($request) {
    $user = new \App\Models\User();
    $user->setConnection('tenant');  // Explicit
    $user->fill([
        'name' => $request->admin_name,
        'email' => $request->admin_email,
        'password' => Hash::make($request->admin_password),
    ])->save();
});
```

---

### Issue 3: "Unknown database 'tenant_X'" Error

**Symptoms:**
```
SQLSTATE[HY000]: General error: 1049 Unknown database 'tenant_X'
```

**Root Cause:**
Database creation failed or wasn't called

**Diagnosis:**
```bash
# Check if database exists
mysql> SHOW DATABASES LIKE 'tenant_%';

# If empty, check if creation code is running
php artisan tinker
>>> DB::connection('mysql')->statement("CREATE DATABASE tenant_test");
>>> DB::select("SHOW DATABASES LIKE 'tenant_test'");  # Should show it

# If that works, problem is in your controller
```

**Fix:**
1. Verify `createTenantDatabase()` method exists in TenantController
2. Verify it's being called before `$tenant->run()`
3. Add error handling to catch SQL errors:

```php
try {
    $this->createTenantDatabase($tenantDbName);
} catch (\Exception $e) {
    return response()->json(['error' => 'Database creation failed: ' . $e->getMessage()], 500);
}
```

---

### Issue 4: "Template_tenant_connection" Not Set to 'tenant'

**Symptoms:**
```
Diagnostic shows: Template Tenant Connection: mysql (should be tenant)
```

**Root Cause:**
config/tenancy.php not properly configured

**Fix:**
```php
// In config/tenancy.php

'database' => [
    'central_connection' => 'mysql',
    'template_tenant_connection' => 'tenant',  // ← MUST be 'tenant'
    'prefix' => 'tenant_',
    // ...
],
```

---

### Issue 5: 'tenant' Connection Not Defined

**Symptoms:**
```
Diagnostic shows: Connection 'tenant' NOT DEFINED
```

**Root Cause:**
Missing connection in config/database.php

**Fix:**
```php
// In config/database.php, add this connection:

'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'illizeo_maindb'),  // Central DB
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        // ... rest of config
    ],

    'tenant' => [  // ← ADD THIS
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => null,  // ← CRITICAL: Must be null for dynamic assignment
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => env('DB_CHARSET', 'utf8mb4'),
        'collation' => env('DB_COLLATION', 'utf8mb4_0900_ai_ci'),
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => null,
    ],
],
```

---

## 🔧 Manual Testing in Tinker

Open `php artisan tinker` and run:

```bash
# Step 1: Verify configuration
config('tenancy.database.template_tenant_connection');  # Should be 'tenant'
config('database.connections.tenant.database');          # Should be null

# Step 2: Test database creation
DB::connection('mysql')->statement("CREATE DATABASE test_db_" . time());
DB::select("SHOW DATABASES LIKE 'test_db_%'");

# Step 3: Create test tenant manually
$tenant = Tenant::create([
    'id' => 'manual_test_' . time(),
    'name' => 'Manual Test',
    'email' => 'test@example.com',
]);

# Step 4: Create database explicitly
$dbName = config('tenancy.database.prefix') . $tenant->id;
DB::connection('mysql')->statement("CREATE DATABASE `$dbName`");

# Step 5: Verify database exists
DB::select("SHOW DATABASES LIKE '$dbName'");

# Step 6: Try to run in tenant context
$tenant->run(function () {
    return DB::select("SELECT DATABASE()");
});

# Step 7: Verify migrations work
$tenant->run(function () {
    Artisan::call('migrate', [
        '--database' => 'tenant',
        '--path' => 'database/migrations/tenant',
        '--force' => true,
    ]);
});

# Step 8: Create user in tenant
$tenant->run(function () {
    \App\Models\User::create([
        'name' => 'Test User',
        'email' => 'user@test.com',
        'password' => Hash::make('password123'),
    ]);
});

# Step 9: Verify user is in tenant DB, not central
DB::table('users')->where('email', 'user@test.com')->exists();  # Should be false
$tenant->run(fn() => DB::table('users')->where('email', 'user@test.com')->exists());  # Should be true

# Step 10: Cleanup
$tenant->delete();
DB::connection('mysql')->statement("DROP DATABASE `$dbName`");
```

---

## 📋 Verification Checklist

Before and after your fix, verify:

### Configuration
- [ ] `config/tenancy.php` has `template_tenant_connection` set to `'tenant'`
- [ ] `config/tenancy.php` has `central_connection` set to `'mysql'`
- [ ] `config/database.php` defines `'mysql'` connection
- [ ] `config/database.php` defines `'tenant'` connection
- [ ] `'tenant'` connection has `'database' => null`

### Model
- [ ] `Tenant` model implements `TenantWithDatabase`
- [ ] `Tenant` model uses `HasDatabase` trait
- [ ] `Tenant` model uses `HasDomains` trait
- [ ] `User` model doesn't force central connection

### Database Creation
- [ ] `TenantController::store()` creates database before `$tenant->run()`
- [ ] Database name is `tenant_` + tenant_id
- [ ] Creation uses correct collation (utf8mb4_0900_ai_ci)
- [ ] Error handling wraps database creation

### Testing
- [ ] Diagnostic command shows all ✓
- [ ] Test database creation works
- [ ] Trace test creates actual databases
- [ ] API endpoint returns correct response
- [ ] `SHOW DATABASES` shows `tenant_*` databases
- [ ] Users are in tenant DB, not central DB

---

## 🚀 Complete Fix Verification Test

```bash
# 1. Clear any test data
php artisan tinker
>>> Tenant::where('id', 'like', 'test_%')->delete();
>>> DB::select("DROP DATABASE IF EXISTS tenant_test_%");

# 2. Run full diagnostic
php artisan diagnose:tenant-creation

# 3. Test database creation
php artisan diagnose:tenant-creation --test-db-creation

# 4. Test full flow
php artisan diagnose:tenant-creation --trace-creation

# 5. Create via API
curl -X POST http://localhost:8000/api/tenants \
  -H "Content-Type: application/json" \
  -d '{
    "company_name": "Acme Corp",
    "company_email": "acme@example.com",
    "domain": "acme.local",
    "admin_name": "John Admin",
    "admin_email": "john@acme.com",
    "admin_password": "SecurePass123!",
    "admin_password_confirmation": "SecurePass123!"
  }'

# 6. Verify in MySQL
mysql> SHOW DATABASES LIKE 'tenant_%';
mysql> USE tenant_acme_corp;
mysql> SELECT * FROM users;
mysql> SELECT * FROM tenants;  # Should be empty
```

**Expected Results:**
- ✓ Diagnostic passes
- ✓ Database creation works
- ✓ Trace creates and cleans up successfully
- ✓ API returns 201 with database name
- ✓ `SHOW DATABASES` shows `tenant_acme_corp`
- ✓ User exists in tenant DB
- ✓ No user in central DB

---

## 📞 If You're Still Stuck

1. **Run diagnostics:**
   ```bash
   php artisan diagnose:tenant-creation
   ```
   Post output in issue

2. **Check error logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Enable query logging:**
   ```php
   // In tinker
   DB::enableQueryLog();
   // ... run your code ...
   DB::getQueryLog();
   ```

4. **Check MySQL logs:**
   ```bash
   tail -f /var/log/mysql/error.log
   ```

---

## Summary

| Step | Action | Command |
|------|--------|---------|
| 1 | Fix TenantController | Apply code changes |
| 2 | Check config | `php artisan diagnose:tenant-creation` |
| 3 | Test creation | `php artisan diagnose:tenant-creation --test-db-creation` |
| 4 | Test full flow | `php artisan diagnose:tenant-creation --trace-creation` |
| 5 | Test API | `curl -X POST http://localhost:8000/api/tenants ...` |
| 6 | Verify DB | `SHOW DATABASES LIKE 'tenant_%';` |

**Status:** All fixes applied and documented ✅
