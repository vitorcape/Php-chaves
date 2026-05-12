<?php
// ============================================================
// api/device.php — API REST para comunicação com ESP32
//
// GET  ?device_id=esp32-01           → retorna estado atual (para o dashboard)
// POST {device_id, button, led}      → ESP32 reporta seu estado, recebe comandos
// ============================================================

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');            // permite ESP32 chamar de qualquer IP
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/db.php';

$pdo = getConnection();
$method = $_SERVER['REQUEST_METHOD'];

// ── Helpers ──────────────────────────────────────────────────

function jsonOut(array $data, int $status = 200): void {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function getDevice(PDO $pdo, string $deviceId): ?array {
    $stmt = $pdo->prepare('SELECT * FROM iot_devices WHERE device_id = ?');
    $stmt->execute([$deviceId]);
    return $stmt->fetch() ?: null;
}

// ── GET: Dashboard consulta o estado atual ────────────────────

if ($method === 'GET') {
    $deviceId = $_GET['device_id'] ?? 'esp32-01';

    $device = getDevice($pdo, $deviceId);

    if (!$device) {
        jsonOut(['error' => 'Device not found'], 404);
    }

    // Calcula "online" como: visto nos últimos 10 segundos
    $online = false;
    if ($device['last_seen']) {
        $diff = time() - strtotime($device['last_seen']);
        $online = $diff <= 10;
    }

    jsonOut([
        'device_id' => $device['device_id'],
        'button'    => (int) $device['button'],
        'led'       => (int) $device['led'],
        'online'    => $online,
        'last_seen' => $device['last_seen'],
    ]);
}

// ── POST: ESP32 reporta estado e recebe comandos pendentes ────

if ($method === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    // Suporte a form-data também (útil para testes com curl)
    if (!$body) {
        $body = $_POST;
    }

    $deviceId = $body['device_id'] ?? null;

    if (!$deviceId) {
        jsonOut(['error' => 'device_id required'], 400);
    }

    $button = isset($body['button']) ? (int) $body['button'] : 0;
    $led    = isset($body['led'])    ? (int) $body['led']    : 0;

    // Upsert: cria dispositivo se não existir, ou atualiza estado
    $stmt = $pdo->prepare('
        INSERT INTO iot_devices (device_id, button, led, last_seen)
        VALUES (?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE
            button    = VALUES(button),
            led       = VALUES(led),
            last_seen = NOW()
    ');
    $stmt->execute([$deviceId, $button, $led]);

    // Busca comandos pendentes para este dispositivo
    $stmt = $pdo->prepare('
        SELECT id, command, value
        FROM iot_commands
        WHERE device_id = ? AND executed = 0
        ORDER BY id ASC
    ');
    $stmt->execute([$deviceId]);
    $commands = $stmt->fetchAll();

    // Marca todos como executados
    if (!empty($commands)) {
        $ids = implode(',', array_column($commands, 'id'));
        $pdo->exec("UPDATE iot_commands SET executed = 1 WHERE id IN ($ids)");
    }

    // Retorna os comandos pendentes para o ESP32 aplicar
    jsonOut([
        'status'   => 'ok',
        'commands' => array_map(fn($c) => [
            'command' => $c['command'],
            'value'   => (int) $c['value'],
        ], $commands),
    ]);
}

jsonOut(['error' => 'Method not allowed'], 405);