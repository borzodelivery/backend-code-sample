<?php

use Dostavista\Features\CourierApi\CourierApiSchema;
use Dostavista\Framework\ApiSchema\ApiDoc;

return [
    'title'       => 'Courier API',
    'description' => <<<'TEXT'
        <b>Courier API</b> — API для обмена данными между курьерскими мобильными приложениями и бэкендом Достависты.
        <br>При разработке API мы следуем API Design Guidelines, принятым в Достависте.

        Если у тебя есть вопросы, их можно обсудить в чате DV-MOBILE.
        TEXT,

    'version'  => CourierApiSchema::getVersion(),
    'versions' => CourierApiSchema::getVersionsData(),

    'path' => "/api/courier/" . CourierApiSchema::getVersion(),

    'default_request_parameters' => [
        'X-DV-Session' => [
            'description' => 'Сессия курьера. Передается в HTTP заголовке запроса.',
            'location'    => ApiDoc::HEADER,
            'type'        => ApiDoc::STRING,
            'required'    => false,
            'nullable'    => false,
            'example'     => '34c077f96be34ee5a393f871fecee434',
        ],
        'User-Agent' => [
            'description' => 'Информация о приложении, версии и операционной системе. Передается в HTTP заголовке запроса.',
            'location'    => ApiDoc::HEADER,
            'type'        => ApiDoc::STRING,
            'required'    => true,
            'nullable'    => false,
            'example'     => 'ru-courier-app-main-ios/9.3.0.123 (ApiDoc)',
        ],
    ],

    'default_response_properties' => [
        'is_successful' => [
            'description' => 'Успешность выполнения запроса.<br>При успешном ответе возвращается true.',
            'type'        => ApiDoc::BOOLEAN,
            'nullable'    => false,
            'example'     => true,
        ],
        'session' => [
            'description' => 'Сессия курьера',
            'type'        => ApiDoc::STRING,
            'nullable'    => false,
            'example'     => '34c077f96be34ee5a393f871fecee434',
        ],
        'server_datetime' => [
            'description' => 'Дата и время отправки ответа',
            'type'        => ApiDoc::DATE_TIME,
            'nullable'    => false,
            'example'     => date('c'),
        ],
    ],
];
