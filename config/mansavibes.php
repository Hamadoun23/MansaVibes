<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Mode instance unique (un seul atelier)
    |--------------------------------------------------------------------------
    |
    | Si défini, ce tenant est toujours actif pour toute la requête (y compris
    | visiteurs non connectés), ce qui évite les erreurs sans contexte tenant.
    | Mettre null pour retrouver le mode multi-tenant par utilisateur.
    |
    */
    'single_tenant_id' => env('MANSAVIBES_SINGLE_TENANT_ID', 3),

];
