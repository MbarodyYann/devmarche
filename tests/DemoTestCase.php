<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\ParallelTesting;

/**
 * Tests sur le jeu de démonstration complet (tests/Demo).
 *
 * Le jeu de démo (40 devs, missions, questions, votes) prend plusieurs secondes à semer : il est semé
 * UNE seule fois par processus, dans une base dédiée (devmarche_demo_test), puis chaque test tourne
 * dans une transaction annulée à la fin. Les autres tests (tests/Feature) gardent leur base vide
 * et leurs factories.
 */
abstract class DemoTestCase extends TestCase
{
    use DatabaseTransactions;

    private static bool $seeded = false;

    public function createApplication()
    {
        $app = parent::createApplication();

        $token = ParallelTesting::token();
        $app['config']->set('database.connections.pgsql.database', 'devmarche_demo_test'.($token ? '_'.$token : ''));

        if (! self::$seeded) {
            $app[Kernel::class]->call('migrate:fresh', ['--seed' => true, '--force' => true]);
            self::$seeded = true;
        }

        return $app;
    }
}
