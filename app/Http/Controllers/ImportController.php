<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Package;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * ImportController
 * ─────────────────────────────────────────────
 * ২টা import method:
 * ১. MikroTik থেকে direct — PPPoE user list
 * ২. CSV দিয়ে — নাম, phone, username সহ
 */
class ImportController extends Controller
{
    // ══════════════════════════════════════════════
    // MikroTik Direct Import
    // ══════════════════════════════════════════════

    /**
     * GET /import
     * Import page দেখাও
     */
    public function index()
    {
        $routers  = MikrotikRouter::where('is_active', 1)->get();
        $packages = Package::active()->get();

        return view('import.index', compact('routers', 'packages'));
    }

    /**
     * POST /import/mikrotik/preview
     * MikroTik থেকে PPPoE user list আনো — preview দেখাও
     */
    public function mikrotikPreview(Request $request)
    {
        $request->validate([
            'router_id' => 'required|exists:mikrotik_routers,id',
        ]);

        $router = MikrotikRouter::findOrFail($request->router_id);

        try {
            $mikrotik = new MikrotikService();
            $users    = $mikrotik->withRouter($router, fn($m) => $m->getPPPoEUsers());

            // Already imported username গুলো বের করো
            $existingUsernames = Customer::pluck('pppoe_username')->toArray();

            // নতুন user গুলো filter করো
            $newUsers = array_filter($users, fn($u) =>
                !in_array($u['name'] ?? '', $existingUsernames)
            );

            return view('import.mikrotik-preview', [
                'users'    => array_values($newUsers),
                'existing' => count($users) - count($newUsers),
                'router'   => $router,
                'packages' => Package::active()->get(),
            ]);

        } catch (\Exception $e) {
            return back()->with('error', 'MikroTik connect করতে সমস্যা: ' . $e->getMessage());
        }
    }

    /**
     * POST /import/mikrotik/execute
     * Selected users import করো
     */
    public function mikrotikImport(Request $request)
    {
        $request->validate([
            'users'      => 'required|array',
            'package_id' => 'required|exists:packages,id',
        ]);

        $imported = 0;
        $skipped  = 0;

        foreach ($request->users as $username) {
            // Already আছে কিনা check
            if (Customer::where('pppoe_username', $username)->exists()) {
                $skipped++;
                continue;
            }

            // Password আলাদাভাবে আনো
            $password = $request->input("password_{$username}", 'imported_' . rand(1000, 9999));

            Customer::create([
                'customer_code'  => Customer::generateCode(),
                'name'           => 'Imported - ' . $username,
                'phone'          => '000' . rand(10000000, 99999999), // placeholder
                'pppoe_username' => $username,
                'pppoe_password' => $password,
                'package_id'     => $request->package_id,
                'billing_date'   => 1,
                'status'         => 'active',
                'mikrotik_status'=> 'active',
                'created_by'     => auth()->id(),
                'remarks'        => 'Imported from MikroTik',
            ]);

            $imported++;
        }

        return redirect()->route('customers.index')
            ->with('success', "{$imported} জন customer import হয়েছে। {$skipped} টি skip হয়েছে (already ছিল)।");
    }

    // ══════════════════════════════════════════════
    // CSV Import
    // ══════════════════════════════════════════════

    /**
     * POST /import/csv/preview
     * CSV file upload করো — preview দেখাও
     */
    public function csvPreview(Request $request)
    {
        $request->validate([
            'csv_file'   => 'required|file|mimes:csv,txt|max:2048',
            'package_id' => 'required|exists:packages,id',
        ]);

        $file = $request->file('csv_file');
        $rows = [];

        if (($handle = fopen($file->getPathname(), 'r')) !== false) {
            $header = fgetcsv($handle); // প্রথম row = header

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

        // Already imported check
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

    /**
     * POST /import/csv/execute
     * CSV data import করো
     */
    public function csvImport(Request $request)
    {
        $request->validate([
            'rows'       => 'required|array',
            'package_id' => 'required|exists:packages,id',
        ]);

        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        foreach ($request->rows as $index => $row) {
            try {
                // Duplicate check
                if (Customer::where('pppoe_username', $row['pppoe_username'] ?? '')->exists()) {
                    $skipped++;
                    continue;
                }
                if (!empty($row['phone']) && Customer::where('phone', $row['phone'])->exists()) {
                    $skipped++;
                    continue;
                }

                Customer::create([
                    'customer_code'  => Customer::generateCode(),
                    'name'           => $row['name']           ?? ('User-' . ($row['pppoe_username'] ?? $index)),
                    'phone'          => $row['phone']          ?? ('000' . rand(10000000, 99999999)),
                    'email'          => $row['email']          ?? null,
                    'address'        => $row['address']        ?? null,
                    'area'           => $row['area']           ?? null,
                    'pppoe_username' => $row['pppoe_username'] ?? null,
                    'pppoe_password' => $row['pppoe_password'] ?? null,
                    'ip_address'     => $row['ip_address']     ?? null,
                    'package_id'     => $request->package_id,
                    'billing_date'   => $row['billing_date']   ?? 1,
                    'status'         => 'active',
                    'mikrotik_status'=> 'active',
                    'created_by'     => auth()->id(),
                    'remarks'        => 'CSV Import',
                ]);

                $imported++;

            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                Log::warning("CSV Import error row {$index}: " . $e->getMessage());
            }
        }

        $message = "{$imported} জন customer import হয়েছে।";
        if ($skipped > 0) $message .= " {$skipped} টি skip।";
        if (!empty($errors)) $message .= " " . count($errors) . " টি error।";

        return redirect()->route('customers.index')->with('success', $message);
    }

    /**
     * GET /import/csv/template
     * CSV template download করো
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename=customer-import-template.csv',
        ];

        $columns = ['name', 'phone', 'email', 'address', 'area', 'pppoe_username', 'pppoe_password', 'ip_address', 'billing_date'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, $columns);

            // Example row
            fputcsv($file, [
                'Md Nizam Uddin',
                '01712345678',
                'nizam@gmail.com',
                'Meraj Nagar, Dhaka',
                'Meraj Nagar',
                'nizam_isp',
                'pass12345',
                '192.168.1.100',
                '1',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
