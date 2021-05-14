<?php

namespace Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Puntodev\Payables\PaymentsServiceProvider;

abstract class TestCase extends Orchestra
{
    public function getEnvironmentSetUp($app)
    {
        // import the CreatePostsTable class from the migration
        include_once __DIR__ . '/../database/migrations/create_payments_table.php.stub';

        // run the up() method of that migration class
        (new \CreatePaymentsTable)->up();
    }

    protected function getPackageProviders($app)
    {
        return [
            PaymentsServiceProvider::class
        ];
    }
}