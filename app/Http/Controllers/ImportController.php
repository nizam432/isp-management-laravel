<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    // ══════════════════════════════════════════════
    // MikroTik Direct Import
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

        // Default package — used when no profile match is found
        $defaultPackage = Package::active()->first();

        foreach ($request->users as $username) {
            if (empty($username)) {
                $skipped++;
                continue;
            }

            // Skip if already exists
            if (Customer::where('pppoe_username', $username)->exists()) {
                $skipped++;
                continue;
            }

            $password = $request->input("password_{$username}", 'pass' . rand(10000, 99999));
            $profile  = $request->input("profile_{$username}");
            $disabled = $request->input("disabled_{$username}", 'false');

            // Match package by MikroTik profile name
            $package = $profile
                ? Package::where('mikrotik_profile', $profile)->first()
                : null;

            // Fall back to default package if no match
            $package = $package ?? $defaultPackage;

            // Set customer status based on MikroTik disabled flag
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

            try { } catch (\Exception $e) {
                Log::warning("MikroTik Import failed for [{$username}]: " . $e->getMessage());
                $skipped++;
            }
        }

        return redirect()->route('customers.index')
            ->with('success', "{$imported} customer(s) imported successfully. {$skipped} skipped.");
    }

    // ══════════════════════════════════════════════
    // CSV Import
    // ══════════════════════════════════════════════

    public function csvPreview(Request $request)
    {
        $request->validate([
            'csv_file'   => 'required|file|mimes:csv,txt|max:2048',
            'package_id' => 'required|exists:packages,id',
        ]);

        $file = $request->file('csv_file');
        $rows = [];

        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $header = fgetcsv($handle);

            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) >= 1) {
                    $row = array_combine(
                        array_map('strtolower', array_map('trim', $header)),
                        array_map('trim', $data)
                    );
                    $rows[] = $row;
                }
            }
            fclose($handle);
        }

        $existingUsernames = Customer::pluck('pppoe_username')->toArray();
        $existingPhones    = Customer::pluck('phone')->toArray();

        foreach ($rows as &$row) {
            $row['_exists_username'] = in_array($row['pppoe_username'] ?? '', $existingUsernames);
            $row['_exists_phone']    = in_array($row['phone'] ?? '', $existingPhones);
            $row['_will_import']     = !$row['_exists_username'] && !$row['_exists_phone'];
        }

        return view('import.csv-preview', [
            'rows'       => $rows,
            'package_id' => $request->package_id,
            'packages'   => Package::active()->get(),
        ]);
    }

    public function csvImport(Request $request)
    {
        $request->validate([
            'rows'       => 'required|array',
            'package_id' => 'required|exists:packages,id',
        ]);

        $imported = 0;
        $skipped  = 0;

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

                Customer::create([
                    'customer_code'   => $this->generateUniqueCode(),
                    'name'            => $row['name']           ?? ('User-' . ($row['pppoe_username'] ?? $index)),
                    'phone'           => !empty($row['phone']) ? $row['phone'] : $this->generateUniquePhone(),
                    'email'           => $row['email']          ?? null,
                    'address'         => $row['address']        ?? null,
                    'area'            => $row['area']           ?? null,
                    'pppoe_username'  => $row['pppoe_username'] ?? null,
                    'pppoe_password'  => $row['pppoe_password'] ?? null,
                    'ip_address'      => $row['ip_address']     ?? null,
                    'package_id'      => $request->package_id,
                    'billing_date'    => $row['billing_date']   ?? 1,
                    'status'          => 'active',
                    'mikrotik_status' => 'active',
                    'created_by'      => auth()->id(),
                    'remarks'         => 'CSV Import',
                ]);

                $imported++;

            } catch (\Exception $e) {
                Log::warning("CSV Import error row {$index}: " . $e->getMessage());
                $skipped++;
            }
        }

        return redirect()->route('customers.index')
            ->with('success', "{$imported} customer(s) imported successfully. {$skipped} skipped.");
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=customer-import-template.csv',
        ];

        $columns = ['name', 'phone', 'email', 'address', 'area', 'pppoe_username', 'pppoe_password', 'ip_address', 'billing_date'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, [
                'Md Nizam Uddin', '01712345678', 'nizam@gmail.com',
                'Meraj Nagar, Dhaka', 'Meraj Nagar', 'nizam_isp',
                'pass12345', '192.168.1.100', '1',
            ]);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ══════════════════════════════════════════════
    // Helpers
    // ══════════════════════════════════════════════

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
