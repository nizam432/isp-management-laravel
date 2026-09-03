<?php

namespace App\Http\Controllers\Reseller;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\MacResellerZone;
use App\Models\MacResellerSubZone;
use App\Models\MacResellerTariffPackage;
use App\Services\MikrotikService;
use App\Models\ProtocolType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ResellerBulkImportController extends Controller
{
    public function index()
    {
        return view('reseller.mikrotik-client.bulk-import');
    }

    /**
     * Expected header row (case-insensitive, order doesn't matter):
     * name, mobile, email, nid, address, zone, sub_zone, package,
     * protocol_type, pppoe_username, pppoe_password, ip_address,
     * status, monthly_bill, join_date
     *
     * Zone / Sub Zone / Package are matched BY NAME against THIS reseller's
     * own tables (MacResellerZone / MacResellerSubZone / MacResellerTariffPackage).
     * Protocol Type is matched against the shared global ProtocolType table.
     */
    public function preview(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $rows = $this->parseSpreadsheet($request->file('excel_file'));

        foreach ($rows as &$row) {
            $row['phone'] = $row['mobile'] ?? $row['phone'] ?? null;
        }
        unset($row);

        $existingUsernames = Customer::forReseller($resellerId)->pluck('pppoe_username')->toArray();
        $existingPhones    = Customer::forReseller($resellerId)->pluck('phone')->toArray();

        $zones     = MacResellerZone::forReseller($resellerId)->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);
        $subZones  = MacResellerSubZone::forReseller($resellerId)->pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);
        $protocols = ProtocolType::pluck('id', 'name')
            ->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);

        // Package name resolves through: this reseller's assigned Tariff
        // (MacReseller.tariff_id) -> that Tariff's package lines
        // (MacResellerTariffPackage) -> the underlying MacResellerPackage name.
        $reseller = Auth::guard('mac_reseller')->user();

        $tariffPackages = collect();
        if ($reseller->tariff_id) {
            $tariffPackages = MacResellerTariffPackage::where('tariff_id', $reseller->tariff_id)
                ->with('package')
                ->get()
                ->filter(fn($tp) => $tp->package)
                ->keyBy(fn($tp) => strtolower(trim($tp->package->name)));
        }

        foreach ($rows as &$row) {
            $row['_zone_id']     = $row['zone'] ? ($zones[strtolower(trim($row['zone']))] ?? null) : null;
            $row['_sub_zone_id'] = $row['sub_zone'] ? ($subZones[strtolower(trim($row['sub_zone']))] ?? null) : null;
            $row['_package_id']  = $row['package'] ? ($tariffPackages[strtolower(trim($row['package']))]->id ?? null) : null;
            $row['_protocol_id'] = $row['protocol_type'] ? ($protocols[strtolower(trim($row['protocol_type']))] ?? null) : null;

            $row['_zone_ok']     = !$row['zone']         || $row['_zone_id'];
            $row['_package_ok']  = !$row['package']       || $row['_package_id'];
            $row['_protocol_ok'] = !$row['protocol_type'] || $row['_protocol_id'];

            $row['_exists_username'] = in_array($row['pppoe_username'] ?? '', $existingUsernames);
            $row['_exists_phone']    = !empty($row['phone']) && in_array($row['phone'], $existingPhones);

            $row['_will_import'] = !$row['_exists_username'] && !$row['_exists_phone']
                && $row['_zone_ok'] && $row['_package_ok'] && $row['_protocol_ok']
                && !empty($row['pppoe_username']) && !empty($row['pppoe_password']);
        }

        return view('reseller.mikrotik-client.bulk-import-preview', ['rows' => $rows]);
    }

    public function import(Request $request)
    {
        $resellerId = Auth::guard('mac_reseller')->id();

        $request->validate(['rows' => 'required|array']);

        $imported   = 0;
        $skipped    = 0;
        $provisioned = 0;

        foreach ($request->rows as $index => $row) {
            try {
                if (Customer::forReseller($resellerId)->where('pppoe_username', $row['pppoe_username'] ?? '')->exists()) {
                    $skipped++;
                    continue;
                }
                if (!empty($row['phone']) && Customer::forReseller($resellerId)->where('phone', $row['phone'])->exists()) {
                    $skipped++;
                    continue;
                }

                $client = Customer::create([
                    'mac_reseller_id'                => $resellerId,
                    'customer_code'                   => $this->generateUniqueCode(),
                    'name'                            => $row['name'] ?? ('User-' . ($row['pppoe_username'] ?? $index)),
                    'phone'                           => !empty($row['phone']) ? $row['phone'] : $this->generateUniquePhone(),
                    'email'                           => $row['email']   ?? null,
                    'nid_number'                      => $row['nid']     ?? null,
                    'address'                         => $row['address'] ?? null,
                    'mac_reseller_zone_id'            => $row['zone_id']     ?: null,
                    'mac_reseller_sub_zone_id'        => $row['sub_zone_id'] ?: null,
                    'mac_reseller_tariff_package_id'  => $row['package_id']  ?: null,
                    'protocol_type_id'                => $row['protocol_id'] ?: null,
                    'pppoe_username'                  => $row['pppoe_username'] ?? null,
                    'pppoe_password'                  => $row['pppoe_password'] ?? null,
                    'ip_address'                       => $row['ip_address']     ?? null,
                    'status'                           => strtolower($row['status'] ?? '') ?: 'active',
                    'monthly_bill_amount'              => $row['monthly_bill'] ?: null,
                    'connection_date'                  => $row['join_date'] ?: today(),
                    'billing_date'                     => 1,
                    'mikrotik_status'                  => 'pending',
                    'created_by'                        => null, // created by a reseller, not an internal Admin User
                    'remarks'                           => 'Excel Import (Reseller)',
                ]);

                $imported++;

                // Provision on MikroTik — best-effort, never blocks the row from
                // counting as imported (DB record is already saved above).
                if ($client->pppoe_username && $client->pppoe_password) {
                    if ((new MikrotikService())->provisionResellerCustomer($client)) {
                        $provisioned++;
                        $client->update(['mikrotik_status' => 'active']);
                    } else {
                        $client->update(['mikrotik_status' => 'failed']);
                    }
                }

            } catch (\Exception $e) {
                Log::warning("Reseller Excel Import error row {$index}: " . $e->getMessage());
                $skipped++;
            }
        }

        return redirect()->route('reseller.client.index')
            ->with('success', "{$imported} client(s) imported successfully. {$skipped} skipped. {$provisioned} provisioned on MikroTik.");
    }

    public function downloadTemplate()
    {
        $columns = [
            'name', 'mobile', 'email', 'nid', 'address', 'zone', 'sub_zone',
            'package', 'protocol_type', 'pppoe_username', 'pppoe_password',
            'ip_address', 'status', 'monthly_bill', 'join_date',
        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray($columns, null, 'A1');
        $sheet->fromArray([
            'Md Nizam Uddin', '01712345678', 'nizam@gmail.com', '1994381557500',
            'Meraj Nagar, Dhaka', 'Meraj Nagar', '', 'Home 10Mbps', 'PPPOE',
            'nizam_isp', 'pass12345', '192.168.1.100', 'active', '500', date('Y-m-d'),
        ], null, 'A2');

        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, 'client-import-template.xlsx');
    }

    // ══════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════

    private function parseSpreadsheet($file): array
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();
        $data        = $sheet->toArray(null, true, true, false);

        if (empty($data)) {
            return [];
        }

        $header = array_map(fn($h) => strtolower(trim((string) $h)), array_shift($data));
        $rows   = [];

        foreach ($data as $line) {
            if (count(array_filter($line, fn($v) => $v !== null && $v !== '')) === 0) {
                continue;
            }

            $row = [];
            foreach ($header as $i => $key) {
                if ($key === '') continue;
                $value = $line[$i] ?? null;
                $row[$key] = is_string($value) ? trim($value) : $value;
            }
            $rows[] = $row;
        }

        return $rows;
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = 'ISP-' . str_pad(rand(1, 99999), 4, '0', STR_PAD_LEFT);
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }

    private function generateUniquePhone(): string
    {
        do {
            $phone = '000' . rand(10000000, 99999999);
        } while (Customer::where('phone', $phone)->exists());

        return $phone;
    }
}