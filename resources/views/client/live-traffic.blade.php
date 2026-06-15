{{-- resources/views/client/live-traffic.blade.php --}}
@extends('client.layout')
@section('title', 'Live Traffic')

@section('extra_css')
<style>
    .traffic-card {
        background: #fff; border-radius: 12px;
        border: 1px solid #eef0f5; padding: 20px;
        margin-bottom: 20px;
    }
    .session-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 14px; margin-bottom: 20px;
    }
    .session-stat {
        background: #f8f9fc; border-radius: 10px;
        padding: 14px 16px; text-align: center;
    }
    .session-stat .s-val { font-size: 18px; font-weight: 700; color: #1a1f36; margin-bottom: 4px; }
    .session-stat .s-lbl { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: .4px; }
    .session-stat.tx .s-val { color: #4e73df; }
    .session-stat.rx .s-val { color: #e74c3c; }
    .session-stat.up .s-val { color: #00c897; }

    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 14px; border-radius: 999px; font-size: 13px; font-weight: 600;
    }
    .status-pill.online  { background: #dcfce7; color: #15803d; }
    .status-pill.offline { background: #fee2e2; color: #b91c1c; }
    .status-dot { width: 8px; height: 8px; border-radius: 50%; }
    .status-dot.online  { background: #16a34a; animation: pulse 1.5s infinite; }
    .status-dot.offline { background: #dc2626; }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .4; }
    }

    #chartContainer {
        position: relative; height: 340px;
    }
    .chart-legend {
        display: flex; gap: 16px; margin-bottom: 12px;
    }
    .legend-item {
        display: flex; align-items: center; gap: 6px;
        font-size: 12px; color: #555;
    }
    .legend-dot { width: 10px; height: 10px; border-radius: 50%; }
</style>
@endsection

@section('content')

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px;">
    <div class="page-title" style="margin:0;">Traffic Monitor</div>
    <div style="font-size:12px; color:#aaa;">
        {{ \App\Models\Setting::get('company_name', 'SmartISP') }} &rsaquo; Traffic Monitor
    </div>
</div>

{{-- Session Header --}}
<div class="traffic-card">
    <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
        <div>
            <div style="font-size:15px; font-weight:600; color:#1a1f36;">
                Traffic Monitor: <span style="color:#4e73df;">{{ $customer->pppoe_username ?? 'N/A' }}</span>
            </div>
            <div style="font-size:12px; color:#aaa; margin-top:3px;">
                Package: {{ $customer->package->name ?? 'N/A' }}
                &nbsp;|&nbsp;
                Speed: {{ $customer->package->speed_download ?? '—' }}Mbps / {{ $customer->package->speed_upload ?? '—' }}Mbps
            </div>
        </div>
        <div id="statusPill" class="status-pill offline">
            <span class="status-dot offline" id="statusDot"></span>
            <span id="statusText">Checking...</span>
        </div>
    </div>

    {{-- Session Stats --}}
    <div class="session-grid">
        <div class="session-stat up">
            <div class="s-val" id="statUptime">—</div>
            <div class="s-lbl">Uptime</div>
        </div>
        <div class="session-stat">
            <div class="s-val" id="statIp">—</div>
            <div class="s-lbl">IP Address</div>
        </div>
        <div class="session-stat tx">
            <div class="s-val" id="statTx">0 B</div>
            <div class="s-lbl"><i class="fas fa-arrow-up"></i> Upload (TX)</div>
        </div>
        <div class="session-stat rx">
            <div class="s-val" id="statRx">0 B</div>
            <div class="s-lbl"><i class="fas fa-arrow-down"></i> Download (RX)</div>
        </div>
    </div>
</div>

{{-- Chart --}}
<div class="traffic-card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-dot" style="background:#4e73df;"></div>
                <span id="txLegend">TX 0 bps</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background:#e74c3c;"></div>
                <span id="rxLegend">RX 0 bps</span>
            </div>
        </div>
        <div style="font-size:12px; color:#aaa;">
            Auto refresh every <strong>5s</strong>
        </div>
    </div>

    <div id="chartContainer">
        <canvas id="trafficChart"></canvas>
    </div>
</div>

@endsection

@section('extra_js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
const SESSION_URL = '{{ route("client.session-data") }}';
const MAX_POINTS  = 20;

// Chart setup
const ctx = document.getElementById('trafficChart').getContext('2d');
const labels   = [];
const txData   = [];
const rxData   = [];

const chart = new Chart(ctx, {
    type: 'line',
    data: {
        labels,
        datasets: [
            {
                label: 'TX (Upload)',
                data: txData,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78,115,223,.08)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#4e73df',
                tension: 0.3,
                fill: true,
            },
            {
                label: 'RX (Download)',
                data: rxData,
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231,76,60,.08)',
                borderWidth: 2,
                pointRadius: 3,
                pointBackgroundColor: '#e74c3c',
                tension: 0.3,
                fill: true,
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.dataset.label + ': ' + formatBps(ctx.raw)
                }
            }
        },
        scales: {
            x: {
                grid: { color: '#f0f2f7' },
                ticks: { font: { size: 11 }, color: '#aaa' }
            },
            y: {
                grid: { color: '#f0f2f7' },
                ticks: {
                    font: { size: 11 }, color: '#aaa',
                    callback: val => formatBps(val)
                },
                beginAtZero: true,
            }
        }
    }
});

// Helpers
function formatBps(bps) {
    bps = parseInt(bps) || 0;
    if (bps >= 1000000) return (bps / 1000000).toFixed(2) + ' Mbps';
    if (bps >= 1000)    return (bps / 1000).toFixed(2) + ' Kbps';
    return bps + ' bps';
}

function formatBytes(bytes) {
    bytes = parseInt(bytes) || 0;
    if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
    if (bytes >= 1048576)    return (bytes / 1048576).toFixed(2) + ' MB';
    if (bytes >= 1024)       return (bytes / 1024).toFixed(2) + ' KB';
    return bytes + ' B';
}

function getTime() {
    return new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
}

// Fetch & update
let prevTx = 0, prevRx = 0, firstLoad = true;

async function fetchSession() {
    try {
        const res  = await fetch(SESSION_URL);
        const data = await res.json();

        const time = getTime();

        if (!data.online) {
            // Offline
            document.getElementById('statusPill').className = 'status-pill offline';
            document.getElementById('statusDot').className  = 'status-dot offline';
            document.getElementById('statusText').textContent = 'Offline';
            document.getElementById('statUptime').textContent = '—';
            document.getElementById('statIp').textContent     = '—';
            document.getElementById('statTx').textContent     = '0 B';
            document.getElementById('statRx').textContent     = '0 B';

            pushChart(time, 0, 0);
            return;
        }

        // Online
        document.getElementById('statusPill').className = 'status-pill online';
        document.getElementById('statusDot').className  = 'status-dot online';
        document.getElementById('statusText').textContent = 'Online';
        document.getElementById('statUptime').textContent = data.uptime;
        document.getElementById('statIp').textContent     = data.ip;
        document.getElementById('statTx').textContent     = formatBytes(data.tx_bytes);
        document.getElementById('statRx').textContent     = formatBytes(data.rx_bytes);

        // Calculate rate from bytes diff
        let txRate = parseInt(data.tx_rate) || 0;
        let rxRate = parseInt(data.rx_rate) || 0;

        // Fallback: calculate from byte diff if rate not available
        if (txRate === 0 && !firstLoad) {
            txRate = Math.max(0, (data.tx_bytes - prevTx)) * 8 / 5; // bits per second over 5s
            rxRate = Math.max(0, (data.rx_bytes - prevRx)) * 8 / 5;
        }

        prevTx = data.tx_bytes;
        prevRx = data.rx_bytes;
        firstLoad = false;

        document.getElementById('txLegend').textContent = 'TX ' + formatBps(txRate);
        document.getElementById('rxLegend').textContent = 'RX ' + formatBps(rxRate);

        pushChart(time, txRate, rxRate);

    } catch (e) {
        console.error('Session fetch error:', e);
    }
}

function pushChart(time, tx, rx) {
    if (labels.length >= MAX_POINTS) {
        labels.shift();
        txData.shift();
        rxData.shift();
    }
    labels.push(time);
    txData.push(tx);
    rxData.push(rx);
    chart.update();
}

// Start
fetchSession();
setInterval(fetchSession, 5000);
</script>
@endsection
