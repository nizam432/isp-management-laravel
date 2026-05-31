<?php
namespace App\Http\Controllers;
use App\Models\Package;
use App\Models\ActivityLog;
use App\Models\MikrotikRouter;
use App\Models\ClientType;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class PackageController extends Controller
{
    /**
     * Display a paginated list of all packages.
     */
    public function index()
    {
        $packages         = Package::withCount('customers')->latest()->paginate(15);
        $mikrotikProfiles = $this->getMikrotikProfiles();
        $clientTypes      = \App\Models\ClientType::active()->get();
        return view('packages.index', compact('packages', 'mikrotikProfiles', 'clientTypes'));
    }
    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $mikrotikProfiles = $this->getMikrotikProfiles();
        return view('packages.create', compact('mikrotikProfiles'));
    }

    /**
     * Store a newly created package in the database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'             => 'required|string|max:100',
            'speed_download'   => 'required|integer|min:1',
            'speed_upload'     => 'required|integer|min:1',
            'price'            => 'required|numeric|min:0',
            'connection_fee'   => 'nullable|numeric|min:0',
            'client_type_id'   => 'nullable|integer',
            'data_limit'       => 'nullable|integer|min:0',
            'btrc_price'       => 'nullable|numeric|min:0',
            'btrc_bandwidth'   => 'nullable|string|max:50',
            'description'      => 'nullable|string',
        ]);

        // MikroTik profile — existing select or new create
        $mikrotikProfile = $request->filled('new_mikrotik_profile')
            ? $request->new_mikrotik_profile
            : $request->mikrotik_profile;

        $package = Package::create([
            'name'             => $request->name,
            'speed_download'   => $request->speed_download,
            'speed_upload'     => $request->speed_upload,
            'price'            => $request->price,
            'connection_fee'   => $request->connection_fee ?? 0,
            'client_type_id'   => $request->client_type_id ?? 0,
            'data_limit'       => $request->data_limit ?? 0,
            'mikrotik_profile' => $mikrotikProfile,
            'btrc_price'       => $request->btrc_price,
            'btrc_bandwidth'   => $request->btrc_bandwidth,
            'description'      => $request->description,
            'is_active'        => true,
        ]);

        // MikroTik profile sync — সব router এ missing হলে create
        if ($mikrotikProfile) {
            $this->syncProfileToAllRouters($package, $mikrotikProfile);
        }

        ActivityLog::log('Package created', 'Package', $package->id, null, $package->toArray());

        return redirect()->route('packages.index')
                         ->with('success', "Package '{$package->name}' created successfully.");
    }

    private function syncProfileToAllRouters(Package $package, string $profileName): void
    {
        $routers  = MikrotikRouter::where('is_active', 1)->get();
        $mikrotik = new MikrotikService();

        foreach ($routers as $router) {
            try {
                $mikrotik->withRouter($router, function($m) use ($package, $profileName) {
                    $profiles = $m->getPPPoEProfiles();
                    $exists   = collect($profiles)->firstWhere('name', $profileName);

                    if (!$exists) {
                        $m->createPPPoEProfile([
                            'name'          => $profileName,
                            'upload_mbps'   => $package->speed_upload,
                            'download_mbps' => $package->speed_download,
                        ]);
                        Log::info("Profile '{$profileName}' created on [{$router->name}]");
                    } else {
                        Log::info("Profile '{$profileName}' already exists on [{$router->name}] — skipped");
                    }
                });
            } catch (\Exception $e) {
                Log::warning("Router [{$router->name}] profile sync failed: " . $e->getMessage());
            }
        }
    }
 /*    private function syncProfileToAllRouters(Package $package): void
    {
        $routers  = MikrotikRouter::where('is_active', 1)->get();
        $mikrotik = new MikrotikService();

        foreach ($routers as $router) {
            try {
                $mikrotik->withRouter($router, function($m) use ($package) {
                    // Already আছে কিনা check
                    $profiles = $m->getPPPoEProfiles();
                    $exists   = collect($profiles)->firstWhere('name', $package->mikrotik_profile);

                    if (!$exists) {
                        $m->createPPPoEProfile([
                            'name'          => $package->mikrotik_profile,
                            'upload_mbps'   => $package->speed_upload,
                            'download_mbps' => $package->speed_download,
                        ]);
                        Log::info("Profile '{$package->mikrotik_profile}' created on router '{$package->mikrotik_profile}'");
                    }
                });
            } catch (\Exception $e) {
                Log::warning("Router [{$router->name}] profile sync failed: " . $e->getMessage());
            }
        }
    } */

    /**
     * Display the specified package with its customer list.
     */
    public function show(Package $package)
    {
        $package->load('customers');
        return view('packages.show', compact('package'));
    }

    /**
     * Show the form for editing the specified package.
     */
    public function edit(Package $package)
    {
        $mikrotikProfiles = $this->getMikrotikProfiles();
        return view('packages.edit', compact('package', 'mikrotikProfiles'));
    }

    /**
     * Update the specified package in the database.
     */
    public function update(Request $request, Package $package)
{
    $request->validate([
        'name'             => 'required|string|max:100',
        'speed_download'   => 'required|integer|min:1',
        'speed_upload'     => 'required|integer|min:1',
        'price'            => 'required|numeric|min:0',
        'connection_fee'   => 'nullable|numeric|min:0',
        'client_type_id'   => 'nullable|integer',
        'data_limit'       => 'nullable|integer|min:0',
        'btrc_price'       => 'nullable|numeric|min:0',
        'btrc_bandwidth'   => 'nullable|string|max:50',
        'description'      => 'nullable|string',
    ]);
/* echo '<pre>'; print_r($request->all());
exit; */
    // MikroTik profile — existing select or new create
    $mikrotikProfile = $request->filled('new_mikrotik_profile')
        ? $request->new_mikrotik_profile
        : $request->mikrotik_profile;

    $old = $package->toArray();

    $package->update([
        'name'             => $request->name,
        'speed_download'   => $request->speed_download,
        'speed_upload'     => $request->speed_upload,
        'price'            => $request->price,
        'connection_fee'   => $request->connection_fee ?? 0,
        'client_type_id'   => $request->client_type_id ?? 0,
        'data_limit'       => $request->data_limit ?? 0,
        'mikrotik_profile' => $mikrotikProfile,
        'btrc_price'       => $request->btrc_price,
        'btrc_bandwidth'   => $request->btrc_bandwidth,
        'description'      => $request->description,
    ]);

    // MikroTik profile sync
    if ($mikrotikProfile) {
        $this->syncProfileToAllRouters($package, $mikrotikProfile);
    }

    ActivityLog::log('Package updated', 'Package', $package->id, $old, $package->toArray());

    return redirect()->route('packages.index')
                     ->with('success', "Package '{$package->name}' updated successfully.");
}
    /**
     * Delete the specified package.
     */
    public function destroy(Package $package)
    {
        if ($package->customers()->count() > 0) {
            return back()->with('error', "Cannot delete — {$package->customers()->count()} customers assigned.");
        }

        ActivityLog::log('Package deleted', 'Package', $package->id, $package->toArray(), null);
        $package->delete();

        return redirect()->route('packages.index')
                         ->with('success', "Package '{$package->name}' deleted successfully.");
    }

    /**
     * Toggle the active/inactive status of a package.
     */
    public function toggleStatus(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);
        $status = $package->is_active ? 'activated' : 'deactivated';
        return back()->with('success', "Package {$status} successfully.");
    }



    /**
     * Save synced packages from MikroTik.
     */
    public function syncStore(Request $request)
{
    $count = 0;
    $defaultPackage = Package::active()->first();

    foreach ($request->profiles as $profile) {
        // selected না থাকলে skip
        if (empty($profile['selected'])) continue;

        // name না থাকলে skip
        if (empty($profile['name'])) continue;

        // Already exists — skip
        if (Package::where('mikrotik_profile', $profile['name'])->exists()) continue;

        // Parse speed from rate-limit (e.g. "10M/10M" or "10MB/10MB")
        $download = 10;
        $upload   = 10;
        if (!empty($profile['rate_limit'])) {
            preg_match('/(\d+)/', $profile['rate_limit'], $matches);
            if (!empty($matches[1])) {
                $download = (int) $matches[1];
                $upload   = (int) $matches[1];
            }
        }

        Package::create([
            'name'             => $profile['name'],
            'mikrotik_profile' => $profile['name'],
            'speed_download'   => $download,
            'speed_upload'     => $upload,
            'price'            => $profile['price'] ?? 0,
            'connection_fee'   => $profile['connection_fee'] ?? 0,
            'client_type_id' => $profile['client_type_id'] ?? 0,
            'is_active'        => true,
        ]);

        $count++;
    }

    return redirect()->route('packages.index')
                     ->with('success', "{$count} package(s) synced from MikroTik.");
}

    public function syncPreview(Request $request)
    {
        $routers = MikrotikRouter::where('is_active', 1)->get();

        // Router select না করলে first router use করো
        $selectedRouter = $request->router_id
            ? MikrotikRouter::findOrFail($request->router_id)
            : $routers->first();

        $profiles      = $this->getMikrotikProfilesFull($selectedRouter);
        $existingNames = Package::pluck('mikrotik_profile')->toArray();
        $clientTypes   = ClientType::active()->get();

        return view('packages.sync-preview', compact('profiles', 'existingNames', 'routers', 'selectedRouter','clientTypes'));
    }

    private function getMikrotikProfilesFull($router = null): array
    {
        try {
            $router = $router ?? MikrotikRouter::where('is_active', 1)->first();
            if (!$router) return [];
            $mikrotik = new MikrotikService();
            return $mikrotik->withRouter($router, fn($m) => $m->getPPPoEProfiles());
        } catch (\Exception $e) {
            return [];
        }
    }
    // ── Private Helpers ───────────────────────────────────

    private function getMikrotikProfiles(): array
    {
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if (!$router) return [];
            $mikrotik = new MikrotikService();
            $profiles = $mikrotik->withRouter($router, fn($m) => $m->getPPPoEProfiles());
            return collect($profiles)->pluck('name')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

 /*    private function getMikrotikProfilesFull(): array
    {
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if (!$router) return [];
            $mikrotik = new MikrotikService();
            return $mikrotik->withRouter($router, fn($m) => $m->getPPPoEProfiles());
        } catch (\Exception $e) {
            return [];
        }
    } */
}