<?php
namespace App\Http\Controllers;
use App\Models\Package;
use App\Models\ActivityLog;
use App\Models\MikrotikRouter;
use App\Services\MikrotikService;
use Illuminate\Http\Request;
class PackageController extends Controller
{
    /**
     * Display a paginated list of all packages.
     */
    public function index()
    {
        $packages = Package::withCount('customers')->latest()->paginate(15);
        return view('packages.index', compact('packages'));
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
            'type'             => 'required|in:home,business,student',
            'data_limit'       => 'nullable|integer|min:0',
            'mikrotik_profile' => 'nullable|string|max:100',
        ]);
        $package = Package::create($request->all());
        ActivityLog::log('Package created', 'Package', $package->id, null, $package->toArray());
        return redirect()->route('packages.index')
                         ->with('success', 'Package created successfully.');
    }

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
            'name'           => 'required|string|max:100',
            'speed_download' => 'required|integer|min:1',
            'speed_upload'   => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
            'type'           => 'required|in:home,business,student',
        ]);
        $old = $package->toArray();
        $package->update($request->all());
        ActivityLog::log('Package updated', 'Package', $package->id, $old, $package->toArray());
        return redirect()->route('packages.index')
                         ->with('success', 'Package updated successfully.');
    }

    /**
     * Delete the specified package.
     */
    public function destroy(Package $package)
    {
        if ($package->customers()->count() > 0) {
            return back()->with('error', 'Cannot delete — customers are assigned to this package.');
        }
        ActivityLog::log('Package deleted', 'Package', $package->id, $package->toArray(), null);
        $package->delete();
        return redirect()->route('packages.index')
                         ->with('success', 'Package deleted successfully.');
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
     * Show MikroTik profile sync preview page.
     */
    public function syncPreview()
    {
        $profiles        = $this->getMikrotikProfilesFull();
        $existingNames   = Package::pluck('mikrotik_profile')->toArray();
        return view('packages.sync-preview', compact('profiles', 'existingNames'));
    }

    /**
     * Save synced packages from MikroTik.
     */
    public function syncStore(Request $request)
    {
        $request->validate([
            'profiles'                => 'required|array',
            'profiles.*.name'         => 'required|string',
            'profiles.*.price'        => 'required|numeric|min:0',
            'profiles.*.connection_fee' => 'nullable|numeric|min:0',
            'profiles.*.type'         => 'required|in:home,business,student',
            'profiles.*.selected'     => 'nullable',
        ]);

        $count = 0;
        foreach ($request->profiles as $profile) {
            if (empty($profile['selected'])) continue;

            // Already exists — skip
            if (Package::where('mikrotik_profile', $profile['name'])->exists()) continue;

            // Parse speed from rate-limit (e.g. "10M/10M")
            $download = 0;
            $upload   = 0;
            if (!empty($profile['rate_limit'])) {
                $parts    = explode('/', $profile['rate_limit']);
                $download = (int) filter_var($parts[0] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                $upload   = (int) filter_var($parts[1] ?? 0, FILTER_SANITIZE_NUMBER_INT);
            }

            $package = Package::create([
                'name'             => $profile['name'],
                'mikrotik_profile' => $profile['name'],
                'speed_download'   => $download ?: 10,
                'speed_upload'     => $upload   ?: 10,
                'price'            => $profile['price'],
                'connection_fee'   => $profile['connection_fee'] ?? 0,
                'type'             => $profile['type'],
                'is_active'        => true,
            ]);

            ActivityLog::log('Package synced from MikroTik', 'Package', $package->id, null, $package->toArray());
            $count++;
        }

        return redirect()->route('packages.index')
                         ->with('success', "{$count} টি package MikroTik থেকে sync হয়েছে।");
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

    private function getMikrotikProfilesFull(): array
    {
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if (!$router) return [];
            $mikrotik = new MikrotikService();
            return $mikrotik->withRouter($router, fn($m) => $m->getPPPoEProfiles());
        } catch (\Exception $e) {
            return [];
        }
    }
}