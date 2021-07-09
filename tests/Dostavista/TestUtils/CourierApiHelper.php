<?php

namespace Dostavista\TestUtils;

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Tests\TestCaseAbstract;

/**
 * Хелпер для вызова методов Courier API.
 * @see CourierApiController
 */
class CourierApiHelper {
    private TestCaseAbstract $test;

    public function __construct(TestCaseAbstract $test) {
        $this->test = $test;
    }

    /**
     * @see CourierApiController::randomGreetingAction()
     */
    public function getRandomGreeting(?CourierRow $courier = null): ModernApiClient {
        return $this->buildGetRequest('random-greeting', $courier);
    }

    // В реальном проекте тут будут ещё разные вспомогательные методы для тестов...
}
