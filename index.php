<?php

require_once __DIR__ . '/vendor/autoload.php';



$utils = new RedisUtils\RedisUtils();

$utils->line('Redis databases info');
$utils->printTable(
    [
        'db' => 'DB №',
        'keys' => 'Keys total',
        'expires' => 'Keys expires',
        'avg_ttl' => 'Avg. TTL',
    ],
    $utils->getDatabases()
);

$utils->line('Redis keys analysis per database');
$utils->printTable(
    [
        'db' => 'DB №',
        'count' => 'Keys total',
        'active' => 'Keys active',
        'expired' => 'Keys expired',
        'neverExpire' => 'Keys w/o expiration',
        'size' => 'DB size',
        'avgTtl' => 'Avg. TTL',
    ],
    $utils->analyzeDatabases()
);