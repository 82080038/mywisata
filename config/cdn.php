<?php
return [
    'enabled' => getenv('CDN_ENABLED') ?: false,
    'url' => getenv('CDN_URL') ?: '',
    'assets' => [
        'css' => getenv('CDN_URL') ?: '' . '/public/css',
        'js' => getenv('CDN_URL') ?: '' . '/public/js',
        'images' => getenv('CDN_URL') ?: '' . '/public/images',
        'fonts' => getenv('CDN_URL') ?: '' . '/public/fonts'
    ],
    'cache' => [
        'enabled' => true,
        'ttl' => 31536000 // 1 year
    ]
];
