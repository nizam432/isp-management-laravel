<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Zone;
use App\Models\SubZone;
use App\Models\ProtocolType;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ImportController extends Controller
{
    // ══════════════════════════════════════════════
    // MikroTik Direct Import — UNCHANGED
    // ══════════════════════════════════════════════

    public function index()
    {
        $routers  = MikrotikRouter::where('is_active', 1)->get();
        $packages = Package::active()->get();

        return view('import.index', compact('routers', 'packages'));
    }

    public function mikrotikPreview(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:mikrotik_routers,id',
        ]);

        $router = MikrotikRouter::findOrFail($request->router_id);

        try {
            $mikrotik = new MikrotikService();
            $users    = $mikrotik->withRouter($router, fn($m) => $m->getPPPoEUsers());

            $existingUsernames = Customer::pluck('pppoe_username')->toArray();

            $newUsers = array_filter($users, fn($u) =>
                !empty($u['name']) && !in_array($u['name'], $existingUsernames)
            );

            return view('import.mikrotik-preview', [
                'users'    => array_values($newUsers),
                'existing' => count($users) - count($newUsers),
                'router'   => $router,
                'packages' => Package::active()->get(),
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to connect to MikroTik: ' . $e->getMessage());
        }
    }

    public function mikrotikSingleImport(Request $request)
    {
        $request->validate([
            'username'  => 'required|string|unique:customers,pppoe_username',
            'router_id' => 'required|integer',
        ]);

        $profile  = $request->profile;
        $disabled = $request->disabled === 'true';

        $package = $profile
            ? Package::where('mikrotik_profile', $profile)->first()
            : Package::active()->first();

        $customer = Customer::create([
            'customer_code'   => $this->generateUniqueCode(),
            'name'            => 'Imported - ' . $request->username,
            'phone'           => $this->generateUniquePhone(),
            'pppoe_username'  => $request->username,
            'pppoe_password'  => $request->password,
            'package_id'      => $package?->id,
            'router_id'       => $request->router_id,
            'connection_date' => today(),
            'billing_date'    => 1,
            'status'          => $disabled ? 'suspended' : 'active',
            'mikrotik_status' => $disabled ? 'suspended' : 'active',
            'created_by'      => auth()->id(),
            'remarks'         => 'Imported from MikroTik',
        ]);

        return redirect()->route('customers.edit', $customer)
            ->with('success', "'{$request->username}' imported successfully. Please fill in the remaining details.");
    }

    public function mikrotikImport(Request $request)
    {
        $request->validate([
            'users'     => 'required|array',
            'router_id' => 'required|integer',
        ]);

        $imported = 0;
        $skipped  = 0;

        $defaultPackage = Package::active()->first();

        foreach ($request->users as $username) {
            if (empty($username)) {
                $skipped++;
                continue;
            }

            if (Customer::where('pppoe_username', $username)->exists()) {
                $skipped++;
                continue;
            }

            $password = $request->input("password_{$username}", 'pass' . rand(10000, 99999));
            $profile  = $request->input("profile_{$username}");
            $disabled = $request->input("disabled_{$username}", 'false');

            $package = $profile
                ? Package::where('mikrotik_profile', $profile)->first()
                : null;

            $package = $package ?? $defaultPackage;

            $status         = ($disabled === 'true') ? 'suspended' : 'active';
            $mikrotikStatus = ($disabled === 'true') ? 'suspended' : 'active';

            Customer::create([
                'customer_code'   => $this->generateUniqueCode(),
                'name'            => 'Imported - ' . $username,
                'phone'           => $this->generateUniquePhone(),
                'pppoe_username'  => $username,
                'pppoe_password'  => $password,
                'package_id'      => $package?->id,
                'connection_date' => today(),
                'billing_date'    => 1,
                'router_id'       => $request->router_id,
                'status'          => $status,
                'mikrotik_status' => $mikrotikStatus,
                'created_by'      => auth()->id(),
                'remarks'         => 'Imported from MikroTik',
            ]);

            $imported++;
        }

        return redirect()->route('customers.index')
            ->with('success', "{$imported} customer(s) imported successfully. {$skipped} skipped.");
    }

    // ══════════════════════════════════════════════
    // XLSX Import (was CSV — kept the same route/method
    // names so routes/web.php doesn't need changes)
    // ══════════════════════════════════════════════

    /**
     * Expected header row (case-insensitive, order doesn't matter):
     * name, mobile, email, nid, address, zone, sub_zone, package,
     * protocol_type, pppoe_username, pppoe_password, ip_address,
     * status, monthly_bill, join_date
     *
     * Zone / Sub Zone / Package / Protocol Type are matched BY NAME against
     * the ISP's own global tables (Zone, SubZone, Package, ProtocolType).
     * The old free-text `area` column and the "pick one Package for the
     * whole file" dropdown are both gone — each row carries its own Package.
     */
    public function csvPreview(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:xlsx,xls|max:5120',
        ]);

        $rows = $this->parseSpreadsheet($request->file('csv_file'));

        // the excel column is called "mobile" (matches the template/sample),
        // but the Customer model's own field is "phone" — normalize once here
        foreach ($rows as &$row) {
            $row['phone'] = $row['mobile'] ?? $row['phone'] ?? null;
        }
        unset($row);

        $existingUsernames = Customer::pluck('pppoe_username')->toArray();
        $existingPhones    = Customer::pluck('phone')->toArray();

        $zones     = Zone::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);
        $subZones  = SubZone::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);
        $packages  = Package::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);
        $protocols = ProtocolType::pluck('id', 'name')->mapWithKeys(fn($id, $name) => [strtolower(trim($name)) => $id]);

        foreach ($rows as &$row) {
            $row['_zone_id']     = $row['zone'] ? ($zones[strtolower(trim($row['zone']))] ?? null) : null;
            $row['_sub_zone_id'] = $row['sub_zone'] ? ($subZones[strtolower(trim($row['sub_zone']))] ?? null) : null;
            $row['_package_id']  = $row['package'] ? ($packages[strtolower(trim($row['package']))] ?? null) : null;
            $row['_protocol_id'] = $row['protocol_type'] ? ($protocols[strtolower(trim($row['protocol_type']))] ?? null) : null;

            $row['_zone_ok']     = !$row['zone']          || $row['_zone_id'];
            $row['_package_ok']  = !$row['package']        || $row['_package_id'];
            $row['_protocol_ok'] = !$row['protocol_type']  || $row['_protocol_id'];

            $row['_exists_username'] = in_array($row['pppoe_username'] ?? '', $existingUsernames);
            $row['_exists_phone']    = !empty($row['phone']) && in_array($row['phone'], $existingPhones);

            $row['_will_import'] = !$row['_exists_username'] && !$row['_exists_phone']
                && $row['_zone_ok'] && $row['_package_ok'] && $row['_protocol_ok']
                && !empty($row['pppoe_username']) && !empty($row['pppoe_password']);
        }

        return view('import.csv-preview', ['rows' => $rows]);
    }

    public function csvImport(Request $request)
    {
        $request->validate(['rows' => 'required|array']);

        $imported    = 0;
        $skipped     = 0;
        $provisioned = 0;

        foreach ($request->rows as $index => $row) {
            try {
                if (Customer::where('pppoe_username', $row['pppoe_username'] ?? '')->exists()) {
                    $skipped++;
                    continue;
                }
                if (!empty($row['phone']) && Customer::where('phone', $row['phone'])->exists()) {
                    $skipped++;
                    continue;
                }

                $customer = Customer::create([
                    'customer_code'   => $this->generateUniqueCode(),
                    'name'            => $row['name']    ?? ('User-' . ($row['pppoe_username'] ?? $index)),
                    'phone'           => !empty($row['phone']) ? $row['phone'] : $this->generateUniquePhone(),
                    'email'           => $row['email']    ?? null,
                    'nid_number'      => $row['nid']      ?? null,
                    'address'         => $row['address']  ?? null,
                    'zone_id'         => $row['zone_id']         ?: null,
                    'sub_zone_id'     => $row['sub_zone_id']     ?: null,
                    'package_id'      => $row['package_id']      ?: null,
                    'protocol_type_id'=> $row['protocol_id']     ?: null,
                    'pppoe_username'  => $row['pppoe_username'] ?? null,
                    'pppoe_password'  => $row['pppoe_password'] ?? null,
                    'ip_address'      => $row['ip_address']     ?? null,
                    'status'          => strtolower($row['status'] ?? '') ?: 'active',
                    'monthly_bill_amount' => $row['monthly_bill'] ?: null,
                    'connection_date' => $row['join_date'] ?: today(),
                    'billing_date'    => 1,
                    'mikrotik_status' => 'pending',
                    'created_by'      => auth()->id(),
                    'remarks'         => 'Excel Import',
                ]);

                $imported++;

                // Provision on MikroTik — best-effort, never blocks the row from
                // counting as imported (DB record is already saved above).
                if ($customer->status === 'active' && $customer->pppoe_username && $customer->pppoe_password) {
                    try {
                        $router = $customer->router ?? MikrotikRouter::active()->first();
                        if ($router) {
                            $mikrotik = new MikrotikService();
                            $mikrotik->withRouter($router, function ($m) use ($customer) {
                                $m->createPPPoEUser([
                                    'username' => $customer->pppoe_username,
                                    'password' => $customer->pppoe_password,
                                    'profile'  => $customer->package->mikrotik_profile ?? 'default',
                                    'comment'  => "ISP-{$customer->customer_code} | {$customer->name}",
                                ]);
                            });
                            $customer->update(['mikrotik_status' => 'active']);
                            $provisioned++;
                        } else {
                            $customer->update(['mikrotik_status' => 'failed']);
                        }
                    } catch (\Exception $e) {
                        Log::warning("Excel Import MikroTik provision failed for row {$index}: " . $e->getMessage());
                        $customer->update(['mikrotik_status' => 'failed']);
                    }
                }

            } catch (\Exception $e) {
                Log::warning("Excel Import error row {$index}: " . $e->getMessage());
                $skipped++;
            }
        }

        return redirect()->route('customers.index')
            ->with('success', "{$imported} customer(s) imported successfully. {$skipped} skipped. {$provisioned} provisioned on MikroTik.");
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
        }, 'customer-import-template.xlsx');
    }

    // ══════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════

    /** Parse an uploaded .xlsx/.xls file into an array of [lowercased_header => value] rows. */
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
            // skip fully-blank rows
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