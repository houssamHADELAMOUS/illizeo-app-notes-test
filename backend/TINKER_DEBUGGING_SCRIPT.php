// ===================================================================
// TENANT CREATION TROUBLESHOOTING - ARTISAN TINKER SCRIPT
// Run in: php artisan tinker
// ===================================================================

// Copy and paste these commands one at a time in artisan tinker

// ===================================================================
// PART 1: CHECK CONFIGURATION
// ===================================================================

// Check tenancy config
$config = config('tenancy');
echo "=== TENANCY CONFIGURATION ===\n";
echo "Tenant Model: " . $config['tenant_model'] . "\n";
echo "Central Connection: " . $config['database']['central_connection'] . "\n";
echo "Template Tenant Connection: " . $config['database']['template_tenant_connection'] . "\n";
echo "Database Prefix: " . $config['database']['prefix'] . "\n";

// Check database connections
echo "\n=== DATABASE CONNECTIONS ===\n";
$mysql = config('database.connections.mysql');
$tenant = config('database.connections.tenant');

echo "MySQL (Central):\n";
echo "  Host: " . $mysql['host'] . "\n";
echo "  Database: " . $mysql['database'] . "\n";
echo "  User: " . $mysql['username'] . "\n";

echo "Tenant (Dynamic):\n";
echo "  Host: " . $tenant['host'] . "\n";
echo "  Database: " . ($tenant['database'] ?? 'null (dynamic)') . "\n";
echo "  User: " . $tenant['username'] . "\n";

// ===================================================================
// PART 2: TEST DATABASE CREATION
// ===================================================================

// Test if we can create a database
$testDb = 'test_db_' . time();
echo "\n=== TESTING DATABASE CREATION ===\n";
echo "Creating test database: $testDb\n";

try {
    DB::connection('mysql')->statement("CREATE DATABASE $testDb");
    echo "✓ Database created successfully\n";

    // Verify it exists
    $dbs = DB::connection('mysql')->select('SHOW DATABASES');
    $dbNames = array_map(fn($db) => array_values((array)$db)[0], $dbs);

    if (in_array($testDb, $dbNames)) {
        echo "✓ Database verified to exist\n";
    }

    // Clean up
    DB::connection('mysql')->statement("DROP DATABASE $testDb");
    echo "✓ Cleanup successful\n";
} catch (Exception $e) {
    echo "✗ Failed: " . $e->getMessage() . "\n";
}

// ===================================================================
// PART 3: CHECK CURRENT TENANTS
// ===================================================================

echo "\n=== EXISTING TENANTS ===\n";
$tenants = Tenant::all();

if ($tenants->isEmpty()) {
    echo "No tenants found\n";
} else {
    foreach ($tenants as $tenant) {
        echo "\nTenant: {$tenant->id}\n";
        echo "  Name: {$tenant->name}\n";
        echo "  Email: {$tenant->email}\n";

        $dbName = config('tenancy.database.prefix') . $tenant->id;
        echo "  Expected DB: $dbName\n";

        // Check if database exists
        $dbs = DB::connection('mysql')->select('SHOW DATABASES');
        $dbNames = array_map(fn($db) => array_values((array)$db)[0], $dbs);

        if (in_array($dbName, $dbNames)) {
            echo "  DB Status: ✓ EXISTS\n";

            // Try to count users in tenant DB
            try {
                $count = $tenant->run(function () {
                    return DB::table('users')->count();
                });
                echo "  Users in tenant DB: $count\n";
            } catch (Exception $e) {
                echo "  Could not count users: " . $e->getMessage() . "\n";
            }
        } else {
            echo "  DB Status: ✗ MISSING\n";
        }
    }
}

// ===================================================================
// PART 4: CHECK USERS LOCATION
// ===================================================================

echo "\n=== USER DATABASE LOCATION ===\n";

// Check central database
$centralUsers = DB::connection('mysql')->table('users')->count();
echo "Users in CENTRAL database: $centralUsers\n";

// Check each tenant database
foreach ($tenants as $tenant) {
    $dbName = config('tenancy.database.prefix') . $tenant->id;
    $dbs = DB::connection('mysql')->select('SHOW DATABASES');
    $dbNames = array_map(fn($db) => array_values((array)$db)[0], $dbs);

    if (in_array($dbName, $dbNames)) {
        try {
            $count = $tenant->run(function () {
                return DB::table('users')->count();
            });
            echo "Users in tenant_$tenant->id: $count\n";
        } catch (Exception $e) {
            echo "Could not check tenant_$tenant->id: " . $e->getMessage() . "\n";
        }
    }
}

