<?php
$app = require __DIR__ . '/bootstrap/app.php';
$db = $app->make('db');

// Drop the expenses table if it exists
$db->statement('DROP TABLE IF EXISTS expenses');
echo "Expenses table dropped.\n";

// Delete migration records
$deleted = $db->table('migrations')
    ->whereIn('migration', [
        '2024_11_12_062654_create_expenses_table',
        '2026_05_26_000000_create_expenses_table'
    ])
    ->delete();
echo "Deleted $deleted migration records.\n";

echo "Cleanup complete. Run 'php artisan migrate' next.\n";
