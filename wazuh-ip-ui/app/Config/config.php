<?php
// app/Config/config.php
return [
    'dashboard' => [
        'url'        => 'https://192.168.196.227:5601', // change-moi
        'user'       => 'admin',                        // change-moi
        'password'   => 'admin',                        // change-moi
        'verify_ssl' => false                           // true en prod
    ],
    'cache_ttl'          => 60, // secondes
    'dashboard_base_url' => 'https://192.168.196.227:5601' // pour ouvrir Discover
];