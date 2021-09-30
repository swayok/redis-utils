<?php

require_once __DIR__ . '/vendor/autoload.php';

$utils = new RedisUtils\RedisUtils();

$db = isset($argv[1]) ? (int)$argv[1] : 0;
$utils->line('Redis keys analysis for database #' . $db);
$utils->printTable(
    [
        'key' => 'Key',
        'type' => 'Type',
        'ttl' => 'TTL',
        'size' => 'Size',
        'count' => 'Items count',
    ],
    $utils->analyzeDatabase($db)
);