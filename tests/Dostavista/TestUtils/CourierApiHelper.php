<?php declare(strict_types=1);

namespace Dostavista\TestUtils;

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Tests\TestCaseAbstract;

/**
 * Helper for calling Courier API methods.
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

    // In a real project there will be various auxiliary methods for tests...
}
