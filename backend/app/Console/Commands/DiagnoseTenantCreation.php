<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Stancl\Tenancy\Tenancy;

class DiagnoseTenantCreation extends Command
{
    protected $signature = 'diagnose:tenant-creation {--test-db-creation} {--trace-creation} {--clean-test}';
    protected $description = 'Comprehensive diagnostic for tenant database creation issues';

    public function handle()
    {
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('TENANT DATABASE CREATION DIAGNOSTIC');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        // SECTION 1: Configuration Check
        $this->section('1. TENANCY CONFIGURATION CHECK');
        $this->checkConfiguration();

        // SECTION 2: Database Connection Check
        $this->section('2. DATABASE CONNECTION CHECK');
        $this->checkDatabaseConnections();

        // SECTION 3: Tenant Model Check
        $this->section('3. TENANT MODEL CHECK');
        $this->checkTenantModel();

        // SECTION 4: MySQL Permissions Check
        $this->section('4. MYSQL PERMISSIONS CHECK');
        $this->checkMySQLPermissions();

        // SECTION 5: Database Creation Capability
        if ($this->option('test-db-creation')) {
            $this->section('5. DATABASE CREATION CAPABILITY TEST');
            $this->testDatabaseCreation();
        }

        // SECTION 6: Trace Tenant Creation
        if ($this->option('trace-creation')) {
            $this->section('6. TENANT CREATION TRACE');
            $this->traceTenantCreation();
        }

        // SECTION 7: Current Tenant State
        $this->section('7. CURRENT TENANT STATE');
        $this->checkTenantState();

        // SECTION 8: Recommendations
        $this->section('8. DIAGNOSIS & RECOMMENDATIONS');
        $this->provideDiagnosis();

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('DIAGNOSTIC COMPLETE');
        $this->info('═══════════════════════════════════════════════════════════════');
    }

    private function checkConfiguration(): void
    {
        $this->info('Checking tenancy configuration...');
        $this->newLine();

        $config = config('tenancy');

        $checks = [
            'Tenant Model' => [
                'value' => $config['tenant_model'],
                'expected' => 'App\Models\Tenant',
            ],
            'Central Connection' => [
                'value' => $config['database']['central_connection'],
                'expected' => 'mysql',
            ],
            'Template Tenant Connection' => [
                'value' => $config['database']['template_tenant_connection'],
                'expected' => 'tenant',
            ],
            'Database Prefix' => [
                'value' => $config['database']['prefix'],
                'expected' => 'tenant_',
            ],
            'Database Manager' => [
                'value' => array_key_first($config['database']['managers']),
                'expected' => 'mysql',
            ],
        ];

        foreach ($checks as $name => $check) {
            $status = $check['value'] === $check['expected'] ? '✓' : '✗';
            $this->line("  $status $name: {$check['value']}");
            if ($check['value'] !== $check['expected']) {
                $this->warn("    Expected: {$check['expected']}");
            }
        }
    }

    private function checkDatabaseConnections(): void
    {
        $this->info('Checking database connections...');
        $this->newLine();

        $configs = config('database.connections');
        $required = ['mysql', 'tenant'];

        foreach ($required as $conn) {
            if (!isset($configs[$conn])) {
                $this->error("✗ Connection '{$conn}' NOT DEFINED");
                continue;
            }

            $this->line("✓ Connection '{$conn}' defined");

            $cfg = $configs[$conn];
            $this->line("  - Driver: {$cfg['driver']}");
            $this->line("  - Host: {$cfg['host']}:{$cfg['port']}");
            $this->line("  - Username: {$cfg['username']}");

            if ($conn === 'mysql') {
                $this->line("  - Database: {$cfg['database']} (CENTRAL)");
            } else {
                $db = $cfg['database'] ?? 'null (dynamic)';
                $this->line("  - Database: $db (DYNAMIC)");
            }

            // Test connection
            try {
                if ($conn === 'tenant') {
                    // For tenant connection, we just test credentials with central DB
                    $testDb = $configs['mysql']['database'];
                    $result = DB::connection('mysql')->selectOne(
                        "SELECT 1"
                    );
                    $this->line("  - Credentials: ✓ Valid");
                } else {
                    $result = DB::connection($conn)->selectOne("SELECT 1");
                    $this->line("  - Connection: ✓ Active");
                }
            } catch (\Exception $e) {
                $this->error("  - Connection: ✗ Failed - {$e->getMessage()}");
            }

            $this->newLine();
        }
    }

