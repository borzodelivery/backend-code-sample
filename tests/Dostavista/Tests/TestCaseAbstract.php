<?php declare(strict_types=1);

namespace Dostavista\Tests;

use Dostavista\TestUtils\CourierApiHelper;
use Dostavista\TestUtils\CourierProvider;

/**
 * Base class for tests.
 */
abstract class TestCaseAbstract extends TestCase {
    public function getCourierApiHelper(): CourierApiHelper {
        return new CourierApiHelper($this);
    }

    public function getCourierProvider(): CourierProvider {
        return new CourierProvider($this);
    }

    // In a real project there will be various auxiliary methods for tests...
}
