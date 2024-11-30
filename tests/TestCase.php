<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

//        echo "=====================================================================\n";
//        echo "Using database connection: " . config('database.default') . "\n";
//        echo "Connected to database: " . DB::connection()->getDatabaseName() . "\n";
//        echo "=====================================================================\n";
    }
}
