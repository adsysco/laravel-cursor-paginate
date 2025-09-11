<?php

namespace Bitsnbolts\CursorPaginate\Tests;

use Bitsnbolts\CursorPaginate\CursorPaginateServiceProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->setUpRoutes();
    }

    protected function getPackageProviders($app)
    {
        return [
            CursorPaginateServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUpDatabase()
    {
        $this->app->get('db')->connection()->getSchemaBuilder()->create('test_models', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
        });
    }

    protected function setUpRoutes()
    {
        Route::any('/', function () {
            $result = TestModel::cursorPaginateWithTotal(2, ['created_at' => 'desc', 'id' => 'desc']);

            return $result;
        });
    }
}
