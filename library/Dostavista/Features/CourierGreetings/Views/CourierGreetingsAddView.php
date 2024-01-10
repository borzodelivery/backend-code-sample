<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings\Views;

use Dostavista\Features\CourierGreetings\CourierGreetingForm;
use Dostavista\Features\Dispatcher\Views\DispatcherViewAbstract;

class CourierGreetingsAddView extends DispatcherViewAbstract {
    public CourierGreetingForm $form;

    public function render(): void {
        include __DIR__ . '/add.phtml';
    }
}
