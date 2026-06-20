<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ClientType;
use App\Models\MikrotikRouter;
use App\Models\Package;
use App\Models\ProtocolType;
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
        $clientTypes      = ClientType::active()->get();
        $protocolTypes    = ProtocolType::active()->get();

        return view('packages.index', compact('packages', 'mikrotikProfiles', 'clientTypes', 'protocolTypes'));
    }

    /**
     * Show the form for creating a new package.
     */
    public function create()
    {
        $mikrotikProfiles = $this->getMikrotikProfiles();
        $clientTypes      = ClientType::active()->get();
        $protocolTypes    = ProtocolType::active()->get();

        return view('packages.create', compact('mikrotikProfiles', 'clientTypes', 'protocolTypes'));
    }

    /**
     * Store a newly created package in the database.
     */
    public function store(Request $request)
    {
        $validated = $this->validatePackage($request);

        $mikrotikProfile = $this->resolveMikrotikProfile($request);

        $package = Package::create([
            'name'              => $validated['name'],
            'speed_download'    => $validated['speed_download'],
            'speed_upload'      => $validated['speed_upload'],
            'price'             => $validated['price'],
            'connection_fee'    => $validated['connection_fee'] ?? 0,
            'client_type_id'    => $validated['client_type_id'] ?? 0,
            'protocol_type_id'  => $validated['protocol_type_id'] ?? null,
            'data_limit'        => $validated['data_limit'] ?? 0,
            'validity_days'     => $validated['validity_days'] ?? 30,
            'mikrotik_profile'  => $mikrotikProfile,
            'btrc_price'        => $validated['btrc_price'] ?? null,
            'btrc_bandwidth'    => $validated['btrc_bandwidth'] ?? null,
            'description'       => $validated['description'] ?? null,
            'is_active'         => true,
        ]);

        if ($mikrotikProfile) {
            $this->syncProfileToAllRouters($package, $mikrotikProfile);
        }

        ActivityLog::log('Package created', 'Package', $package->id, null, $package->toArray());

        return redirect()->route('packages.index')
                          ->with('success', "Package '{$package->name}' created successfully.");
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
        $clientTypes      = ClientType::active()->get();
        $protocolTypes    = ProtocolType::active()->get();

        return view('packages.edit', compact('package', 'mikrotikProfiles', 'clientTypes', 'protocolTypes'));
    }

    /**
     * Update the specified package in the database.
     */
    public function update(Request $request, Package $package)
    {
        $validated = $this->validatePackage($request);

        $mikrotikProfile = $this->resolveMikrotikProfile($request);

        $old = $package->toArray();

        $package->update([
            'name'              => $validated['name'],
            'speed_download'    => $validated['speed_download'],
            'speed_upload'      => $validated['speed_upload'],
            'price'             => $validated['price'],
            'connection_fee'    => $validated['connection_fee'] ?? 0,
            'client_type_id'    => $validated['client_type_id'] ?? 0,
            'protocol_type_id'  => $validated['protocol_type_id'] ?? null,
            'data_limit'        => $validated['data_limit'] ?? 0,
            'validity_days'     => $validated['validity_days'] ?? 30,
            'mikrotik_profile'  => $mikrotikProfile,
            'btrc_price'        => $validated['btrc_price'] ?? null,
            'btrc_bandwidth'    => $validated['btrc_bandwidth'] ?? null,
            'description'       => $validated['description'] ?? null,
        ]);

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
        $customerCount = $package->customers()->count();

        if ($customerCount > 0) {
            return back()->with('error', "Cannot delete — {$customerCount} customers assigned.");
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
     * Save synced packages selected from MikroTik.
     */
    public function syncStore(Request $request)
    {
        $count = 0;

        foreach ($request->input('profiles', []) as $profile) {
            // Skip rows the user didn't select
            if (empty($profile['selected'])) {
                continue;
            }

            // Skip rows with no profile name
            if (empty($profile['name'])) {
                continue;
            }

            // Skip if this profile is already mapped to a package
            if (Package::where('mikrotik_profile', $profile['name'])->exists()) {
                continue;
            }

            [$upload, $download] = $this->parseRateLimit($profile['rate_limit'] ?? null);

            Package::create([
                'name'             => $profile['name'],
                'mikrotik_profile' => $profile['name'],
                'speed_download'   => $download,
                'speed_upload'     => $upload,
                'price'            => $profile['price'] ?? 0,
                'connection_fee'   => $profile['connection_fee'] ?? 0,
                'client_type_id'   => $profile['client_type_id'] ?? 0,
                'protocol_type_id' => $profile['protocol_type_id'] ?? null,
                'validity_days'    => $profile['validity_days'] ?? 30,
                'is_active'        => true,
            ]);

            $count++;
        }

        return redirect()->route('packages.index')
                          ->with('success', "{$count} package(s) synced from MikroTik.");
    }

    /**
     * Show the MikroTik profile sync preview page.
     */
    public function syncPreview(Request $request)
    {
        $routers = MikrotikRouter::where('is_active', 1)->get();

        // Use the first active router if none was explicitly selected
        $selectedRouter = $request->router_id
            ? MikrotikRouter::findOrFail($request->router_id)
            : $routers->first();

        $protocol = $request->input('protocol', 'pppoe');

        $profiles      = $this->getMikrotikProfilesFull($selectedRouter, $protocol);
        $existingNames = Package::pluck('mikrotik_profile')->toArray();
        $clientTypes   = ClientType::active()->get();
        $protocolTypes = ProtocolType::active()->get();

        return view('packages.sync-preview', compact(
            'profiles',
            'existingNames',
            'routers',
            'selectedRouter',
            'clientTypes',
            'protocolTypes',
            'protocol'
        ));
    }

    /**
     * AJAX endpoint: return MikroTik profiles for a given protocol type.
     * Used by the Add/Edit package form when a Protocol Type is selected,
     * so the MikroTik Profile dropdown can be populated dynamically.
     *
     * GET /packages/mikrotik-profiles?protocol=pppoe|hotspot
     */
    public function mikrotikProfilesByProtocol(Request $request)
    {
        $protocol = strtolower((string) $request->query('protocol'));
        $router   = MikrotikRouter::where('is_active', 1)->first();

        if (!$router) {
            return response()->json(['success' => false, 'message' => 'No active router found.'], 404);
        }

        // Protocols with no MikroTik "profile" concept — nothing to fetch,
        // the form should fall back to manual profile-name entry.
        $noProfileProtocols = ['static', 'svpn'];

        if (in_array($protocol, $noProfileProtocols, true)) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            $mikrotik = new MikrotikService();

            $profiles = $mikrotik->withRouter($router, function ($m) use ($protocol) {
                return match ($protocol) {
                    'hotspot'        => $m->getHotspotProfiles(),
                    // PPTP shares the same /ppp/profile pool as PPPoE in RouterOS.
                    'pppoe', 'pptp'  => $m->getPPPoEProfiles(),
                    default          => null,
                };
            });

            if ($profiles === null) {
                Log::warning("mikrotikProfilesByProtocol: unrecognized protocol '{$protocol}'");
                return response()->json(['success' => false, 'message' => "Unsupported protocol: {$protocol}"], 422);
            }

            $names = collect($profiles)->pluck('name')->values()->all();

            return response()->json(['success' => true, 'data' => $names]);
        } catch (\Exception $e) {
            Log::warning("mikrotikProfilesByProtocol [{$protocol}] failed: " . $e->getMessage());

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Private Helpers ─────────────────────────────────────────────

    /**
     * Shared validation rules for store() and update().
     */
    private function validatePackage(Request $request): array
    {
        return $request->validate([
            'name'              => 'required|string|max:100',
            'speed_download'    => 'required|integer|min:1',
            'speed_upload'      => 'required|integer|min:1',
            'price'             => 'required|numeric|min:0',
            'connection_fee'    => 'nullable|numeric|min:0',
            'client_type_id'    => 'nullable|integer',
            'protocol_type_id'  => 'nullable|integer|exists:protocol_types,id',
            'data_limit'        => 'nullable|integer|min:0',
            'validity_days'     => 'required|integer|min:1',
            'btrc_price'        => 'nullable|numeric|min:0',
            'btrc_bandwidth'    => 'nullable|string|max:50',
            'description'       => 'nullable|string',
        ]);
    }

    /**
     * Resolve which MikroTik profile name to use — either the existing
     * profile selected from the dropdown, or a brand-new one typed in.
     */
    private function resolveMikrotikProfile(Request $request): ?string
    {
        return $request->filled('new_mikrotik_profile')
            ? $request->input('new_mikrotik_profile')
            : $request->input('mikrotik_profile');
    }

    /**
     * Parse a MikroTik rate-limit string into [upload, download] Mbps.
     *
     * RouterOS rate-limit format is "upload/download", e.g. "5M/20M"
     * means 5 Mbps upload and 20 Mbps download. Previously this method
     * only captured the first number and used it for both values, which
     * silently lost the second value. This now parses both sides.
     */
    private function parseRateLimit(?string $rateLimit): array
    {
        $defaultUpload   = 10;
        $defaultDownload = 10;

        if (empty($rateLimit)) {
            return [$defaultUpload, $defaultDownload];
        }

        // Expect formats like "5M/20M", "5000k/20000k", "1G/1G"
        if (!preg_match('/^(\d+)\s*[a-zA-Z]*\s*\/\s*(\d+)\s*[a-zA-Z]*/', trim($rateLimit), $matches)) {
            return [$defaultUpload, $defaultDownload];
        }

        $upload   = (int) $matches[1];
        $download = (int) $matches[2];

        return [$upload ?: $defaultUpload, $download ?: $defaultDownload];
    }

    /**
     * Create the given MikroTik profile on every active router that
     * doesn't already have it. Uses the package's protocol type to
     * decide whether to create a PPPoE or Hotspot profile.
     */
    private function syncProfileToAllRouters(Package $package, string $profileName): void
    {
        $protocolSlug = $package->protocolType
            ? \Illuminate\Support\Str::slug($package->protocolType->name)
            : 'pppoe';

        // Static / SVPN / unrecognized protocols have no MikroTik profile
        // concept — nothing to create on the router.
        if (!in_array($protocolSlug, ['pppoe', 'pptp', 'hotspot'], true)) {
            return;
        }

        $routers  = MikrotikRouter::where('is_active', 1)->get();
        $mikrotik = new MikrotikService();

        foreach ($routers as $router) {
            try {
                $mikrotik->withRouter($router, function ($m) use ($package, $profileName, $protocolSlug) {
                    if ($protocolSlug === 'hotspot') {
                        $profiles = $m->getHotspotProfiles();
                        $exists   = collect($profiles)->firstWhere('name', $profileName);

                        if (!$exists) {
                            $m->createHotspotProfile([
                                'name'          => $profileName,
                                'upload_mbps'   => $package->speed_upload,
                                'download_mbps' => $package->speed_download,
                            ]);
                            Log::info("Hotspot profile '{$profileName}' created on [{$router->name}]");
                        } else {
                            Log::info("Hotspot profile '{$profileName}' already exists on [{$router->name}] — skipped");
                        }

                        return;
                    }

                    // PPPoE and PPTP share the same /ppp/profile pool.
                    $profiles = $m->getPPPoEProfiles();
                    $exists   = collect($profiles)->firstWhere('name', $profileName);

                    if (!$exists) {
                        $m->createPPPoEProfile([
                            'name'          => $profileName,
                            'upload_mbps'   => $package->speed_upload,
                            'download_mbps' => $package->speed_download,
                        ]);
                        Log::info("PPPoE profile '{$profileName}' created on [{$router->name}]");
                    } else {
                        Log::info("PPPoE profile '{$profileName}' already exists on [{$router->name}] — skipped");
                    }
                });
            } catch (\Exception $e) {
                Log::warning("Router [{$router->name}] profile sync failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Get the full MikroTik profile data (name + rate-limit etc.) for a
     * given router and protocol, used by the sync preview page.
     */
    private function getMikrotikProfilesFull($router = null, string $protocol = 'pppoe'): array
    {
        try {
            $router = $router ?? MikrotikRouter::where('is_active', 1)->first();
            if (!$router) {
                return [];
            }

            $mikrotik = new MikrotikService();

            return $mikrotik->withRouter($router, function ($m) use ($protocol) {
                return match ($protocol) {
                    'hotspot'       => $m->getHotspotProfiles(),
                    'pppoe', 'pptp' => $m->getPPPoEProfiles(),
                    default         => [],
                };
            });
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get just the names of PPPoE profiles from the first active router.
     * Used to populate the default MikroTik Profile dropdown.
     */
    private function getMikrotikProfiles(): array
    {
        try {
            $router = MikrotikRouter::where('is_active', 1)->first();
            if (!$router) {
                return [];
            }

            $mikrotik = new MikrotikService();
            $profiles = $mikrotik->withRouter($router, fn ($m) => $m->getPPPoEProfiles());

            return collect($profiles)->pluck('name')->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }
}
