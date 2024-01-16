<?php declare(strict_types=1);

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Framework\ApiSchema\ApiDoc;

return [
    'title'       => 'Greeting',
    'description' => 'Returns a randomly selected greeting for the courier',

    /** @see CourierApiController::randomGreetingAction() */
    'path'          => '/random-greeting',
    'http_method'   => ApiDoc::GET,
    'auth_required' => true,

    'parameters' => [],

    'response' => [
        'properties' => [
            'greeting_text' => [
                'description' => 'Greeting text',
                'type'        => ApiDoc::STRING,
                'nullable'    => true,
                'example'     => 'Hello, Igor!',
            ],
        ],
    ],
];
