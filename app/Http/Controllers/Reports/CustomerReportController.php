<?php
// ════════════════════════════════════════════
// app/Http/Controllers/Reports/CustomerReportController.php
//
//   1. Customer Report
//   2. POP Wise Clients Report
// ════════════════════════════════════════════

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MacReseller;
use App\Models\Package;
use App\Models\Zone;
use App\Models\ClientType;
use App\Models\ProtocolType;
use App\Models\MikrotikRouter;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CustomerReportController extends Controller
{
    // ══════════════════════════════════════════════════════
    // 1. CUSTOMER REPORT
    // ══════════════════════════════════════════════════════

    public function customerReport(Request $request)
    {
        $query = $this->buildCustomerQuery($request);

        $perPage   = (int) $request->get('show', 25);
        $customers = $query->paginate($perPage)->withQueryString();

        $grandTotal = [
            'total'    => $this->buildCustomerQuery($request)->count(),
            'active'   => $this->buildCustomerQuery($request)->where('status', 'active')->count(),
            'inactive' => $this->buildCustomerQuery($request)->whereIn('status', ['inactive','suspended','expired'])->count(),
        ];

        $packages      = Package::orderBy('name')->get();
        $zones         = Zone::orderBy('name')->get();
        $clientTypes   = ClientType::orderBy('name')->get();
        $protocolTypes = ProtocolType::orderBy('name')->get();
        $routers       = MikrotikRouter::orderBy('name')->get();
        $resellers     = MacReseller::orderBy('business_name')->get();

        return view('reports.bill.customer-report', compact(
            'customers', 'grandTotal', 'packages', 'zones',
            'clientTypes', 'protocolTypes', 'routers', 'resellers', 'perPage'
        ));
    }

    public function exportCustomerPdf(Request $request)
    {
        $customers  = $this->buildCustomerQuery($request)->get();
        $grandTotal = ['total' => $customers->count()];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'reports.bill.customer-report-pdf',
            compact('customers', 'grandTotal')
        )->setPaper('a4', 'landscape');

        return $pdf->download('customer-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCustomerXlsx(Request $request)
    {
        $customers = $this->buildCustomerQuery($request)->get();
        $filename  = 'customer-report-' . now()->format('Y-m-d') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Customer Report');

        $headers = [
            'Client Code', 'Username', 'Customer Name', 'Contact Number',
            'Client Type', 'Package', 'Server', 'Protocol',
            'Monthly Bill', 'B.Status', 'POP Name', 'M.Status',
        ];
        $sheet->fromArray($headers, null, 'A1');

        $headerStyle = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

        $row = 2;
        foreach ($customers as $cust) {
            $sheet->fromArray([
                $cust->customer_code,
                $cust->pppoe_username ?? '-',
                $cust->name,
                $cust->phone ?? '-',
                $cust->clientType->name ?? '-',
                $cust->package->name ?? '-',
                $cust->router->name ?? '-',
                $cust->protocolType->name ?? '-',
                $cust->monthly_bill_amount ?? 0,
                ucfirst($cust->billing_status ?? $cust->status),
                $cust->macReseller->business_name ?? '-',
                ucfirst($cust->mikrotik_status ?? '-'),
            ], null, 'A' . $row);
            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    private function buildCustomerQuery(Request $request)
    {
        $query = Customer::query()->with([
            'package', 'clientType', 'protocolType', 'router', 'macReseller',
        ]);

        if ($search = $request->get('search'))
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('customer_code', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('pppoe_username', 'like', "%{$search}%"));

        if ($packageId = $request->get('package_id'))
            $query->where('package_id', $packageId);
        if ($zoneId = $request->get('zone_id'))
            $query->where('zone_id', $zoneId);
        if ($clientTypeId = $request->get('client_type_id'))
            $query->where('client_type_id', $clientTypeId);
        if ($protocolTypeId = $request->get('protocol_type_id'))
            $query->where('protocol_type_id', $protocolTypeId);
        if ($routerId = $request->get('router_id'))
            $query->where('router_id', $routerId);
        if ($resellerId = $request->get('mac_reseller_id'))
            $query->where('mac_reseller_id', $resellerId);
        if ($billingStatus = $request->get('billing_status'))
            $query->where('billing_status', $billingStatus);
        if ($status = $request->get('status'))
            $query->where('status', $status);
        if ($from = $request->get('from_date'))
            $query->whereDate('connection_date', '>=', Carbon::parse($from));
        if ($to = $request->get('to_date'))
            $query->whereDate('connection_date', '<=', Carbon::parse($to));

        return $query->orderBy('customer_code');
    }

    // ══════════════════════════════════════════════════════
    // 2. POP WISE CLIENTS REPORT
    // ══════════════════════════════════════════════════════

    public function popWiseClients(Request $request)
    {
        $resellers = MacReseller::orderBy('business_name')->get();

        $rows = $resellers->map(function ($reseller) {
            $base = Customer::where('mac_reseller_id', $reseller->id);

            return [
                'reseller' => $reseller->business_name ?? $reseller->code,
                'total'    => (clone $base)->count(),
                'active'   => (clone $base)->where('status', 'active')->count(),
                'expired'  => (clone $base)->where('status', 'expired')->count(),
                'left'     => (clone $base)->where('status', 'left')->count(),
                'pending'  => (clone $base)->where('status', 'inactive')->count(),
                'pppoe'    => (clone $base)->whereHas('protocolType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%pppoe%']))->count(),
                'hotspot'  => (clone $base)->whereHas('protocolType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%hotspot%']))->count(),
                'free'     => (clone $base)->whereHas('clientType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%free%']))->count(),
                'vip'      => (clone $base)->whereHas('clientType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%vip%']))->count(),
            ];
        });

        $totals = [
            'total'   => $rows->sum('total'),
            'active'  => $rows->sum('active'),
            'expired' => $rows->sum('expired'),
            'left'    => $rows->sum('left'),
            'pending' => $rows->sum('pending'),
            'pppoe'   => $rows->sum('pppoe'),
            'hotspot' => $rows->sum('hotspot'),
            'free'    => $rows->sum('free'),
            'vip'     => $rows->sum('vip'),
        ];

        return view('reports.bill.pop-wise-clients', compact('rows', 'totals'));
    }

    public function exportPopWisePdf(Request $request)
    {
        $resellers = MacReseller::orderBy('business_name')->get();
        $rows      = $resellers->map(function ($reseller) {
            $base = Customer::where('mac_reseller_id', $reseller->id);
            return [
                'reseller' => $reseller->business_name ?? $reseller->code,
                'total'    => (clone $base)->count(),
                'active'   => (clone $base)->where('status', 'active')->count(),
                'expired'  => (clone $base)->where('status', 'expired')->count(),
                'left'     => (clone $base)->where('status', 'left')->count(),
                'pending'  => (clone $base)->where('status', 'inactive')->count(),
                'pppoe'    => (clone $base)->whereHas('protocolType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%pppoe%']))->count(),
                'hotspot'  => (clone $base)->whereHas('protocolType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%hotspot%']))->count(),
                'free'     => (clone $base)->whereHas('clientType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%free%']))->count(),
                'vip'      => (clone $base)->whereHas('clientType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%vip%']))->count(),
            ];
        });
        $totals = [
            'total'=>$rows->sum('total'),'active'=>$rows->sum('active'),'expired'=>$rows->sum('expired'),
            'left'=>$rows->sum('left'),'pending'=>$rows->sum('pending'),'pppoe'=>$rows->sum('pppoe'),
            'hotspot'=>$rows->sum('hotspot'),'free'=>$rows->sum('free'),'vip'=>$rows->sum('vip'),
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('reports.bill.pop-wise-clients-pdf', compact('rows', 'totals'))
                  ->setPaper('a4', 'landscape');
        return $pdf->download('pop-wise-clients-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportPopWiseXlsx(Request $request)
    {
        $resellers = MacReseller::orderBy('business_name')->get();
        $rows      = $resellers->map(function ($reseller) {
            $base = Customer::where('mac_reseller_id', $reseller->id);
            return [
                'reseller' => $reseller->business_name ?? $reseller->code,
                'total'    => (clone $base)->count(),
                'active'   => (clone $base)->where('status', 'active')->count(),
                'expired'  => (clone $base)->where('status', 'expired')->count(),
                'left'     => (clone $base)->where('status', 'left')->count(),
                'pending'  => (clone $base)->where('status', 'inactive')->count(),
                'pppoe'    => (clone $base)->whereHas('protocolType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%pppoe%']))->count(),
                'hotspot'  => (clone $base)->whereHas('protocolType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%hotspot%']))->count(),
                'free'     => (clone $base)->whereHas('clientType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%free%']))->count(),
                'vip'      => (clone $base)->whereHas('clientType', fn($q) => $q->whereRaw('LOWER(name) LIKE ?', ['%vip%']))->count(),
            ];
        });

        $filename    = 'pop-wise-clients-' . now()->format('Y-m-d') . '.xlsx';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('POP Wise Clients');

        $headers = ['SL', 'Reseller', 'Total', 'Active', 'Expired', 'Left', 'Pending', 'PPPOE', 'Hotspot', 'Free', 'VIP'];
        $sheet->fromArray($headers, null, 'A1');
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1F3864']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 2;
        foreach ($rows as $i => $r) {
            $sheet->fromArray([$i+1, $r['reseller'], $r['total'], $r['active'], $r['expired'], $r['left'], $r['pending'], $r['pppoe'], $r['hotspot'], $r['free'], $r['vip']], null, 'A' . $row);
            $row++;
        }
        $sheet->fromArray(['', 'TOTAL', $rows->sum('total'), $rows->sum('active'), $rows->sum('expired'), $rows->sum('left'), $rows->sum('pending'), $rows->sum('pppoe'), $rows->sum('hotspot'), $rows->sum('free'), $rows->sum('vip')], null, 'A' . $row);
        $sheet->getStyle('A' . $row . ':K' . $row)->applyFromArray(['font' => ['bold' => true], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFDCE6F1']]]);

        foreach (range('A', 'K') as $col) { $sheet->getColumnDimension($col)->setAutoSize(true); }

        $writer   = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