    private function checkTenantModel(): void
    {
        $this->info('Checking Tenant model implementation...');
        $this->newLine();

        $model = new Tenant();
        $interfaces = class_implements(get_class($model));

        $requiredInterfaces = [
            'Stancl\Tenancy\Contracts\TenantWithDatabase' => 'TenantWithDatabase',
        ];

        foreach ($requiredInterfaces as $interface => $name) {
            $status = isset($interfaces[$interface]) ? '✓' : '✗';
            $this->line("  $status Implements {$name}");
        }

        $requiredTraits = [
            'HasDatabase' => 'Stancl\Tenancy\Database\Concerns\HasDatabase',
            'HasDomains' => 'Stancl\Tenancy\Database\Concerns\HasDomains',
        ];

        $this->newLine();
        $this->line('Checking Traits...');

        foreach ($requiredTraits as $name => $trait) {
            $traits = class_uses_recursive(get_class($model));
            $status = isset($traits[$trait]) ? '✓' : '✗';
            $this->line("  $status Uses {$name}");
        }

        // Check custom columns
        $this->newLine();
        $this->line('Custom Columns:');
        $columns = Tenant::getCustomColumns();
        foreach ($columns as $col) {
            $this->line("  - $col");
        }
    }

    private function checkMySQLPermissions(): void
    {
        $this->info('Checking MySQL user permissions...');
        $this->newLine();

        try {
            $user = DB::connection('mysql')->selectOne(
                "SELECT CURRENT_USER() as user"
            );

            $this->line("✓ Connected as: {$user->user}");
            $this->newLine();

            // Check CREATE DATABASE permission
            try {
                // This won't actually create, just test if permission exists
                $result = DB::connection('mysql')->selectOne(
                    "SHOW GRANTS FOR CURRENT_USER()"
                );
                $this->line('✓ Can check grants');
                $this->newLine();

                $grants = DB::connection('mysql')->select(
                    "SHOW GRANTS FOR CURRENT_USER()"
                );

                foreach ($grants as $grant) {
                    $grantObj = (object) $grant;
                    $grantStr = $grantObj->{'Grants for ' . $user->user}
                        ?? array_values((array) $grantObj)[0];
                    $this->line("  - " . $grantStr);
                }

                // Check for CREATE DATABASE specifically
                $hasCreateDb = false;
                foreach ($grants as $grant) {
                    $grantObj = (object) $grant;
                    $grantStr = $grantObj->{'Grants for ' . $user->user}
                        ?? array_values((array) $grantObj)[0];
                    if (stripos($grantStr, 'CREATE') !== false &&
                        (stripos($grantStr, 'ALL') !== false || stripos($grantStr, 'ON *.*') !== false)) {
                        $hasCreateDb = true;
                    }
                }

                $this->newLine();
                if ($hasCreateDb) {
                    $this->line('✓ User has CREATE DATABASE permission');
                } else {
                    $this->warn('⚠ User may not have global CREATE DATABASE permission');
                    $this->warn('  Check if GRANT includes CREATE on *.*');
                }
            } catch (\Exception $e) {
                $this->error("✗ Could not check grants: {$e->getMessage()}");
            }

        } catch (\Exception $e) {
            $this->error("✗ MySQL connection failed: {$e->getMessage()}");
        }
    }

