<?php

return array(
    'google' => array(
        'places' => array(
            'key' => env('GOOGLE_PLACES_API_KEY', null),
        )
    ),
    // Exemple for redis
    'predis' => array(
        'parameters' => array(
            'host' => '',
            'port' => '',
            'password' => ''
        ),
        'options' => array(
            'prefix'  => 'test:'
        ),
    ),
);