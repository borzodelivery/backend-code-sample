<?php

namespace Dostavista\Tests;

use Dostavista\TestUtils\CourierApiHelper;
use Dostavista\TestUtils\CourierProvider;

/**
 * Базовый класс для тестов.
 */
abstract class TestCaseAbstract extends TestCase {
    public function getCourierApiHelper(): CourierApiHelper {
        return new CourierApiHelper($this);
    }

    public function getCourierProvider(): CourierProvider {
        return new CourierProvider($this);
    }

    // В реальном проекте тут будут ещё разные вспомогательные методы для тестов...
}
