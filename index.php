<?php

require_once __DIR__ . '/vendor/autoload.php';

$utils = new RedisUtils\RedisUtils();
$utils->analyze();