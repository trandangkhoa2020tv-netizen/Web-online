<?php

function connectDatabase(array $defaults, string $localConfigPath): PDO
{
    $environmentKeys = [
        'host' => 'DB_HOST',
        'port' => 'DB_PORT',
        'dbname' => 'DB_NAME',
        'username' => 'DB_USER',
        'password' => 'DB_PASSWORD',
    ];
    $config = $defaults;
    if (is_file($localConfigPath)) {
        $localConfig = require $localConfigPath;
        if (!is_array($localConfig)) {
            throw new RuntimeException('Database local configuration must return an array.');
        }
        $config = array_replace($config, array_intersect_key($localConfig, $defaults));
    }

    foreach ($environmentKeys as $key => $environmentKey) {
        $value = getenv($environmentKey);
        if ($value !== false) {
            $config[$key] = $value;
        }
    }

    foreach (['host', 'dbname', 'username'] as $requiredKey) {
        if (!array_key_exists($requiredKey, $config) || trim((string)$config[$requiredKey]) === '') {
            throw new RuntimeException('Database configuration is incomplete. Set DB_* environment variables or create the local config file.');
        }
    }

    $config['host'] = trim((string)($config['host'] ?? 'localhost')) ?: 'localhost';
    $config['dbname'] = trim((string)($config['dbname'] ?? 'uth_shop')) ?: 'uth_shop';
    $config['username'] = trim((string)($config['username'] ?? 'root')) ?: 'root';
    $config['password'] = (string)($config['password'] ?? '');

    $candidatePorts = [];
    $portValue = trim((string)($config['port'] ?? ''));
    if ($portValue !== '') {
        $candidatePorts[] = $portValue;
    }
    foreach (['3307', '3306', '3308', '3309'] as $candidatePort) {
        if (!in_array($candidatePort, $candidatePorts, true)) {
            $candidatePorts[] = $candidatePort;
        }
    }

    $lastException = null;
    foreach ($candidatePorts as $candidatePort) {
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $candidatePort . ';dbname=' . $config['dbname'] . ';charset=utf8mb4';
        try {
            return new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $exception) {
            $lastException = $exception;
        }
    }

    error_log('Database connection failed: ' . $lastException?->getMessage());
    throw new RuntimeException('Database connection failed. Check the DB_* configuration and database server.');
}
