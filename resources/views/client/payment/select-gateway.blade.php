{{-- resources/views/client/payment/select-gateway.blade.php --}}
@extends('client.layout')
@section('title', 'Pay Invoice')

@section('content')
@php
    $allowPartial  = \App\Models\Setting::get('allow_partial_payment', '0') == '1';
    $payAll        = request('pay_all') == '1';
    $payableAmount = $payAll ? $totalDue : $invoice->due_amount;
@endphp

<div style="max-width:620px; margin:0 auto; padding:20px 0;">

    {{-- Amount Summary --}}
    <div class="card mb-4">
        <div class="card-header" style="background:#1a1f36; color:#fff;">
            <i class="fas fa-file-invoice mr-2"></i>
            @if($payAll)
                All Outstanding Dues
            @else
                Invoice: <strong>{{ $invoice->invoice_no }}</strong>
            @endif
        </div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; text-align:center;">
                @if($payAll)
                <div style="border-right:1px solid #eef0f5; padding:10px;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Total Invoices</div>
                    <div style="font-size:22px; font-weight:700; color:#1a1f36;">
                        {{ $unpaidCount ?? 0 }}
                    </div>
                </div>
                @else
                <div style="border-right:1px solid #eef0f5; padding:10px;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Invoice Amount</div>
                    <div style="font-size:22px; font-weight:700; color:#1a1f36;">
                        Tk{{ number_format($invoice->amount, 2) }}
                    </div>
                </div>
                @endif
                <div style="padding:10px;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Due Amount</div>
                    <div style="font-size:22px; font-weight:700; color:#e74c3c;">
                        Tk{{ number_format($payableAmount, 2) }}
                    </div>
                </div>
            </div>

            {{-- Partial Payment Input --}}
            @if($allowPartial)
            <div style="margin-top:14px; padding-top:14px; border-top:1px solid #eef0f5;">
                <label style="font-size:12px; color:#888; display:block; margin-bottom:6px;">
                    Payment Amount <span style="color:#28a745;">(editable)</span>
                </label>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="font-size:16px; font-weight:700; color:#555;">Tk</span>
                    <input type="number" id="payAmount"
                           value="{{ number_format($payableAmount, 2, '.', '') }}"
                           min="1" max="{{ $payableAmount }}" step="0.01"
                           style="font-size:20px; font-weight:700; color:#e74c3c; border:2px solid #28a745; border-radius:8px; padding:6px 12px; width:180px; outline:none;">
                </div>
                <small style="color:#aaa; font-size:11px; margin-top:4px; display:block;">
                    Max: Tk{{ number_format($payableAmount, 2) }}
                </small>
            </div>
            @endif
        </div>
    </div>

    {{-- Gateway Selection --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-credit-card mr-2" style="color:#1a56db;"></i>
            <strong>Select Payment Method</strong>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div style="background:#fff0f0; border:1px solid #ffd0d0; border-radius:8px; padding:10px 14px; color:#c0392b; font-size:13px; margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            @php
                $gwMeta = [
                    'bkash'      => ['label'=>'bKash',      'color'=>'#E2136E','icon'=>'fas fa-mobile-alt','bg'=>'#fef0f6'],
                    'nagad'      => ['label'=>'Nagad',      'color'=>'#F05A22','icon'=>'fas fa-mobile-alt','bg'=>'#fff4f0'],
                    'sslcommerz' => ['label'=>'SSLCommerz', 'color'=>'#0B6E4F','icon'=>'fas fa-credit-card','bg'=>'#f0faf4'],
                    'amarpayz'   => ['label'=>'AmarPay',    'color'=>'#FF6B00','icon'=>'fas fa-credit-card','bg'=>'#fff7f0'],
                    'stripe'     => ['label'=>'Stripe',     'color'=>'#6772E5','icon'=>'fab fa-stripe-s',  'bg'=>'#f4f4ff'],
                    'paypal'     => ['label'=>'PayPal',     'color'=>'#003087','icon'=>'fab fa-paypal',    'bg'=>'#f0f4ff'],
                    'razorpay'   => ['label'=>'Razorpay',   'color'=>'#072654','icon'=>'fas fa-credit-card','bg'=>'#f0f2f7'],
                    'shurjopay'  => ['label'=>'ShurjoPay',  'color'=>'#E4A11B','icon'=>'fas fa-credit-card','bg'=>'#fffbf0'],
                ];
            @endphp

            <form method="POST" action="{{ route('client.payment.initiate', $invoice->id) }}" id="pgForm">
                @csrf
                <input type="hidden" name="gateway"   id="selectedGateway" value="">
                <input type="hidden" name="pay_all"   value="{{ $payAll ? '1' : '0' }}">
                <input type="hidden" name="amount"    id="finalAmount"      value="{{ $payableAmount }}">

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px; margin-bottom:16px;">
                    @foreach($gateways as $gw)
                    @php $meta = $gwMeta[$gw->slug] ?? ['label'=>$gw->name,'color'=>'#6c757d','icon'=>'fas fa-credit-card','bg'=>'#f8f9fa']; @endphp
                    <div class="gw-card"
                         data-slug="{{ $gw->slug }}"
                         style="background:{{ $meta['bg'] }}; border:2px solid #eef0f5; border-radius:10px; padding:16px; text-align:center; cursor:pointer; transition:all .2s;">
                        <i class="{{ $meta['icon'] }} fa-2x mb-2 d-block" style="color:{{ $meta['color'] }};"></i>
                        <div style="font-weight:700; font-size:13px; color:{{ $meta['color'] }};">{{ $meta['label'] }}</div>
                        <div class="select-badge" style="display:none; margin-top:6px;">
                            <span style="background:#28a745; color:#fff; border-radius:999px; font-size:11px; padding:2px 10px;">
                                <i class="fas fa-check mr-1"></i> Selected
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Selected info box --}}
                <div id="amountBox" style="display:none; background:#f0faf7; border:1px solid #b0e8d0; border-radius:8px; padding:12px 16px; text-align:center; font-size:14px; color:#1a7a50; margin-bottom:16px;">
                    <i class="fas fa-check-circle mr-2"></i>
                    You will pay <strong id="displayAmount">Tk{{ number_format($payableAmount, 2) }}</strong> via
                    <strong id="selectedGwLabel"></strong>
                </div>

                <button type="submit" id="payBtn"
                        style="width:100%; padding:13px; background:#1a56db; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; opacity:.5; pointer-events:none; transition:opacity .2s;">
                    <i class="fas fa-lock mr-2"></i> Pay <span id="payBtnAmount">Tk{{ number_format($payableAmount, 2) }}</span>
                </button>
            </form>

            <div style="text-align:center; font-size:12px; color:#aaa; margin-top:12px;">
                <i class="fas fa-shield-alt mr-1" style="color:#28a745;"></i>
                SSL encrypted secure payment
            </div>
        </div>
    </div>

    <div style="text-align:center; margin-top:14px;">
        <a href="{{ route('client.invoices') }}" style="color:#888; font-size:13px; text-decoration:none;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
        </a>
    </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
    var gwLabels     = @json(collect($gateways)->pluck('name', 'slug'));
    var allowPartial = {{ $allowPartial ? 'true' : 'false' }};
    var baseAmount   = {{ $payableAmount }};

    function getAmount() {
        if (!allowPartial) return baseAmount;
        var inp = document.getElementById('payAmount');
        return inp ? parseFloat(inp.value) || baseAmount : baseAmount;
    }

    function updateAmountDisplay() {
        var amt = getAmount();
        var fmt = 'Tk' + amt.toFixed(2);
        var displayAmt  = document.getElementById('displayAmount');
        var payBtnAmt   = document.getElementById('payBtnAmount');
        var finalAmount = document.getElementById('finalAmount');
        if (displayAmt)  displayAmt.textContent  = fmt;
        if (payBtnAmt)   payBtnAmt.textContent   = fmt;
        if (finalAmount) finalAmount.value        = amt;
    }

    // Partial amount input listener
    var amtInput = document.getElementById('payAmount');
    if (amtInput) {
        amtInput.addEventListener('input', updateAmountDisplay);
    }

    function initGwCards() {
        var cards = document.querySelectorAll('.gw-card');
        if (!cards.length) { setTimeout(initGwCards, 200); return; }

        cards.forEach(function(card) {
            card.addEventListener('click', function() {
                cards.forEach(function(c) {
                    c.style.borderColor = '#eef0f5';
                    c.style.boxShadow   = 'none';
                    var b = c.querySelector('.select-badge');
                    if (b) b.style.display = 'none';
                });
                this.style.borderColor = '#1a56db';
                this.style.boxShadow   = '0 0 0 3px rgba(26,86,219,.15)';
                var badge = this.querySelector('.select-badge');
                if (badge) badge.style.display = 'block';

                var slug  = this.dataset.slug;
                document.getElementById('selectedGateway').value      = slug;
                document.getElementById('selectedGwLabel').textContent = gwLabels[slug] || slug;
                document.getElementById('amountBox').style.display    = 'block';

                updateAmountDisplay();

                var btn = document.getElementById('payBtn');
                if (btn) {
                    btn.style.opacity      = '1';
                    btn.style.pointerEvents = 'auto';
                    btn.disabled           = false;
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initGwCards);
})();
</script>
@endsection

@section('content')
<div style="max-width:620px; margin:0 auto; padding:20px 0;">

    {{-- Invoice Summary --}}
    <div class="card mb-4">
        <div class="card-header" style="background:#1a1f36; color:#fff;">
            <i class="fas fa-file-invoice mr-2"></i>
            Invoice: <strong>{{ $invoice->invoice_no }}</strong>
        </div>
        <div class="card-body">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; text-align:center;">
                <div style="border-right:1px solid #eef0f5; padding:10px;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Invoice Amount</div>
                    <div style="font-size:22px; font-weight:700; color:#1a1f36;">
                        Tk{{ number_format($invoice->amount, 2) }}
                    </div>
                </div>
                <div style="padding:10px;">
                    <div style="font-size:12px; color:#888; margin-bottom:4px;">Due Amount</div>
                    <div style="font-size:22px; font-weight:700; color:#e74c3c;">
                        Tk{{ number_format($invoice->due_amount, 2) }}
                    </div>
                </div>
            </div>
            <div style="text-align:center; font-size:12px; color:#aaa; margin-top:10px; padding-top:10px; border-top:1px solid #eef0f5;">
                Period: {{ $invoice->period_label ?? \Carbon\Carbon::parse($invoice->month ?? now())->format('F Y') }}
                &nbsp;|&nbsp;
                Due: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : 'N/A' }}
            </div>
        </div>
    </div>

    {{-- Gateway Selection --}}
    <div class="card">
        <div class="card-header">
            <i class="fas fa-credit-card mr-2" style="color:#1a56db;"></i>
            <strong>Select Payment Method</strong>
        </div>
        <div class="card-body">

            @if(session('error'))
                <div style="background:#fff0f0; border:1px solid #ffd0d0; border-radius:8px; padding:10px 14px; color:#c0392b; font-size:13px; margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                </div>
            @endif

            @php
                $gwMeta = [
                    'bkash'      => ['label'=>'bKash',      'color'=>'#E2136E','icon'=>'fas fa-mobile-alt','bg'=>'#fef0f6'],
                    'nagad'      => ['label'=>'Nagad',      'color'=>'#F05A22','icon'=>'fas fa-mobile-alt','bg'=>'#fff4f0'],
                    'sslcommerz' => ['label'=>'SSLCommerz', 'color'=>'#0B6E4F','icon'=>'fas fa-credit-card','bg'=>'#f0faf4'],
                    'amarpayz'   => ['label'=>'AmarPay',    'color'=>'#FF6B00','icon'=>'fas fa-credit-card','bg'=>'#fff7f0'],
                    'stripe'     => ['label'=>'Stripe',     'color'=>'#6772E5','icon'=>'fab fa-stripe-s',  'bg'=>'#f4f4ff'],
                    'paypal'     => ['label'=>'PayPal',     'color'=>'#003087','icon'=>'fab fa-paypal',    'bg'=>'#f0f4ff'],
                    'razorpay'   => ['label'=>'Razorpay',   'color'=>'#072654','icon'=>'fas fa-credit-card','bg'=>'#f0f2f7'],
                    'shurjopay'  => ['label'=>'ShurjoPay',  'color'=>'#E4A11B','icon'=>'fas fa-credit-card','bg'=>'#fffbf0'],
                ];
            @endphp

            <form method="POST" action="{{ route('client.payment.initiate', $invoice->id) }}" id="pgForm">
                @csrf
                <input type="hidden" name="gateway" id="selectedGateway" value="">

                <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(160px, 1fr)); gap:12px; margin-bottom:16px;" id="gwCards">
                    @foreach($gateways as $gw)
                    @php $meta = $gwMeta[$gw->slug] ?? ['label'=>$gw->name,'color'=>'#6c757d','icon'=>'fas fa-credit-card','bg'=>'#f8f9fa']; @endphp
                    <div class="gw-card"
                         data-slug="{{ $gw->slug }}"
                         style="background:{{ $meta['bg'] }}; border:2px solid #eef0f5; border-radius:10px; padding:16px; text-align:center; cursor:pointer; transition:all .2s;">
                        <i class="{{ $meta['icon'] }} fa-2x mb-2 d-block" style="color:{{ $meta['color'] }};"></i>
                        <div style="font-weight:700; font-size:13px; color:{{ $meta['color'] }};">{{ $meta['label'] }}</div>
                        <div class="select-badge" style="display:none; margin-top:6px;">
                            <span style="background:#28a745; color:#fff; border-radius:999px; font-size:11px; padding:2px 10px;">
                                <i class="fas fa-check mr-1"></i> Selected
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Selected amount box --}}
                <div id="amountBox" style="display:none; background:#f0faf7; border:1px solid #b0e8d0; border-radius:8px; padding:12px 16px; text-align:center; font-size:14px; color:#1a7a50; margin-bottom:16px;">
                    <i class="fas fa-check-circle mr-2"></i>
                    You will pay <strong>Tk{{ number_format($invoice->due_amount, 2) }}</strong> via
                    <strong id="selectedGwLabel"></strong>
                </div>

                <button type="submit" id="payBtn"
                        style="width:100%; padding:13px; background:#1a56db; color:#fff; border:none; border-radius:8px; font-size:15px; font-weight:600; cursor:pointer; opacity:.5; pointer-events:none; transition:opacity .2s;">
                    <i class="fas fa-lock mr-2"></i> Pay Tk{{ number_format($invoice->due_amount, 2) }}
                </button>
            </form>

            <div style="text-align:center; font-size:12px; color:#aaa; margin-top:12px;">
                <i class="fas fa-shield-alt mr-1" style="color:#28a745;"></i>
                SSL encrypted secure payment
            </div>
        </div>
    </div>

    <div style="text-align:center; margin-top:14px;">
        <a href="{{ route('client.invoices') }}" style="color:#888; font-size:13px; text-decoration:none;">
            <i class="fas fa-arrow-left mr-1"></i> Back to Invoices
        </a>
    </div>
