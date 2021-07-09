<?php

namespace Dostavista\TestUtils;

use Dostavista\Core\Couriers\CourierRow;
use Dostavista\Tests\TestCaseAbstract;

class CourierProvider {
    private TestCaseAbstract $test;

    public function __construct(TestCaseAbstract $test) {
        $this->test = $test;
    }

    /**
     * @param mixed[] $data
     */
    public function getApprovedCourier(string $phone = '9190000000', array $data = []): CourierRow {
        // ...
    }

    // В реальном проекте тут будут ещё разные вспомогательные методы для тестов...
}
