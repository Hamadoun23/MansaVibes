<?php

return [

    /*
    |--------------------------------------------------------------------------
    | WhatsApp Cloud API (Meta)
    |--------------------------------------------------------------------------
    |
    | Permet d’envoyer de vrais documents PDF en pièce jointe. Les liens
    | web wa.me ne peuvent envoyer que du texte, pas de fichiers.
    |
    | https://developers.facebook.com/docs/whatsapp/cloud-api
    |
    */

    'cloud' => [
        'enabled' => filter_var(env('WHATSAPP_CLOUD_ENABLED', false), FILTER_VALIDATE_BOOLEAN),
        'access_token' => env('WHATSAPP_CLOUD_ACCESS_TOKEN'),
        'phone_number_id' => env('WHATSAPP_CLOUD_PHONE_NUMBER_ID'),
        'api_version' => env('WHATSAPP_CLOUD_API_VERSION', 'v21.0'),
    ],

];
