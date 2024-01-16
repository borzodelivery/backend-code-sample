<?php declare(strict_types=1);

namespace Dostavista\Tests\CourierApi;

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Features\CourierGreetings\CourierGreetingsTable;
use Dostavista\Tests\TestCaseAbstract;

/**
 * Courier greeting tests.
 * @see CourierApiController::randomGreetingAction()
 */
class CourierApiRandomGreetingTest extends TestCaseAbstract {
    /**
     * Checks the case when there are no suitable greetings for the courier in the database.
     * @see CourierApiController::randomGreetingAction()
     */
    public function testRandomGreetingEmpty(): void {
        $courier = $this->getCourierProvider()->getApprovedCourier();

        $json = $this->getCourierApiHelper()->getRandomGreeting($courier)->getJson();
        assertNull($json['greeting_text']);
    }

    /**
     * Checks successful getting of a simple greeting for the courier.
     * @see CourierApiController::randomGreetingAction()
     */
    public function testRandomGreetingSimple(): void {
        // Add a greeting that can be shown at any time.
        $greeting = CourierGreetingsTable::makeUnsavedRow();

        $greeting->greeting_template = 'Hello, %name%!';
        $greeting->save();

        $courier = $this->getCourierProvider()->getApprovedCourier();

        $json = $this->getCourierApiHelper()->getRandomGreeting($courier)->getJson();
        assertSame('Hello, Courier!', $json['greeting_text']);
    }
}