// ===================================================================
// PART 5: CREATE TEST TENANT (USING FIXED APPROACH)
// ===================================================================

echo "\n=== CREATING TEST TENANT ===\n";

$testId = 'test_' . time();
$testDomain = 'test-' . time() . '.local';

echo "Creating tenant: $testId\n";

try {
    // Step 1: Create tenant record
    echo "Step 1: Creating tenant record...\n";
    $newTenant = Tenant::create([
        'id' => $testId,
        'name' => 'Test Tenant',
        'email' => 'test@example.com',
    ]);
    echo "  ✓ Record created\n";

    // Step 2: Create domain
    echo "Step 2: Creating domain...\n";
    $newTenant->domains()->create(['domain' => $testDomain]);
    echo "  ✓ Domain created\n";

    // Step 3: Create database (THE CRITICAL STEP!)
    echo "Step 3: Creating tenant database...\n";
    $dbName = config('tenancy.database.prefix') . $testId;
    DB::connection('mysql')->statement(
        "CREATE DATABASE IF NOT EXISTS `$dbName` COLLATE 'utf8mb4_0900_ai_ci'"
    );

    // Verify database exists
    $dbs = DB::connection('mysql')->select('SHOW DATABASES');
    $dbNames = array_map(fn($db) => array_values((array)$db)[0], $dbs);

    if (in_array($dbName, $dbNames)) {
        echo "  ✓ Database created and verified\n";
    } else {
        echo "  ✗ Database creation failed\n";
        throw new Exception("Database not found after creation");
    }

    // Step 4: Run migrations
    echo "Step 4: Running migrations...\n";
    $newTenant->run(function () {
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    });
    echo "  ✓ Migrations completed\n";

    // Step 5: Create test user
    echo "Step 5: Creating test user in tenant database...\n";
    $newTenant->run(function () {
        \App\Models\User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
        ]);
    });
    echo "  ✓ User created\n";

    // Verify user is in tenant DB, not central
    echo "\nStep 6: Verifying user location...\n";

    $inCentral = DB::connection('mysql')
        ->table('users')
        ->where('email', 'admin@test.com')
        ->exists();

    $inTenant = false;
    try {
        $inTenant = $newTenant->run(function () {
            return DB::table('users')
                ->where('email', 'admin@test.com')
                ->exists();
        });
    } catch (Exception $e) {
        echo "  Could not check tenant DB: " . $e->getMessage() . "\n";
    }

    echo "  User in CENTRAL database: " . ($inCentral ? "YES ✗" : "NO ✓") . "\n";
    echo "  User in TENANT database: " . ($inTenant ? "YES ✓" : "NO ✗") . "\n";

    echo "\n✓ TEST TENANT CREATION SUCCESSFUL\n";
    echo "  Tenant ID: $testId\n";
    echo "  Database: $dbName\n";
    echo "  Domain: $testDomain\n";

} catch (Exception $e) {
    echo "✗ FAILED: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";

    // Attempt cleanup
    try {
        if (isset($newTenant)) {
            $newTenant->delete();
        }
        $dbName = config('tenancy.database.prefix') . $testId;
        DB::connection('mysql')->statement("DROP DATABASE IF EXISTS $dbName");
        echo "\nCleanup completed\n";
    } catch (Exception $e2) {
        echo "\nCleanup failed: " . $e2->getMessage() . "\n";
    }
}

// ===================================================================
// PART 6: QUICK CLEANUP (DELETE TEST TENANTS)
// ===================================================================

// Uncomment to delete test tenants
/*
echo "\n=== CLEANUP ===\n";

$testTenants = Tenant::where('id', 'like', 'test_%')->get();
foreach ($testTenants as $t) {
    $dbName = config('tenancy.database.prefix') . $t->id;
    try {
        DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `$dbName`");
        $t->delete();
        echo "Deleted test tenant: {$t->id}\n";
    } catch (Exception $e) {
        echo "Failed to delete {$t->id}: " . $e->getMessage() . "\n";
    }
}
*/

echo "\n=== END OF SCRIPT ===\n";
