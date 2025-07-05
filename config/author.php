<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Company/Author/Developer/Licence information of the application
    |--------------------------------------------------------------------------
    |
    | All details about the author/developer/contact/licence of the application
    |
    | IMPORTANT: CHANGING ANY OF THIS INFORMATION WILL UNSTABILIZE THE APPLICATION
    |
    */

    'vendor' => 'Brother IT',
    'vendor_url' => 'https://www.brotherit.net',
    'email' => 'admin@brotherit.net',
    'app_version' => "4.7.6",
    'lic1' => 'aHR0cHM6Ly9saWNlbnNlLmJyb3RoZXJpdC5uZXQvYXBpL2NoZWNrLw==',
    'pid' => 1,
    'brotherit_license_code' => env('BROTHERIT_LICENSE_CODE', 0),
    'boot_time' => env('POS_BOOT_TIME', 0),
    'boot_type' => env('POS_BOOT_TYPE', 0)
];
