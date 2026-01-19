<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test database creation
$dbName = 'tenant_testflow_inc';

echo "Testing database creation...\n";

try {
    $connection = config('database.connections.mysql');
    $collation = $connection['collation'] ?? 'utf8mb4_0900_ai_ci';

    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE '$collation'";
    DB::connection('mysql')->statement($sql);

    echo "✓ Database created: $dbName\n";

    // Verify
    $result = DB::connection('mysql')->select(
        "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = ?",
        [$dbName]
    );

    if (!empty($result)) {
        echo "✓ Database verified in MySQL\n";

        // Cleanup
        DB::connection('mysql')->statement("DROP DATABASE IF EXISTS `$dbName`");
        echo "✓ Cleanup complete\n";
        echo "\n✅ Database creation is working!\n";
    } else {
        echo "✗ Database verification failed\n";
    }

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
