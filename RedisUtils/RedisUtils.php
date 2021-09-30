<?php

namespace RedisUtils;

use Predis\Client;
use Predis\Collection\Iterator\Keyspace;

class RedisUtils {
    
    protected \Closure $lineHandler;
    protected Client $redis;
    
    public int $limitPerIteration = 1000;
    
    public function __construct(string $host = '127.0.0.1', int $port = 6379, ?string $password = null) {
        $this->redis = new Client([
            'scheme' => 'tcp',
            'host' => $host,
            'port' => $port,
            'password' => $password
        ]);
        $this->lineHandler = function ($line) {
            echo $line . "\n";
        };
    }
    
    public function getClient(): Client {
        return $this->redis;
    }
    
    public function setOutputLineHandler(\Closure $line): void {
        $this->lineHandler = $line;
    }
    
    protected function line(string $message): void {
        call_user_func($this->lineHandler, $message);
    }
    
    public function getDatabases(): array {
        $keyspace = $this->redis->info('keyspace');
        $databases = [];
        foreach ($keyspace['Keyspace'] as $db => $keysInfo) {
            $dbId = (int)substr($db, 2);
            $keysInfo['db'] = $dbId;
            $databases[$dbId] = $keysInfo;
        }
        return $databases;
    }
    
    public function analyzeDatabases(): array {
        $databases = array_keys($this->getDatabases());
        $report = [];
        foreach ($databases as $db) {
            if (!$this->redis->select($db)) {
                $this->line('DB failed to be selected: ' . $db);
                continue;
            }
            
            $report[$db] = [
                'db' => $db, //total count
                'count' => 0, //total count
                'size' => 0, //total size
                'neverExpire' => 0, //the count of never expired keys
                'expired' => 0, //the count of expired keys
                'active' => 0, //the count of not expired keys
                'avgTtl' => 0, //the average ttl of the going to be expired keys
            ];
            $keys = new Keyspace($this->redis, null, $this->limitPerIteration);
            foreach ($keys as $key) {
                $ttl = $this->redis->ttl($key);
                if ($ttl) {
                    ++$report[$db]['count'];
                    switch ($ttl) {
                        case -2:
                            ++$report[$db]['expired'];
                            break;
                        case -1:
                            ++$report[$db]['neverExpire'];
                            break;
                        default:
                            ++$report[$db]['active'];
                            $totalTtl = $report[$db]['avgTtl'] * ($report[$db]['active'] - 1) + $ttl;
                            $report[$db]['avgTtl'] = $totalTtl / $report[$db]['active'];
                            break;
                    }
                    $debug = $this->redis->executeRaw(['debug', 'object', $key]);
                    if ($debug) {
                        $debug = explode(' ', $debug);
                        $lens = explode(':', $debug[4]);
                        $report[$db]['size'] += $lens[1];//approximate memory usage by serializedlength
                    }
                }
            }
            if ($report[$db]['size'] > 1024 * 100) {
                $report[$db]['size'] = round($report[$db]['size'] / 1024 / 1024, 2) . ' Mb';
            } else {
                $report[$db]['size'] = round($report[$db]['size'] / 1024, 2) . ' Kb';
            }
            $report[$db]['avgTtl'] = round($report[$db]['avgTtl']);
        }
        return $report;
    }
    
    public function printTable(array $headers, array $rows): void {
        $separator = ' | ';
        $prefix = '| ';
        $suffix = ' |';
        $colSizes = [];
        foreach ($headers as $i => $header) {
            $colSizes[$i] = mb_strlen($header);
        }
        foreach ($rows as $row) {
            foreach ($colSizes as $key => &$maxLen) {
                if (isset($row[$key])) {
                    $len = mb_strlen($row[$key]);
                    if ($len > $maxLen) {
                        $maxLen = $len;
                    }
                }
            }
            unset($maxLen);
        }
        $line = [];
        foreach ($headers as $i => $header) {
            $line[] = str_pad($header, $colSizes[$i]);
        }
        $headersLine = implode($separator, $line);
        $horizontalSeparator = str_pad('', mb_strlen($headersLine) + mb_strlen($prefix) + mb_strlen($suffix), '-');
        $this->line($horizontalSeparator);
        $this->line($prefix . $headersLine . $suffix);
        $this->line($horizontalSeparator);
        foreach ($rows as $row) {
            $line = [];
            foreach ($colSizes as $key => $colSize) {
                $line[] = str_pad($row[$key], $colSize);
            }
            $this->line($prefix . implode($separator, $line) . $suffix);
        }
        $this->line($horizontalSeparator);
    }
    
    
}