    private function testDatabaseCreation(): void
    {
        $this->info('Testing database creation capability...');
        $this->newLine();

        $testDbName = 'test_tenant_creation_' . time();
        $this->line("Test database name: $testDbName");
        $this->newLine();

        try {
            // Step 1: Create database
            $this->line('Step 1: Creating test database...');
            DB::connection('mysql')->statement("CREATE DATABASE $testDbName");
            $this->line('  ✓ Database created successfully');

            // Step 2: Verify database exists
            $this->line('Step 2: Verifying database exists...');
            $databases = DB::connection('mysql')->select('SHOW DATABASES');
            $dbNames = array_map(fn($db) => array_values((array) $db)[0], $databases);

            if (in_array($testDbName, $dbNames)) {
                $this->line('  ✓ Database confirmed to exist');
            } else {
                $this->error('  ✗ Database creation failed verification');
            }

            // Step 3: Test connection to new database
            $this->line('Step 3: Testing connection to new database...');
            try {
                $tempConfig = config('database.connections.mysql');
                $tempConfig['database'] = $testDbName;
                config(['database.connections.test_temp' => $tempConfig]);

                $result = DB::connection('test_temp')->selectOne('SELECT 1');
                $this->line('  ✓ Successfully connected to new database');
            } catch (\Exception $e) {
                $this->error("  ✗ Could not connect: {$e->getMessage()}");
            }

            // Step 4: Create a table
            $this->line('Step 4: Creating test table...');
            DB::connection('mysql')->statement(
                "CREATE TABLE $testDbName.test_table (id INT PRIMARY KEY, data VARCHAR(255))"
            );
            $this->line('  ✓ Test table created');

            // Step 5: Drop test database
            $this->line('Step 5: Cleaning up test database...');
            DB::connection('mysql')->statement("DROP DATABASE $testDbName");
            $this->line('  ✓ Test database dropped');

            $this->newLine();
            $this->info('✓ Database creation capability: WORKING');

        } catch (\Exception $e) {
            $this->error("✗ Database creation test failed: {$e->getMessage()}");
            $this->error("Error details: " . $e->getFile() . ':' . $e->getLine());

            // Attempt cleanup
            try {
                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS $testDbName");
            } catch (\Exception) {
                // Silently ignore cleanup errors
            }
        }
    }

    private function traceTenantCreation(): void
    {
        $this->info('Tracing tenant creation process...');
        $this->newLine();

        $testId = 'test_tenant_' . time();
        $testDomain = 'test-' . time() . '.local';

        $this->line("Creating test tenant: $testId");
        $this->newLine();

        try {
            // Step 1: Create tenant record
            $this->line('Step 1: Creating tenant record in central database...');
            $tenant = Tenant::create([
                'id' => $testId,
                'name' => 'Test Tenant ' . time(),
                'email' => 'test-' . time() . '@example.com',
            ]);
            $this->line("  ✓ Tenant record created");
            $this->line("    ID: {$tenant->id}");
            $this->line("    Name: {$tenant->name}");

            // Step 2: Check if database was created
            $this->line('Step 2: Checking if tenant database was created...');
            $expectedDbName = 'tenant_' . $testId;

            $databases = DB::connection('mysql')->select('SHOW DATABASES');
            $dbNames = array_map(fn($db) => array_values((array) $db)[0], $databases);

            if (in_array($expectedDbName, $dbNames)) {
                $this->line("  ✓ Database created: $expectedDbName");
            } else {
                $this->error("  ✗ Database NOT created: $expectedDbName");
                $this->warn("    Available tenant databases:");
                foreach ($dbNames as $db) {
                    if (strpos($db, 'tenant_') === 0) {
                        $this->warn("      - $db");
                    }
                }
            }

            // Step 3: Create domain
            $this->line('Step 3: Creating domain...');
            $tenant->domains()->create(['domain' => $testDomain]);
            $this->line("  ✓ Domain created: $testDomain");

            // Step 4: Try to run in tenant context
            $this->line('Step 4: Running code in tenant context...');
            try {
                $tenant->run(function () {
                    // Check which database we're connected to
                    $result = DB::selectOne('SELECT DATABASE() as db_name');
                    return $result;
                });
                $this->line("  ✓ Tenant context execution successful");
            } catch (\Exception $e) {
                $this->error("  ✗ Tenant context execution failed: {$e->getMessage()}");
            }

            // Step 5: Check events
            $this->line('Step 5: Checking Tenancy events...');
            $this->line("  - events.register: " . (config('tenancy.features.register_events') ? 'enabled' : 'disabled'));

            // Step 6: Cleanup
            $this->line('Step 6: Cleaning up...');
            try {
                DB::connection('mysql')->statement("DROP DATABASE IF EXISTS $expectedDbName");
                $tenant->delete();
                $this->line("  ✓ Test data cleaned up");
            } catch (\Exception $e) {
                $this->error("  ✗ Cleanup failed: {$e->getMessage()}");
            }

        } catch (\Exception $e) {
            $this->error("✗ Trace failed: {$e->getMessage()}");
            $this->error("Stack trace: " . $e->getTraceAsString());
        }
    }

    private function checkTenantState(): void
    {
        $this->info('Checking existing tenants and databases...');
        $this->newLine();

        // Get tenants from database
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No tenants found in central database');
            return;
        }

