<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Concerns\ActsAsRole;

abstract class TestCase extends BaseTestCase
{
    use ActsAsRole;
}
