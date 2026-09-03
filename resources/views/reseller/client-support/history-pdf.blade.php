<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #eee; }
        h3 { margin-bottom: 4px; }
    </style>
</head>
<body>
    <h3>Support History Report</h3>
    <p>Generated on {{ now()->format('d M Y h:i A') }}</p>
    <table>
        <thead>
            <tr>
                <th>Sr.No</th><th>Date</th><th>Ticket No</th><th>Client Code</th><th>Username</th>
                <th>Mobile No</th><th>Zone</th><th>Category</th><th>Priority</th><th>Solve Time</th><th>Duration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tickets as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->created_at->format('d-m-Y') }}</td>
                <td>{{ $t->ticket_no }}</td>
                <td>{{ $t->customer->customer_code ?? '—' }}</td>
                <td>{{ $t->customer->pppoe_username ?? '—' }}</td>
                <td>{{ $t->customer->phone ?? '—' }}</td>
                <td>{{ $t->customer->resellerZone->name ?? '—' }}</td>
                <td>{{ $t->category->name ?? '—' }}</td>
                <td>{{ ucfirst($t->priority) }}</td>
                <td>{{ $t->solved_at?->format('d-m-Y H:i') }}</td>
                <td>{{ $t->duration }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>