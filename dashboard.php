<?php
// ============================================================
// dashboard.php — Painel IoT com ESP32
// ============================================================

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

requireAuth();

$pdo      = getConnection();
$user     = currentUser();
$deviceId = $_GET['device'] ?? 'esp32-01';

// Lista todos os dispositivos cadastrados
$devices = $pdo->query("SELECT device_id, last_seen FROM iot_devices ORDER BY device_id")->fetchAll();

// Estado inicial do dispositivo selecionado
$device = $pdo->prepare('SELECT * FROM iot_devices WHERE device_id = ?');
$device->execute([$deviceId]);
$device = $device->fetch();

$pageTitle  = 'Dashboard IoT';
$activeMenu = 'dashboard';

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* ── IoT Dashboard ─────────────────────────────────────── */

.iot-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.iot-header h1 {
    font-family: 'Sora', sans-serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: var(--t1);
    display: flex;
    align-items: center;
    gap: .6rem;
}

.device-select {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    border-radius: 8px;
    color: var(--t1);
    padding: .45rem .9rem;
    font-size: .85rem;
    cursor: pointer;
    outline: none;
}

/* Status pill */
.status-pill {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .3rem .8rem;
    border-radius: 99px;
    font-size: .78rem;
    font-weight: 600;
    transition: all .3s;
}
.status-pill.online  { background: color-mix(in srgb, var(--accent) 15%, transparent); color: var(--accent); }
.status-pill.offline { background: color-mix(in srgb, var(--t3) 15%, transparent);    color: var(--t3); }

.status-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    background: currentColor;
}
.status-pill.online .status-dot {
    animation: pulse-dot 1.5s ease infinite;
    box-shadow: 0 0 0 0 currentColor;
}
@keyframes pulse-dot {
    0%   { box-shadow: 0 0 0 0 color-mix(in srgb, var(--accent) 60%, transparent); }
    70%  { box-shadow: 0 0 0 6px transparent; }
    100% { box-shadow: 0 0 0 0 transparent; }
}

/* IoT grid */
.iot-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.2rem;
    margin-bottom: 1.5rem;
}

/* Indicador visual */
.iot-indicator-card {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.2rem;
    transition: border-color .3s;
}

.indicator-ring {
    position: relative;
    width: 120px;
    height: 120px;
}

.indicator-ring svg {
    width: 100%;
    height: 100%;
    transform: rotate(-90deg);
}

.indicator-ring .track {
    fill: none;
    stroke: var(--border-soft);
    stroke-width: 8;
}

.indicator-ring .fill {
    fill: none;
    stroke-width: 8;
    stroke-linecap: round;
    stroke-dasharray: 283;
    stroke-dashoffset: 283;
    transition: stroke-dashoffset .6s cubic-bezier(.4,0,.2,1), stroke .4s;
}

.indicator-icon {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.2rem;
    transition: all .4s;
}

.indicator-label {
    font-family: 'Sora', sans-serif;
    font-size: .95rem;
    font-weight: 600;
    color: var(--t1);
    text-align: center;
}

.indicator-sublabel {
    font-size: .78rem;
    color: var(--t3);
    text-align: center;
    margin-top: -.6rem;
}

/* Indicador de botão */
.btn-indicator[data-state="1"] .fill  { stroke: var(--accent); stroke-dashoffset: 0; }
.btn-indicator[data-state="1"] .indicator-icon { filter: drop-shadow(0 0 12px var(--accent)); }

/* Indicador de LED */
.led-indicator[data-state="1"] .fill  { stroke: var(--yellow); stroke-dashoffset: 0; }
.led-indicator[data-state="1"] .indicator-icon { filter: drop-shadow(0 0 14px var(--yellow)); }

