<?php

require_once __DIR__ . '/vendor/autoload.php';



$utils = new RedisUtils\RedisUtils();

$this->line('Redis databases info');
$this->printTable(
    [
        'db' => 'DB №',
        'keys' => 'Keys total',
        'expires' => 'Keys expires',
        'avg_ttl' => 'Avg. TTL',
    ],
    $utils->getDatabases()
);

$this->line('Redis keys analysis per database');
$this->printTable(
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