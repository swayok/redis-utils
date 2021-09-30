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

$db = isset($argv[1]) ? (int)$argv[1] : 0;
$utils->line('Redis database #' . $db . ' analysis');
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
    $utils->analyzeDatabases($db)
);