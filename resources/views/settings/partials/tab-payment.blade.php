{{--
    resources/views/settings/partials/tab-payment.blade.php
    Include করুন settings/general.blade.php এর #tab-payment div এ:
        @include('settings.partials.tab-payment')
    পুরনো JS gatewayConfigs block মুছে দিন।
--}}
@php
    use App\Models\PaymentGateway;
    use App\Models\PaymentGatewaySetting;
    use App\Services\PaymentGatewayService;

    $tenantId    = PaymentGatewayService::tenantId();
    $allGateways = PaymentGateway::enabled()->orderBy('type')->orderBy('id')->get();
    $gwSettings  = PaymentGatewaySetting::where('tenant_id', $tenantId)
                       ->get()->keyBy('gateway_slug');

    $gwMeta = [
        // ── Local ────────────────────────────────────────────────
        'bkash' => [
            'color' => '#E2136E', 'icon' => 'fas fa-mobile-alt', 'type' => 'local',
            'fields' => [
                ['key' => 'app_key',    'label' => 'App Key',    'type' => 'text'],
                ['key' => 'app_secret', 'label' => 'App Secret', 'type' => 'password'],
                ['key' => 'username',   'label' => 'Username',   'type' => 'text'],
                ['key' => 'password',   'label' => 'Password',   'type' => 'password'],
            ],
            'help' => 'bKash Merchant Account → Checkout URL API credentials. <a href="https://developer.bka.sh" target="_blank">developer.bka.sh <i class="fas fa-external-link-alt fa-xs"></i></a>',
        ],
        'nagad' => [
            'color' => '#F05A22', 'icon' => 'fas fa-mobile-alt', 'type' => 'local',
            'fields' => [
                ['key' => 'merchant_id',     'label' => 'Merchant ID',          'type' => 'text'],
                ['key' => 'merchant_number', 'label' => 'Merchant Number',      'type' => 'text'],
                ['key' => 'public_key',      'label' => "Nagad's Public Key",   'type' => 'textarea'],
                ['key' => 'private_key',     'label' => 'Merchant Private Key', 'type' => 'textarea'],
            ],
            'help' => 'Collect API keys from the Nagad Merchant Portal.',
        ],
        'sslcommerz' => [
            'color' => '#0B6E4F', 'icon' => 'fas fa-credit-card', 'type' => 'local',
            'fields' => [
                ['key' => 'store_id',     'label' => 'Store ID',       'type' => 'text'],
                ['key' => 'store_passwd', 'label' => 'Store Password', 'type' => 'password'],
            ],
            'help' => '<a href="https://manage.sslcommerz.com" target="_blank">manage.sslcommerz.com <i class="fas fa-external-link-alt fa-xs"></i></a> — Get your Store ID and Password.',
        ],
        'amarpayz' => [
            'color' => '#FF6B00', 'icon' => 'fas fa-credit-card', 'type' => 'local',
            'fields' => [
                ['key' => 'app_id',  'label' => 'Store ID / App ID', 'type' => 'text'],
                ['key' => 'app_key', 'label' => 'Signature Key',     'type' => 'password'],
            ],
            'help' => '<a href="https://aamarpay.com" target="_blank">aamarpay.com <i class="fas fa-external-link-alt fa-xs"></i></a> — Get credentials from the merchant portal.',
        ],
        // ── International ─────────────────────────────────────────
        'stripe' => [
            'color' => '#6772E5', 'icon' => 'fab fa-stripe-s', 'type' => 'international',
            'fields' => [
                ['key' => 'publishable_key', 'label' => 'Publishable Key', 'type' => 'text'],
                ['key' => 'secret_key',      'label' => 'Secret Key',      'type' => 'password'],
                ['key' => 'webhook_secret',  'label' => 'Webhook Secret',  'type' => 'password'],
            ],
            'help' => '<a href="https://dashboard.stripe.com/apikeys" target="_blank">Stripe Dashboard → Developers → API Keys <i class="fas fa-external-link-alt fa-xs"></i></a>. Sandbox: <code>sk_test_...</code> | Live: <code>sk_live_...</code>',
        ],
        'paypal' => [
            'color' => '#003087', 'icon' => 'fab fa-paypal', 'type' => 'international',
            'fields' => [
                ['key' => 'client_id',     'label' => 'Client ID',     'type' => 'text'],
                ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password'],
            ],
            'help' => '<a href="https://developer.paypal.com/dashboard" target="_blank">PayPal Developer Dashboard <i class="fas fa-external-link-alt fa-xs"></i></a> → My Apps & Credentials.',
        ],
        'razorpay' => [
            'color' => '#072654', 'icon' => 'fas fa-credit-card', 'type' => 'international',
            'fields' => [
                ['key' => 'key_id',     'label' => 'Key ID',     'type' => 'text'],
                ['key' => 'key_secret', 'label' => 'Key Secret', 'type' => 'password'],
            ],
            'help' => '<a href="https://dashboard.razorpay.com/app/keys" target="_blank">Razorpay Dashboard → Settings → API Keys <i class="fas fa-external-link-alt fa-xs"></i></a>. Test: <code>rzp_test_...</code> | Live: <code>rzp_live_...</code>',
        ],
    ];
