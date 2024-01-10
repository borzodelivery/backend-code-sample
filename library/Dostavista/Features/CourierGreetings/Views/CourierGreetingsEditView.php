<?php declare(strict_types=1);

namespace Dostavista\Features\CourierGreetings\Views;

use Dostavista\Features\CourierGreetings\CourierGreetingForm;
use Dostavista\Features\CourierGreetings\CourierGreetingRow;
use Dostavista\Features\Dispatcher\Views\DispatcherViewAbstract;

class CourierGreetingsEditView extends DispatcherViewAbstract {
    public CourierGreetingForm $form;

    public CourierGreetingRow $greeting;

    public function render(): void {
        include __DIR__ . '/edit.phtml';
    }
}