</div>

@endsection

@section('extra_js')
<script>
(function() {
    var gwLabels = @json(collect($gateways)->pluck('name', 'slug'));

    function initGwCards() {
        var cards = document.querySelectorAll('.gw-card');
        if (!cards.length) {
            setTimeout(initGwCards, 200);
            return;
        }

        cards.forEach(function(card) {
            card.addEventListener('click', function() {
                // Reset all
                cards.forEach(function(c) {
                    c.style.borderColor = '#eef0f5';
                    c.style.boxShadow   = 'none';
                    var badge = c.querySelector('.select-badge');
                    if (badge) badge.style.display = 'none';
                });

                // Highlight selected
                this.style.borderColor = '#1a56db';
                this.style.boxShadow   = '0 0 0 3px rgba(26,86,219,.15)';
                var badge = this.querySelector('.select-badge');
                if (badge) badge.style.display = 'block';

                var slug  = this.dataset.slug;
                var label = gwLabels[slug] || slug;

                var gwInput  = document.getElementById('selectedGateway');
                var gwLabel  = document.getElementById('selectedGwLabel');
                var amtBox   = document.getElementById('amountBox');
                var payBtn   = document.getElementById('payBtn');

                if (gwInput)  gwInput.value           = slug;
                if (gwLabel)  gwLabel.textContent      = label;
                if (amtBox)   amtBox.style.display     = 'block';
                if (payBtn) {
                    payBtn.style.opacity       = '1';
                    payBtn.style.pointerEvents = 'auto';
                    payBtn.disabled            = false;
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', initGwCards);
})();
</script>
@endsection
