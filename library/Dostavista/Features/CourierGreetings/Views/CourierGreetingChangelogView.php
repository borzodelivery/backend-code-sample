<?php

namespace Dostavista\Features\CourierGreetings\Views;

use Dostavista\Core\Changelogs\ChangelogRow;
use Dostavista\Features\CourierGreetings\CourierGreetingRow;
use Dostavista\Features\Dispatcher\Views\DispatcherViewAbstract;
use Dostavista\Framework\Pagination;

class CourierGreetingChangelogView extends DispatcherViewAbstract {
    public ?CourierGreetingRow $greeting;

    /** @var ChangelogRow[] */
    public array $changelog;

    public Pagination $pagination;

    public function render(): void {
        include __DIR__ . '/changelog.phtml';
    }
}
