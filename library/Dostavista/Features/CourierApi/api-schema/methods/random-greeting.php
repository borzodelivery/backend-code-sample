<?php

use Dostavista\Features\CourierApi\CourierApiController;
use Dostavista\Framework\ApiSchema\ApiDoc;

return [
    'title'       => 'Приветствие',
    'description' => 'Возвращает случайно выбранное приветствие для курьера',

    /** @see CourierApiController::randomGreetingAction() */
    'path'          => '/random-greeting',
    'http_method'   => ApiDoc::GET,
    'auth_required' => true,

    'parameters' => [],

    'response' => [
        'properties' => [
            'greeting_text' => [
                'description' => 'Текст приветствия',
                'type'        => ApiDoc::STRING,
                'nullable'    => true,
                'example'     => 'Привет, Игорь!',
            ],
        ],
    ],
];
