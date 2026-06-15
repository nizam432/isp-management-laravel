{{-- resources/views/client_support/_row.blade.php --}}
<tr id="ticket-row-{{ $t->id }}">
    <td><small>{{ $t->ticket_no }}</small></td>
    <td><small>{{ $t->customer->customer_code ?? '—' }}</small></td>
    <td><small>{{ $t->customer->pppoe_username ?? '—' }}</small></td>
    <td><small>{{ $t->customer->name ?? '—' }}</small></td>
    <td><small>{{ $t->customer->phone ?? '—' }}</small></td>
    <td><small>{{ $t->complained_no }}</small></td>
    <td><small>{{ $t->customer->zone->name ?? '—' }}</small></td>
    <td><small>{{ $t->customer->subZone->name ?? '—' }}</small></td>
    <td><small>{{ $t->category->name ?? '—' }}</small></td>
    <td>
        <span class="badge badge-{{ $t->priority_badge }}">{{ ucfirst($t->priority) }}</span>
    </td>
    <td><small>{{ $t->created_at->format('d M Y H:i A') }}</small></td>
    <td><small>{{ $t->createdBy->name ?? '—' }}</small></td>
    <td class="status-badge">
        @if($t->status === 'processing')
            <button class="btn btn-xs btn-warning btn-ticket-solve"
                    data-id="{{ $t->id }}"
                    data-mac="{{ $t->customer->mac_address }}"
                    data-ip="{{ $t->customer->ip_address }}">
                Processing
            </button>
        @else
            <span class="badge badge-{{ $t->status_badge }}">{{ ucfirst($t->status) }}</span>
        @endif
    </td>
    <td class="assign-cell">
        <small>{{ $t->assignees->pluck('name')->implode(', ') ?: '—' }}</small>
    </td>
    <td class="duration-cell">
        @if($t->solved_at)
            <small class="text-muted">Duration<br>{{ $t->duration }}</small>
        @endif
        <div class="mt-1">
            <button class="btn btn-xs btn-warning ticket-action-btn btn-ticket-reassign" data-id="{{ $t->id }}">Re Assign</button><br>
            <div class="mt-1">
                <button class="btn btn-xs btn-success btn-ticket-solve"
                        data-id="{{ $t->id }}"
                        data-mac="{{ $t->customer->mac_address }}"
                        data-ip="{{ $t->customer->ip_address }}"
                        title="Solve"><i class="fas fa-check"></i></button>
                <a href="{{ route('client-support.chat', $t->id) }}" class="btn btn-xs btn-primary" title="Chat">
                    <i class="fas fa-comments"></i>
                </a>
                <button class="btn btn-xs btn-info btn-ticket-edit" data-id="{{ $t->id }}" title="Edit"><i class="fas fa-edit"></i></button>
                <button class="btn btn-xs btn-danger btn-ticket-delete" data-id="{{ $t->id }}" title="Delete"><i class="fas fa-trash"></i></button>
            </div>
        </div>
    </td>
</tr>
