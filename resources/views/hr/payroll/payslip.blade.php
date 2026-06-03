{{-- resources/views/hr/payroll/payslip.blade.php --}}
@php
    use App\Models\Setting;
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payslip — {{ $payroll->employee->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; margin: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 15px; }
        .header h2 { margin: 0; }
        .info-table { width: 100%; margin-bottom: 15px; }
        .info-table td { padding: 4px 8px; }
        .salary-table { width: 100%; border-collapse: collapse; }
        .salary-table th, .salary-table td { border: 1px solid #ddd; padding: 6px 10px; }
        .salary-table thead { background: #333; color: #fff; }
        .total-row { font-weight: bold; background: #f5f5f5; }
        .net-row { font-weight: bold; background: #333; color: #fff; font-size: 15px; }
        .footer { margin-top: 30px; text-align: center; font-size: 11px; color: #666; }
        @media print { button { display: none; } }
    </style>
</head>
<body>

<div class="header">
    <h2>{{ \App\Models\Setting::get('company_name', 'My ISP') }}</h2>
    <p>{{ \App\Models\Setting::get('company_address') }}</p>
    <h3>PAYSLIP — {{ \Carbon\Carbon::parse($payroll->month . '-01')->format('F Y') }}</h3>
</div>

<table class="info-table">
    <tr>
        <td><strong>Employee Name:</strong> {{ $payroll->employee->name }}</td>
        <td><strong>Employee ID:</strong> {{ $payroll->employee->employee_code }}</td>
    </tr>
    <tr>
        <td><strong>Department:</strong> {{ $payroll->employee->department->name ?? '—' }}</td>
        <td><strong>Position:</strong> {{ $payroll->employee->position->name ?? '—' }}</td>
    </tr>
    <tr>
        <td><strong>Payment Date:</strong> {{ $payroll->payment_date ? $payroll->payment_date->format('d M Y') : '—' }}</td>
        <td><strong>Payment Method:</strong> {{ ucfirst($payroll->payment_method) }}</td>
    </tr>
</table>

<table class="salary-table">
    <thead>
        <tr>
            <th>Earnings</th>
            <th>Amount (৳)</th>
            <th>Deductions</th>
            <th>Amount (৳)</th>
        </tr>
    </thead>
    <tbody>
        @php
            $additions  = $payroll->details->filter(fn($d) => $d->salaryHead->type === 'addition');
            $deductions = $payroll->details->filter(fn($d) => $d->salaryHead->type === 'deduction');
            $maxRows    = max($additions->count(), $deductions->count());
            $addArr     = $additions->values();
            $dedArr     = $deductions->values();
        @endphp
        @for($i = 0; $i < $maxRows; $i++)
        <tr>
            <td>{{ $addArr[$i]->salaryHead->name ?? '' }}</td>
            <td>{{ isset($addArr[$i]) ? number_format($addArr[$i]->amount) : '' }}</td>
            <td>{{ $dedArr[$i]->salaryHead->name ?? '' }}</td>
            <td>{{ isset($dedArr[$i]) ? number_format($dedArr[$i]->amount) : '' }}</td>
        </tr>
        @endfor
        <tr class="total-row">
            <td>Gross Salary</td>
            <td>{{ number_format($payroll->gross_salary) }}</td>
            <td>Total Deduction</td>
            <td>{{ number_format($payroll->total_deduction) }}</td>
        </tr>
        <tr class="net-row">
            <td colspan="3">NET SALARY</td>
            <td>৳ {{ number_format($payroll->net_salary) }}</td>
        </tr>
    </tbody>
</table>

<div style="margin-top:30px; display:flex; justify-content:space-between;">
    <div style="text-align:center;">
        <div style="border-top:1px solid #333; width:150px; padding-top:5px;">
            Employee Signature
        </div>
    </div>
    <div style="text-align:center;">
        <div style="border-top:1px solid #333; width:150px; padding-top:5px;">
            Authorized Signature
        </div>
    </div>
</div>

<div class="footer">
    <p>{{ \App\Models\Setting::get('invoice_footer_text', 'Thank you.') }}</p>
</div>

<div style="text-align:center; margin-top:20px;">
    <button onclick="window.print()" style="padding:8px 20px; cursor:pointer;">
        Print Payslip
    </button>
</div>

</body>
</html>
