<?php

namespace Dostavista\Features\CourierApi;

use Dostavista\Features\CourierGreetings\CourierGreetingsTable;
use Dostavista\Features\ModernApi\ModernApiControllerAbstract;
use Dostavista\Features\ModernApi\TypeCaster;
use Dostavista\Framework\View\JsonView;

/**
 * Courier API для курьерских мобильных приложений.
 */
class CourierApiController extends ModernApiControllerAbstract {
    /**
     * Возвращает случайно выбранное приветствие для авторизованного курьера.
     */
    public function randomGreetingAction(): JsonView {
        $this->requireGet();
        $courier = $this->requireAuthCourier();

        $greetingText = CourierGreetingsTable::getRandomGreetingMessage($courier);

        return $this->makeResponse([
            'greeting_text' => TypeCaster::stringOrNull($greetingText),
        ]);
    }

    // В реальном проекте тут будут ещё разные другие методы...
}
