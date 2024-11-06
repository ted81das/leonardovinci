<?php

return [
    /*
     * The property id of which you want to display data.
     */
    'property_id' => env('GOOGLE_ANALYTICS_PROPERTY_ID'),

    /*
     * Path to the client secret json file.
     */
    'service_account_credentials_json' => storage_path('app/analytics/'. env('GOOGLE_SERVICE_ACCOUNT_CREDENTIALS')),
];