/* Controle do LED */
.led-control-card {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.led-control-card h3 {
    font-family: 'Sora', sans-serif;
    font-size: .9rem;
    font-weight: 600;
    color: var(--t1);
}

/* Toggle switch */
.toggle-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.toggle-label {
    font-size: .875rem;
    color: var(--t2);
}

.toggle-switch {
    position: relative;
    width: 52px;
    height: 28px;
    flex-shrink: 0;
}

.toggle-switch input { opacity: 0; width: 0; height: 0; }

.toggle-slider {
    position: absolute;
    cursor: pointer;
    inset: 0;
    background: var(--border-soft);
    border-radius: 99px;
    transition: background .3s;
}

.toggle-slider:before {
    content: '';
    position: absolute;
    left: 4px; top: 4px;
    width: 20px; height: 20px;
    border-radius: 50%;
    background: white;
    transition: transform .3s cubic-bezier(.4,0,.2,1);
    box-shadow: 0 2px 4px rgba(0,0,0,.3);
}

.toggle-switch input:checked + .toggle-slider {
    background: var(--yellow);
}

.toggle-switch input:checked + .toggle-slider:before {
    transform: translateX(24px);
}

/* Log de eventos */
.event-log {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    padding: 1.5rem;
}

.event-log h3 {
    font-family: 'Sora', sans-serif;
    font-size: .9rem;
    font-weight: 600;
    color: var(--t1);
    margin-bottom: 1rem;
}

.log-list {
    display: flex;
    flex-direction: column;
    gap: .5rem;
    max-height: 220px;
    overflow-y: auto;
}

.log-item {
    display: flex;
    align-items: center;
    gap: .7rem;
    padding: .5rem .6rem;
    border-radius: 8px;
    background: var(--bg);
    font-size: .8rem;
    color: var(--t2);
    animation: log-appear .3s ease;
}

@keyframes log-appear {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: none; }
}

.log-dot {
    width: 7px; height: 7px;
    border-radius: 50%;
    flex-shrink: 0;
}

.log-time {
    color: var(--t3);
    font-size: .73rem;
    margin-left: auto;
    white-space: nowrap;
}

/* Last seen badge */
.last-seen {
    font-size: .75rem;
    color: var(--t3);
    text-align: right;
}

/* Seção de referência rápida da API */
.api-ref {
    background: var(--surface);
    border: 1px solid var(--border-soft);
    border-radius: 16px;
    padding: 1.5rem;
    margin-top: 1.5rem;
}

.api-ref h3 {
    font-family: 'Sora', sans-serif;
    font-size: .9rem;
    font-weight: 600;
    color: var(--t1);
    margin-bottom: 1rem;
}

.api-code {
    background: var(--bg);
    border: 1px solid var(--border-soft);
    border-radius: 8px;
    padding: 1rem 1.2rem;
    font-family: 'Fira Code', 'Cascadia Code', monospace;
    font-size: .78rem;
    color: var(--t2);
    overflow-x: auto;
    white-space: pre;
    line-height: 1.7;
}

.api-method {
    color: var(--accent);
    font-weight: 700;
}

.api-url {
    color: var(--blue);
}

.api-comment {
    color: var(--t3);
}
</style>

