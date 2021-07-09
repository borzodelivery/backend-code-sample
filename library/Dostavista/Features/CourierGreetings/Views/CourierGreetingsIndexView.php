<?php

namespace Dostavista\Features\CourierGreetings\Views;

use Dostavista\Features\CourierGreetings\CourierGreetingRow;
use Dostavista\Features\Dispatcher\Views\DispatcherViewAbstract;

class CourierGreetingsIndexView extends DispatcherViewAbstract {
    /** @var CourierGreetingRow[] */
    public array $greetings;

    public function render(): void {
        include __DIR__ . '/index.phtml';
    }
}