@endphp

<div class="row">

    {{-- ── Left: Gateway List ──────────────────────────────────── --}}
    <div class="col-md-4">
        <div class="list-group" id="pgTabList">

            {{-- LOCAL --}}
            <div class="list-group-item list-group-item-dark py-1 px-3">
                <small class="font-weight-bold"><i class="fas fa-map-marker-alt mr-1"></i> LOCAL</small>
            </div>
            @foreach($allGateways->where('type','local') as $gw)
                @include('settings.partials._pg-tab-item', ['gw'=>$gw,'gwMeta'=>$gwMeta,'gwSettings'=>$gwSettings])
            @endforeach

            {{-- INTERNATIONAL --}}
            <div class="list-group-item list-group-item-dark py-1 px-3 mt-1">
                <small class="font-weight-bold"><i class="fas fa-globe mr-1"></i> INTERNATIONAL</small>
            </div>
            @foreach($allGateways->where('type','international') as $gw)
                @include('settings.partials._pg-tab-item', ['gw'=>$gw,'gwMeta'=>$gwMeta,'gwSettings'=>$gwSettings])
            @endforeach

        </div>
    </div>

    {{-- ── Right: Config Panel ─────────────────────────────────── --}}
    <div class="col-md-8">
        <div id="pgConfigPanel">
            <div class="text-center text-muted py-5">
                <i class="fas fa-credit-card fa-3x mb-3 d-block" style="opacity:.25"></i>
                <p>Select a gateway from the left to configure</p>
            </div>
        </div>
    </div>

</div>

@push('js')
<script>
if (typeof PG_META === 'undefined') {
    var PG_META     = @json($gwMeta);
    var GW_SETTINGS = @json($gwSettings);
    var CSRF        = '{{ csrf_token() }}';
}

document.querySelectorAll('.pg-tab-link').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelectorAll('.pg-tab-link').forEach(l => l.classList.remove('active'));
        this.classList.add('active');

        const slug = this.dataset.slug;
        renderPgConfig(slug);
    });
});

function renderPgConfig(slug) {
    const meta    = PG_META[slug]      || { color:'#6c757d', fields:[], help:'' };
    const saved   = GW_SETTINGS[slug] || {};
    const config  = saved.config      || {};
    const active  = saved.is_active == true || saved.is_active === 1 || saved.is_active === '1';
    const sandbox = saved.sandbox !== undefined ? (saved.sandbox == true || saved.sandbox === 1) : true;

    const saveUrl   = `/settings/payment-gateways/${slug}/save`;
    const toggleUrl = `/settings/payment-gateways/${slug}/toggle`;

    const fieldsHtml = meta.fields.map(f => {
        const savedVal = config[f.key] || '';
        const display  = (f.type === 'password' && savedVal) ? '' : esc(savedVal);
        const ph       = (f.type === 'password' && savedVal) ? '••••••• (saved — leave blank to keep)' : `Enter ${f.label}`;

        if (f.type === 'textarea') {
            return `<div class="form-group mb-2">
                <label class="font-weight-bold small mb-1">${f.label}</label>
                <textarea name="config[${f.key}]" class="form-control form-control-sm"
                    rows="4" placeholder="Paste ${f.label} here">${esc(savedVal)}</textarea>
            </div>`;
        }
        return `<div class="form-group mb-2">
            <label class="font-weight-bold small mb-1">${f.label}</label>
            <input type="${f.type}" name="config[${f.key}]"
                class="form-control form-control-sm"
                value="${display}" placeholder="${ph}">
        </div>`;
    }).join('');

    const helpHtml = meta.help
        ? `<div class="alert alert-light border small py-2 mt-1 mb-3">
               <i class="fas fa-info-circle mr-1 text-info"></i> ${meta.help}
           </div>` : '';

    document.getElementById('pgConfigPanel').innerHTML = `
    <div class="card shadow-sm mb-0" style="border-top:3px solid ${meta.color}">
        <div class="card-header py-2 d-flex align-items-center justify-content-between" style="background:#fafafa">
            <span class="font-weight-bold" style="color:${meta.color}">
                <i class="${meta.icon || 'fas fa-credit-card'} mr-2"></i>${slug.toUpperCase()} Configuration
            </span>
        </div>

        <div class="card-body pb-2">
            {{-- Sandbox Toggle --}}
            <div class="form-group mb-3">
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="sb_${slug}"
                           onchange="pgSandboxChange('${slug}', this.checked)"
                           ${sandbox ? 'checked' : ''}>
                    <label class="custom-control-label" for="sb_${slug}">
                        <span id="sbLabel_${slug}"
                              class="${sandbox ? 'text-warning' : 'text-success'} font-weight-bold">
                            ${sandbox ? '⚠ Sandbox / Test Mode' : '✅ Live / Production Mode'}
                        </span>
                        <small class="text-muted d-block">Disable Sandbox to accept live payments.</small>
                    </label>
                </div>
            </div>
            <hr class="mt-0 mb-3">

            {{-- Credentials form --}}
            <div id="pgCredForm_${slug}">
                <input type="hidden" id="pgSandbox_${slug}" value="${sandbox ? '1' : '0'}">

                ${fieldsHtml}
                ${helpHtml}

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <button type="button" class="btn btn-primary btn-sm px-4"
                            onclick="pgSaveCredentials('${slug}')">
                        <i class="fas fa-save mr-1"></i> Save Credentials
                    </button>

                    <button type="button"
                            class="btn btn-sm ${active ? 'btn-outline-danger' : 'btn-outline-success'}"
                            onclick="pgToggleGateway('${slug}', ${active})">
                        ${active
                            ? '<i class="fas fa-toggle-off mr-1"></i> Disable'
                            : '<i class="fas fa-toggle-on mr-1"></i> Enable for Customers'}
                    </button>
                </div>
            </div>
        </div>

        <div class="card-footer py-2 text-muted small d-flex justify-content-between">
            <span><i class="fas fa-shield-alt mr-1 text-success"></i> Credentials stored securely.</span>
            <span id="modeTag_${slug}">
                ${sandbox
                    ? '<span class="badge badge-warning">SANDBOX</span>'
                    : '<span class="badge badge-success">LIVE</span>'}
            </span>
        </div>
    </div>`;
}

