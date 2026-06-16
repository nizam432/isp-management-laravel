@if(in_array($invoice->status, ['unpaid','partial','overdue']))
    <a href="{{ route('client.payment.select', $invoice->id) }}"
       class="btn btn-success btn-sm">
        <i class="fas fa-credit-card mr-1"></i> Pay Now
    </a>
@elseif($invoice->status === 'paid')
    <span class="badge badge-success" style="padding:6px 12px;">
        <i class="fas fa-check mr-1"></i> Paid
    </span>
@endif
