<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\DemoTestCase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Configuration des tests
|--------------------------------------------------------------------------
|
| Les tests Feature tournent sur PostgreSQL (base devmarche_test, voir phpunit.xml)
| et la base est remise à zéro à chaque test.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

// Tests sur le jeu de démo complet : semé une fois par processus, une transaction par test.
pest()->extend(DemoTestCase::class)
    ->in('Demo');
