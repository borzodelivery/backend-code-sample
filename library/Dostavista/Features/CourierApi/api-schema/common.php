<?php declare(strict_types=1);

use Dostavista\Features\CourierApi\CourierApiSchema;
use Dostavista\Framework\ApiSchema\ApiDoc;

return [
    'title'       => 'Courier API',
    'description' => <<<'TEXT'
        <b>Courier API</b> — API for data exchange between courier mobile applications and the Borzo backend.
        <br>When developing APIs, we follow the API Design Guidelines adopted by Borzo.

        If you have questions, you can discuss them in the BORZO-DEV-MOBILE chat.
        TEXT,

    'version'  => CourierApiSchema::getVersion(),
    'versions' => CourierApiSchema::getVersionsData(),

    'path' => "/api/courier/" . CourierApiSchema::getVersion(),

    'default_request_parameters' => [
        'X-DV-Session' => [
            'description' => "Courier session. It's sent in the HTTP request header.",
            'location'    => ApiDoc::HEADER,
            'type'        => ApiDoc::STRING,
            'required'    => false,
            'nullable'    => false,
            'example'     => '34c077f96be34ee5a393f871fecee434',
        ],
        'User-Agent' => [
            'description' => "Application, version and operating system information. It's sent in the HTTP request header.",
            'location'    => ApiDoc::HEADER,
            'type'        => ApiDoc::STRING,
            'required'    => true,
            'nullable'    => false,
            'example'     => 'ru-courier-app-main-ios/9.3.0.123 (ApiDoc)',
        ],
    ],

    'default_response_properties' => [
        'is_successful' => [
            'description' => 'Whether a request has been successful.<br>If successful, the response returns true.',
            'type'        => ApiDoc::BOOLEAN,
            'nullable'    => false,
            'example'     => true,
        ],
        'session' => [
            'description' => 'Courier session',
            'type'        => ApiDoc::STRING,
            'nullable'    => false,
            'example'     => '34c077f96be34ee5a393f871fecee434',
        ],
        'server_datetime' => [
            'description' => 'Date and time the response was sent',
            'type'        => ApiDoc::DATE_TIME,
            'nullable'    => false,
            'example'     => date('c'),
        ],
    ],
];
