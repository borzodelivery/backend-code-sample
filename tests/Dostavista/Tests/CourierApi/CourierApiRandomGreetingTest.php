<?php

namespace Dostavista\Tests\CourierApi;

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Features\CourierGreetings\CourierGreetingsTable;
use Dostavista\Tests\TestCaseAbstract;

/**
 * Тесты приветствий курьера.
 * @covers CourierApiController::randomGreetingAction()
 */
class CourierApiRandomGreetingTest extends TestCaseAbstract {
    /**
     * Проверяет случай, когда в базе нет подходящих приветствий для курьера.
     * @covers CourierApiController::randomGreetingAction()
     */
    public function testRandomGreetingEmpty(): void {
        $courier = $this->getCourierProvider()->getApprovedCourier();

        $json = $this->getCourierApiHelper()->getRandomGreeting($courier)->getJson();
        assertNull($json['greeting_text']);
    }

    /**
     * Проверяет успешное получение простого приветствия для курьера.
     * @covers CourierApiController::randomGreetingAction()
     */
    public function testRandomGreetingSimple(): void {
        // Добавляем приветствие, которое можно показывать в любое время
        $greeting = CourierGreetingsTable::makeUnsavedRow();

        $greeting->greeting_template = 'Привет, %name%!';
        $greeting->save();

        $courier = $this->getCourierProvider()->getApprovedCourier();

        $json = $this->getCourierApiHelper()->getRandomGreeting($courier)->getJson();
        assertSame('Привет, Курьер!', $json['greeting_text']);
    }
}