function pgSandboxChange(slug, checked) {
    var label   = document.getElementById('sbLabel_' + slug);
    var hidden  = document.getElementById('pgSandbox_' + slug);
    var modeTag = document.getElementById('modeTag_' + slug);
    if (label) {
        label.className   = (checked ? 'text-warning' : 'text-success') + ' font-weight-bold';
        label.textContent = checked ? '⚠ Sandbox / Test Mode' : '✅ Live / Production Mode';
    }
    if (hidden)  hidden.value    = checked ? '1' : '0';
    if (modeTag) modeTag.innerHTML = checked
        ? '<span class="badge badge-warning">SANDBOX</span>'
        : '<span class="badge badge-success">LIVE</span>';
}

function pgSaveCredentials(slug) {
    var fd = new FormData();
    fd.append('_token', CSRF);

    // sandbox value
    var sbHidden = document.getElementById('pgSandbox_' + slug);
    fd.append('sandbox', sbHidden ? sbHidden.value : '1');

    // is_active — keep current value
    var isActive = (GW_SETTINGS[slug] && GW_SETTINGS[slug].is_active) ? '1' : '0';
    fd.append('is_active', isActive);

    // collect all config[key] inputs inside pgCredForm
    var container = document.getElementById('pgCredForm_' + slug);
    if (container) {
        container.querySelectorAll('input[name^="config["], textarea[name^="config["]').forEach(function(el) {
            if (el.value.trim() !== '') {
                fd.append(el.name, el.value);
            } else if (el.type !== 'password') {
                fd.append(el.name, el.value);
            }
            // skip empty password fields — means "keep existing"
        });
    }

    fetch('/settings/payment-gateways/' + slug + '/save', {
        method: 'POST',
        body: fd,
    })
    .then(res => {
        if (res.ok || res.redirected) {
            // Show success
            var btn = container ? container.querySelector('.btn-primary') : null;
            if (btn) {
                var orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-1"></i> Saved!';
                btn.classList.remove('btn-primary');
                btn.classList.add('btn-success');
                setTimeout(function() {
                    btn.innerHTML = orig;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-primary');
                }, 2000);
            }
            // Update local GW_SETTINGS config
            if (!GW_SETTINGS[slug]) GW_SETTINGS[slug] = {};
        } else {
            alert('Save failed. Please try again.');
        }
    })
    .catch(function() { alert('Network error. Please try again.'); });
}

function pgToggleGateway(slug, currentlyActive) {
    var fd = new FormData();
    fd.append('_token', CSRF);

    fetch(`/settings/payment-gateways/${slug}/toggle`, {
        method: 'POST',
        body: fd,
    })
    .then(res => {
        if (res.ok) {
            if (!GW_SETTINGS[slug]) GW_SETTINGS[slug] = {};
            GW_SETTINGS[slug].is_active = !currentlyActive;
            renderPgConfig(slug);
        } else {
            res.text().then(t => alert('Toggle failed: ' + t));
        }
    })
    .catch(() => alert('Network error. Please try again.'));
}

function esc(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
</script>
@endpush
