<?php

return [
    'whatsapp' => [
        'token' => env('META_WHATSAPP_TOKEN'),
        'phone_number_id' => env('META_WHATSAPP_PHONE_NUMBER_ID'),
        'graph_version' => env('META_WHATSAPP_GRAPH_VERSION', 'v23.0'),
        'api_url' => env('META_WHATSAPP_API_URL', 'https://graph.facebook.com'),
        'template' => env('META_WHATSAPP_TEMPLATE_NAME'),
        'template_language' => env('META_WHATSAPP_TEMPLATE_LANGUAGE', 'id'),
    ],
];
