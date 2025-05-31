<?php
require_once('../system/config_bridge.php');
global $data,$mysqli,$bdata;
return [
    'callback' => $bdata['domain'] . '/login/social_login.php', // Must point to login.php

    'providers' => [
        'Google' => [
            'enabled' => true,
            'keys' => [
                'id' => $bdata['google_id'],
                'secret' => $bdata['google_secret']
            ],
        ],
        'Facebook' => [
            'enabled' => true,
            'keys' => [
                'id' => $bdata['facebook_id'],
                'secret' => $bdata['facebook_secret']
            ],
            'scope' => 'email,public_profile',
        ],
        'Twitter' => [
            'enabled' => true,
            'keys' => [
                'key' => $bdata['twitter_id'],
                'secret' => $bdata['twitter_secret']
            ],
        ],
    ],

    // Optional settings
    'debug_mode' => true,
    'debug_file' => __DIR__ . '/hybridauth.log',
];