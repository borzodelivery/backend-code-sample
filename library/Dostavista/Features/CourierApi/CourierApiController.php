<?php declare(strict_types=1);

namespace Dostavista\Features\CourierApi;

use Dostavista\Features\CourierGreetings\CourierGreetingsTable;
use Dostavista\Features\ModernApi\ModernApiControllerAbstract;
use Dostavista\Features\ModernApi\TypeCaster;
use Dostavista\Framework\View\JsonView;

/**
 * Courier API for courier mobile applications.
 */
class CourierApiController extends ModernApiControllerAbstract {
    /**
     * Returns a randomly selected greeting for an authorized courier.
     */
    public function randomGreetingAction(): JsonView {
        $this->requireGet();
        $courier = $this->requireAuthCourier();

        $greetingText = CourierGreetingsTable::getRandomGreetingMessage($courier);

        return $this->makeResponse([
            'greeting_text' => TypeCaster::stringOrNull($greetingText),
        ]);
    }

    // In a real project there will be various other methods...
}