<!-- Header do painel -->
<div class="iot-header">
    <h1>
        <span>⚡</span> Dashboard IoT
        <span class="status-pill offline" id="statusPill">
            <span class="status-dot"></span>
            <span id="statusText">Aguardando...</span>
        </span>
    </h1>

    <div style="display:flex;align-items:center;gap:.8rem;flex-wrap:wrap">
        <select class="device-select" id="deviceSelect" onchange="changeDevice(this.value)">
            <?php foreach ($devices as $d): ?>
                <option value="<?= htmlspecialchars($d['device_id']) ?>"
                    <?= $d['device_id'] === $deviceId ? 'selected' : '' ?>>
                    <?= htmlspecialchars($d['device_id']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="last-seen" id="lastSeen">—</span>
    </div>
</div>

<!-- Indicadores e controles -->
<div class="iot-grid">

    <!-- Indicador: Botão físico -->
    <div class="iot-indicator-card">
        <div class="indicator-ring btn-indicator" data-state="0" id="btnIndicator">
            <svg viewBox="0 0 100 100">
                <circle class="track" cx="50" cy="50" r="45"/>
                <circle class="fill"  cx="50" cy="50" r="45"/>
            </svg>
            <div class="indicator-icon" id="btnIcon">🔘</div>
        </div>
        <div class="indicator-label">Botão Físico</div>
        <div class="indicator-sublabel" id="btnState">Aguardando ESP32…</div>
    </div>

    <!-- Indicador: Estado do LED -->
    <div class="iot-indicator-card">
        <div class="indicator-ring led-indicator" data-state="0" id="ledIndicator">
            <svg viewBox="0 0 100 100">
                <circle class="track" cx="50" cy="50" r="45"/>
                <circle class="fill"  cx="50" cy="50" r="45"/>
            </svg>
            <div class="indicator-icon" id="ledIcon">💡</div>
        </div>
        <div class="indicator-label">LED</div>
        <div class="indicator-sublabel" id="ledState">Aguardando ESP32…</div>
    </div>

    <!-- Controle do LED -->
    <div class="led-control-card">
        <h3>⚙️ Controles</h3>

        <div class="toggle-row">
            <span class="toggle-label">Ligar / Desligar LED</span>
            <label class="toggle-switch">
                <input type="checkbox" id="ledToggle" onchange="sendCommand('led', this.checked ? 1 : 0)">
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div style="height:1px;background:var(--border-soft)"></div>

        <div style="display:flex;gap:.7rem;flex-wrap:wrap">
            <button class="btn btn-secondary" style="flex:1" onclick="sendCommand('led', 1)">
                💡 Ligar LED
            </button>
            <button class="btn btn-ghost" style="flex:1" onclick="sendCommand('led', 0)">
                ⬤ Desligar
            </button>
        </div>

        <div style="font-size:.75rem;color:var(--t3);text-align:center" id="cmdStatus">
            Nenhum comando enviado ainda
        </div>
    </div>
</div>

<!-- Log de eventos -->
<div class="event-log">
    <h3>📋 Log de Eventos</h3>
    <div class="log-list" id="logList">
        <div class="log-item" style="color:var(--t3)">Nenhum evento ainda…</div>
    </div>
</div>

<!-- Referência rápida da API para configurar o ESP32 -->
<div class="api-ref">
    <h3>📡 Referência da API — Configure no ESP32</h3>
    <div class="api-code"><span class="api-comment">// ESP32 → Reporta estado e recebe comandos (a cada 2s)</span>
<span class="api-method">POST</span> <span class="api-url"><?= rtrim($_SERVER['HTTP_HOST'] ?? 'seu-servidor.com', '/') ?>/api/device.php</span>
Content-Type: application/json
{"device_id": "esp32-01", "button": 1, "led": 0}

<span class="api-comment">// Resposta com comandos pendentes:</span>
{"status": "ok", "commands": [{"command": "led", "value": 1}]}

<span class="api-comment">// Dashboard → Verifica estado (polling a cada 2s)</span>
<span class="api-method">GET</span>  <span class="api-url"><?= rtrim($_SERVER['HTTP_HOST'] ?? 'seu-servidor.com', '/') ?>/api/device.php?device_id=esp32-01</span>
{"device_id":"esp32-01","button":0,"led":0,"online":true}</div>
</div>

<script>
// ── Configuração ──────────────────────────────────────────────
const API_BASE  = '<?= APP_BASE ?>/api';
let   deviceId  = '<?= htmlspecialchars($deviceId) ?>';
let   prevState = { button: -1, led: -1, online: null };
let   logItems  = [];

// ── Polling principal ────────────────────────────────────────

async function poll() {
    try {
        const res  = await fetch(`${API_BASE}/device.php?device_id=${deviceId}`);
        const data = await res.json();
        updateUI(data);
    } catch (e) {
        setOffline();
    }
}

function updateUI(data) {
    const { button, led, online, last_seen } = data;

    // Status online/offline
    const pill = document.getElementById('statusPill');
    const txt  = document.getElementById('statusText');
    pill.className = `status-pill ${online ? 'online' : 'offline'}`;
    txt.textContent = online ? 'Online' : 'Offline';

    // Último contato
    if (last_seen) {
        const d = new Date(last_seen.replace(' ', 'T') + 'Z');
        document.getElementById('lastSeen').textContent =
            'Visto: ' + d.toLocaleTimeString('pt-BR');
    }

    // Indicador: botão físico
    const btnInd = document.getElementById('btnIndicator');
    btnInd.dataset.state = button;
    document.getElementById('btnState').textContent =
        button ? '🟢 Pressionado' : '⚫ Solto';
    document.getElementById('btnIcon').textContent = button ? '🟢' : '🔘';

    // Indicador: LED
    const ledInd = document.getElementById('ledIndicator');
    ledInd.dataset.state = led;
    document.getElementById('ledState').textContent =
        led ? '🟡 Ligado' : '⚫ Desligado';
    document.getElementById('ledIcon').textContent = led ? '💡' : '🔦';

    // Sincroniza o toggle com o estado real
    document.getElementById('ledToggle').checked = !!led;

    // Registra no log apenas mudanças de estado
    if (prevState.button !== button && prevState.button !== -1) {
        addLog(
            button ? 'Botão físico pressionado' : 'Botão físico solto',
            button ? '#22c55e' : '#6b7280'
        );
    }

    if (prevState.led !== led && prevState.led !== -1) {
        addLog(
            led ? 'LED ligado pelo dashboard' : 'LED desligado',
            led ? '#facc15' : '#6b7280'
        );
    }

    if (prevState.online !== online) {
        addLog(
            online ? '✔ ESP32 conectado' : '✘ ESP32 desconectado',
            online ? '#22c55e' : '#ef4444'
        );
    }

    prevState = { button, led, online };
}

function setOffline() {
    const pill = document.getElementById('statusPill');
    pill.className = 'status-pill offline';
    document.getElementById('statusText').textContent = 'Sem resposta';
}

// ── Envio de comando ─────────────────────────────────────────

async function sendCommand(command, value) {
    const statusEl = document.getElementById('cmdStatus');
    statusEl.textContent = 'Enviando…';

    try {
        const res = await fetch(`${API_BASE}/command.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ device_id: deviceId, command, value }),
        });
        const data = await res.json();

        if (data.status === 'queued') {
            statusEl.textContent = `✔ Comando "${command}=${value}" enfileirado`;
            addLog(
                `Comando enviado: ${command} = ${value}`,
                value ? '#facc15' : '#6b7280'
            );
        } else {
            statusEl.textContent = `Erro: ${data.error ?? 'desconhecido'}`;
        }
    } catch (e) {
        statusEl.textContent = '✘ Falha na conexão';
    }
}

// ── Log ──────────────────────────────────────────────────────

function addLog(message, color = '#6b7280') {
    const now = new Date().toLocaleTimeString('pt-BR');

    logItems.unshift({ message, color, time: now });
    if (logItems.length > 30) logItems.pop();

    const list = document.getElementById('logList');
    list.innerHTML = logItems.map(item => `
        <div class="log-item">
            <span class="log-dot" style="background:${item.color}"></span>
            ${item.message}
            <span class="log-time">${item.time}</span>
        </div>
    `).join('');
}

// ── Troca de dispositivo ─────────────────────────────────────

function changeDevice(id) {
    deviceId = id;
    prevState = { button: -1, led: -1, online: null };
    addLog(`Dispositivo alterado para: ${id}`, '#60a5fa');
    poll();
    const url = new URL(window.location);
    url.searchParams.set('device', id);
    window.history.replaceState({}, '', url);
}

// ── Inicialização ────────────────────────────────────────────

poll();
setInterval(poll, 500);    // polling a cada 2 segundos
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>