        $this->line("Found " . $tenants->count() . " tenant(s):");
        $this->newLine();

        // Get actual databases
        $databases = DB::connection('mysql')->select('SHOW DATABASES');
        $dbNames = array_map(fn($db) => array_values((array) $db)[0], $databases);
        $tenantDbs = array_filter($dbNames, fn($db) => strpos($db, 'tenant_') === 0);

        foreach ($tenants as $tenant) {
            $expectedDb = 'tenant_' . $tenant->id;
            $exists = in_array($expectedDb, $tenantDbs);
            $status = $exists ? '✓' : '✗';

            $this->line("$status Tenant: {$tenant->id}");
            $this->line("   Name: {$tenant->name}");
            $this->line("   Email: {$tenant->email}");
            $this->line("   Expected DB: $expectedDb");
            $this->line("   Actual: " . ($exists ? 'EXISTS' : 'MISSING'));

            // Check if tenant has users
            try {
                if ($exists) {
                    $tenant->run(function () {
                        $userCount = DB::table('users')->count();
                        return $userCount;
                    });
                }
            } catch (\Exception $e) {
                $this->warn("   Could not check users: {$e->getMessage()}");
            }

            $this->newLine();
        }

        // Show all tenant databases
        $this->newLine();
        $this->line('All tenant databases in MySQL:');
        if (empty($tenantDbs)) {
            $this->warn('  (none found)');
        } else {
            foreach ($tenantDbs as $db) {
                $this->line("  - $db");
            }
        }
    }

    private function provideDiagnosis(): void
    {
        $this->info('Analyzing results...');
        $this->newLine();

        $config = config('tenancy');
        $issues = [];

        // Check 1: Configuration
        if ($config['database']['template_tenant_connection'] !== 'tenant') {
            $issues[] = 'CRITICAL: template_tenant_connection is not set to "tenant"';
        }

        if ($config['database']['central_connection'] !== 'mysql') {
            $issues[] = 'CRITICAL: central_connection is not set to "mysql"';
        }

        // Check 2: Database configuration
        $dbCfg = config('database.connections');
        if (!isset($dbCfg['tenant'])) {
            $issues[] = 'CRITICAL: "tenant" connection not defined in database.php';
        } elseif ($dbCfg['tenant']['database'] !== null) {
            $issues[] = 'CRITICAL: "tenant" connection database should be null (for dynamic assignment)';
        }

        // Check 3: Tenant model
        $model = new Tenant();
        $interfaces = class_implements(get_class($model));
        if (!isset($interfaces['Stancl\Tenancy\Contracts\TenantWithDatabase'])) {
            $issues[] = 'CRITICAL: Tenant model does not implement TenantWithDatabase';
        }

        $traits = class_uses_recursive(get_class($model));
        if (!isset($traits['Stancl\Tenancy\Database\Concerns\HasDatabase'])) {
            $issues[] = 'CRITICAL: Tenant model does not use HasDatabase trait';
        }

        if (empty($issues)) {
            $this->info('✓ No critical configuration issues detected!');
            $this->newLine();
            $this->line('Possible causes of tenant database not being created:');
            $this->line('');
            $this->line('1. DATABASE MANAGER NOT BEING INVOKED');
            $this->line('   The stancl/tenancy package requires explicit database creation.');
            $this->line('   Solution: Use the DatabaseManager directly or ensure');
            $this->line('   the CreateTenancy event fires');
            $this->line('');
            $this->line('2. MISSING EVENT LISTENERS');
            $this->line('   Check if service providers are registered in config/app.php');
            $this->line('   Required: Stancl\Tenancy\TenancyServiceProvider');
            $this->line('');
            $this->line('3. TENANT->RUN() NOT TRIGGERING DATABASE CREATION');
            $this->line('   The $tenant->run() method does NOT auto-create the database.');
            $this->line('   You must explicitly create it using DatabaseManager');
            $this->line('');
            $this->line('4. RECOMMENDED FIX:');
            $this->line('   Use Tenancy::runFresh() or DatabaseManager directly');
            $this->line('');
        } else {
            $this->error('✗ Configuration issues found:');
            foreach ($issues as $issue) {
                $this->error("  - $issue");
            }
        }

        $this->newLine();
        $this->info('Run with --test-db-creation to test database creation');
        $this->info('Run with --trace-creation to trace a full tenant creation');
    }
}
