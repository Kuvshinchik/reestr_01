<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestDbConnection extends Command
{
    protected $signature = 'db:test';
    protected $description = 'Test database connections';

    public function handle()
    {
        try {
            $db1 = DB::connection()->getDatabaseName();
            $this->info("✓ Connected to main DB: {$db1}");
        } catch (\Exception $e) {
            $this->error("✗ Main DB failed: " . $e->getMessage());
        }

        try {
            $db2 = DB::connection('mysql2')->getDatabaseName(); // замените на ваше имя соединения
            $this->info("✓ Connected to second DB: {$db2}");
        } catch (\Exception $e) {
            $this->error("✗ Second DB failed: " . $e->getMessage());
        }
    }
}