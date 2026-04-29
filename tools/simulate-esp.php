#!/usr/bin/env php
<?php

declare(strict_types=1);

$rootPath = dirname(__DIR__);

$options = parseArguments(array_slice($argv, 1));
$command = $options['_command'] ?? 'help';

$baseUrl = rtrim((string) ($options['base'] ?? envValue($rootPath, 'ESP_SIM_BASE_URL') ?? 'http://127.0.0.1:8000'), '/');
$deviceId = (string) ($options['device'] ?? envValue($rootPath, 'ESP_SIM_DEVICE_ID') ?? '14:08:08:A6:69:34');
$uid = (string) ($options['uid'] ?? envValue($rootPath, 'ESP_SIM_UID') ?? '37 DB 7E 5');

if (in_array($command, ['help', '--help', '-h'], true)) {
    showUsage();
    exit(0);
}

try {
    match ($command) {
        'check' => readLockerStatus($baseUrl, $deviceId),
        'status' => sendSensorStatus($baseUrl, $deviceId, switchState($options['locstatus'] ?? $options['state'] ?? 0)),
        'tap' => sendTap($baseUrl, $deviceId, $uid),
        'borrow' => simulateBorrow($baseUrl, $deviceId, $uid),
        'return' => simulateReturn($baseUrl, $deviceId, $uid),
        'cycle' => simulateCycle($baseUrl, $deviceId, $uid),
        default => throw new RuntimeException("Command '{$command}' tidak dikenal. Jalankan: php tools/simulate-esp.php help"),
    };
} catch (Throwable $exception) {
    fwrite(STDERR, PHP_EOL.'ERROR: '.$exception->getMessage().PHP_EOL);
    exit(1);
}

function simulateBorrow(string $baseUrl, string $deviceId, string $uid): bool
{
    writeln('Simulasi pinjam: switch ketekan, RFID tap, lalu barang diambil.');

    $current = readLockerStatus($baseUrl, $deviceId);

    if (($current['locker_status'] ?? null) !== 'available') {
        writeln(PHP_EOL.'Loker belum tersedia, jadi simulator tidak mengubah nilai switch.');
        writeln('Simulator hanya mengirim tap RFID seperti orang mencoba akses loker yang masih dipinjam.');
        sendTap($baseUrl, $deviceId, $uid, false);

        return true;
    }

    if ((int) ($current['switch_state'] ?? $current['locstatus'] ?? -1) !== 0) {
        sendSensorStatus($baseUrl, $deviceId, 0);
    }

    sendTap($baseUrl, $deviceId, $uid);
    sendSensorStatus($baseUrl, $deviceId, 1);

    return true;
}

function simulateCycle(string $baseUrl, string $deviceId, string $uid): bool
{
    simulateBorrow($baseUrl, $deviceId, $uid);
    simulateReturn($baseUrl, $deviceId, $uid);

    return true;
}

function simulateReturn(string $baseUrl, string $deviceId, string $uid): bool
{
    writeln('Simulasi kembali: locker kosong, RFID tap, lalu barang masuk lagi.');
    sendSensorStatus($baseUrl, $deviceId, 1);
    sendTap($baseUrl, $deviceId, $uid);
    sendSensorStatus($baseUrl, $deviceId, 0);

    return true;
}

function sendSensorStatus(string $baseUrl, string $deviceId, int $locstatus): void
{
    postJson($baseUrl.'/api/getStatus', [
        'device_id' => $deviceId,
        'locstatus' => $locstatus,
    ]);
}

function readLockerStatus(string $baseUrl, string $deviceId): array
{
    return postJson($baseUrl.'/api/getStatus', [
        'device_id' => $deviceId,
    ]);
}

function sendTap(string $baseUrl, string $deviceId, string $uid, bool $failOnError = true): void
{
    postJson($baseUrl.'/api/tab', [
        'uid' => $uid,
        'device_id' => $deviceId,
    ], $failOnError);
}

function postJson(string $url, array $payload, bool $failOnError = true): array
{
    writeln(PHP_EOL.'POST '.$url);
    writeln('Payload: '.json_encode($payload, JSON_UNESCAPED_SLASHES));

    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nAccept: application/json\r\n",
            'content' => json_encode($payload),
            'ignore_errors' => true,
            'timeout' => 15,
        ],
    ]);

    $response = @file_get_contents($url, false, $context);
    $statusLine = $http_response_header[0] ?? 'HTTP response tidak tersedia';

    if ($response === false) {
        throw new RuntimeException("Gagal menghubungi {$url}. Pastikan server Laravel sedang jalan dan base URL benar.");
    }

    writeln('Response: '.$statusLine);

    $decoded = json_decode($response, true);
    writeln(json_last_error() === JSON_ERROR_NONE
        ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        : $response);

    $statusCode = httpStatusCode($statusLine);

    if ($failOnError && $statusCode >= 400) {
        throw new RuntimeException("API membalas error {$statusCode}. Cek response di atas.");
    }

    return is_array($decoded) ? $decoded : ['raw' => $response];
}

function parseArguments(array $arguments): array
{
    $parsed = [];
    $count = count($arguments);

    for ($index = 0; $index < $count; $index++) {
        $argument = $arguments[$index];

        if ($index === 0 && ! str_starts_with($argument, '--')) {
            $parsed['_command'] = $argument;
            continue;
        }

        if (! str_starts_with($argument, '--')) {
            continue;
        }

        $option = substr($argument, 2);
        [$key, $value] = array_pad(explode('=', $option, 2), 2, true);

        if ($value === true && isset($arguments[$index + 1]) && ! str_starts_with($arguments[$index + 1], '--')) {
            $value = $arguments[++$index];
        }

        $parsed[$key] = $value;
    }

    return $parsed;
}

function switchState(mixed $value): int
{
    if (! in_array((string) $value, ['0', '1'], true)) {
        throw new InvalidArgumentException('locstatus harus 0 atau 1.');
    }

    return (int) $value;
}

function envValue(string $rootPath, string $key): ?string
{
    $processValue = getenv($key);

    if ($processValue !== false && $processValue !== '') {
        return $processValue;
    }

    $envPath = $rootPath.DIRECTORY_SEPARATOR.'.env';

    if (! is_file($envPath)) {
        return null;
    }

    foreach (file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
            continue;
        }

        [$envKey, $envValue] = explode('=', $line, 2);

        if (trim($envKey) === $key) {
            return trim(trim($envValue), "\"'");
        }
    }

    return null;
}

function httpStatusCode(string $statusLine): int
{
    preg_match('/\s(\d{3})\s?/', $statusLine, $matches);

    return isset($matches[1]) ? (int) $matches[1] : 0;
}

function showUsage(): void
{
    writeln(<<<'TEXT'
SIEMOLA ESP Simulator

Contoh:
  php tools/simulate-esp.php status --locstatus=0
  php tools/simulate-esp.php status --locstatus=1
  php tools/simulate-esp.php check
  php tools/simulate-esp.php tap --uid="37 DB 7E 5"
  php tools/simulate-esp.php borrow
  php tools/simulate-esp.php return
  php tools/simulate-esp.php cycle

Opsi:
  --base=http://127.0.0.1:8000
  --device=14:08:08:A6:69:34
  --uid="37 DB 7E 5"

Catatan:
  Default base URL simulator adalah http://127.0.0.1:8000 untuk php artisan serve.
  Karena sketch memakai INPUT_PULLUP:
  locstatus=0 artinya switch ketekan / ada barang.
  locstatus=1 artinya switch tidak ketekan / kosong.
TEXT);
}

function writeln(string $text): void
{
    fwrite(STDOUT, $text.PHP_EOL);
}
