<?php
return [
    'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
    'port' => getenv('REDIS_PORT') ?: 6379,
    'password' => getenv('REDIS_PASSWORD') ?: null,
    'database' => getenv('REDIS_DB') ?: 0,
    'ttl' => getenv('REDIS_TTL') ?: 3600, // Default TTL: 1 hour
    'prefix' => getenv('REDIS_PREFIX') ?: 'mywisata:'
];
