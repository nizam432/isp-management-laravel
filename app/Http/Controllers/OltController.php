<?php
namespace App\Http\Controllers;
use App\Services\Olt\OltSyncService;

use App\Models\Olt;
use App\Models\OltType;
use App\Models\OltUser;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class OltController extends Controller
{
    // ══════════════════════════════════════════
    // OLT CRUD
    // ══════════════════════════════════════════

    /** GET /olt */
    public function index()
    {
        $olts     = Olt::with('oltType')->withCount('oltUsers')->latest()->get();
        $oltTypes = OltType::active()->orderBy('name')->get();
        return view('olt.index', compact('olts', 'oltTypes'));
    }

    /** POST /olt */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'ip_address'   => 'required|string|max:100',
            'community'    => 'nullable|string|max:100',
            'olt_type_id'  => 'required|exists:olt_types,id',
            'web_ip'       => 'nullable|string|max:100',
            'web_username' => 'nullable|string|max:100',
            'web_password' => 'nullable|string|max:255',
        ]);

        $olt = Olt::create([
            'ip_address'   => $request->ip_address,
            'community'    => $request->community ?? 'public',
            'olt_type_id'  => $request->olt_type_id,
            'web_ip'       => $request->web_ip,
            'web_username' => $request->web_username ?? 'admin',
            'web_password' => $request->web_password,
            'is_active'    => true,
            'created_by'   => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OLT সফলভাবে যোগ হয়েছে।',
            'olt'     => $olt->load('oltType'),
        ]);
    }

    /** GET /olt/{olt} — return OLT data for the edit modal. */
    public function show(Olt $olt): JsonResponse
    {
        return response()->json(['success' => true, 'olt' => $olt->load('oltType')]);
    }

    /** PUT /olt/{olt} */
    public function update(Request $request, Olt $olt): JsonResponse
    {
        $request->validate([
            'ip_address'   => 'required|string|max:100',
            'community'    => 'nullable|string|max:100',
            'olt_type_id'  => 'required|exists:olt_types,id',
            'web_ip'       => 'nullable|string|max:100',
            'web_username' => 'nullable|string|max:100',
            'web_password' => 'nullable|string|max:255',
        ]);

        $olt->update($request->only([
            'ip_address', 'community', 'olt_type_id',
            'web_ip', 'web_username', 'web_password',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'OLT আপডেট হয়েছে।',
            'olt'     => $olt->fresh(['oltType']),
        ]);
    }

    /** DELETE /olt/{olt} */
    public function destroy(Olt $olt): JsonResponse
    {
        $olt->delete();
        return response()->json(['success' => true, 'message' => 'OLT মুছে ফেলা হয়েছে।']);
    }

    /** POST /olt/{olt}/sync */
    public function sync(Olt $olt): JsonResponse
    {
        if (! $this->pingHost($olt->ip_address)) {
            Log::warning("OLT ping failed [{$olt->ip_address}]");
            return response()->json([
                'success' => false,
                'message' => "OLT [{$olt->ip_address}] reachable নয় — ping failed।",
            ], 422);
        }

        try {
            $result = (new OltSyncService())->sync($olt);

            $methodLabel = $result['method'] === 'snmp' ? 'SNMP' : 'Web Scraping';

            return response()->json([
                'success' => true,
                'message' => "OLT [{$olt->ip_address}] sync সম্পন্ন ({$methodLabel}) — "
                           . "মোট {$result['total']} ONU | নতুন: {$result['saved']} | আপডেট: {$result['updated']}",
            ]);

        } catch (\Exception $e) {
            Log::error("OLT sync failed [{$olt->ip_address}]: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => "Sync ব্যর্থ: " . $e->getMessage(),
            ], 500);
        }
    }

    /** POST /olt/sync-all */
    public function syncAll(): JsonResponse
    {
        $olts    = Olt::active()->get();
        $success = 0;
        $failed  = [];

        foreach ($olts as $olt) {
            if (! $this->pingHost($olt->ip_address)) {
                $failed[] = $olt->ip_address . ' (unreachable)';
                continue;
            }

            try {
                (new OltSyncService())->sync($olt);
                $success++;
            } catch (\Exception $e) {
                $failed[] = $olt->ip_address . ' (' . $e->getMessage() . ')';
                Log::error("OLT sync-all failed [{$olt->ip_address}]: " . $e->getMessage());
            }
        }

        $msg = "{$success}টি OLT sync হয়েছে।";
        if (count($failed)) {
            $msg .= ' ব্যর্থ: ' . implode(', ', $failed);
        }

        return response()->json(['success' => true, 'message' => $msg]);
    }

    // ══════════════════════════════════════════
    /**
     * Check whether a host is reachable.
     * IP:Port → TCP connect via fsockopen. IP only → ICMP ping.
     */
    private function pingHost(string $input): bool
    {
        if (str_contains($input, ':')) {
            [$ip, $port] = explode(':', $input, 2);
            $port = (int) $port;
        } else {
            $ip   = $input;
            $port = null;
        }

        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        if ($port) {
            $conn = @fsockopen($ip, $port, $errno, $errstr, 3);
            if ($conn) {
                fclose($conn);
                return true;
            }
            return false;
        }

        $cmd = str_starts_with(strtolower(PHP_OS), 'win')
            ? 'ping -n 1 -w 2000 ' . escapeshellarg($ip)
            : 'ping -c 1 -W 2 '    . escapeshellarg($ip);

        exec($cmd, $output, $exitCode);

        return $exitCode === 0;
    }

    /** GET /olt/users */
    public function users(Request $request)
    {
        $oltList = Olt::active()->with('oltType')->get();

        $stats = [
            'online'      => OltUser::online()->count(),
            'offline'     => OltUser::offline()->count(),
            'weak_signal' => OltUser::weakSignal()->count(),
            'total_olt'   => Olt::count(),
        ];

        return view('olt.users', compact('stats', 'oltList'));
    }

    /** GET /olt/users/data — AJAX DataTable */
    public function usersData(Request $request): JsonResponse
    {
        $query = OltUser::with(['olt.oltType', 'customer'])
            ->when($request->status, fn($q) => $q->where('onu_status', $request->status))
            ->when($request->olt_id, fn($q) => $q->where('olt_id', $request->olt_id))
            ->when($request->dbm, fn($q) => match ($request->dbm) {
                'excellent' => $q->where('optical_power', '>=', -20),
                'good'      => $q->whereBetween('optical_power', [-24, -20]),
                'weak'      => $q->whereBetween('optical_power', [-27, -24]),
                'very_weak' => $q->where('optical_power', '<', -27),
                default     => $q,
            });

        $users = $query->latest('last_synced_at')->get()->map(fn($u) => [
            'id'                   => $u->id,
            'client_code'          => $u->customer?->customer_code ?? '-',
            'username'             => $u->customer?->pppoe_username ?? '-',
            'client_name'          => $u->customer?->name ?? '-',
            'mac_address'          => $u->mac_address ?? '-',
            'ip_address'           => $u->ip_address ?? '-',
            'olt_name'             => $u->olt?->ip_address ?? '-',
            'optical_power'        => $u->optical_power,
            'onu_mac_address'      => $u->onu_mac_address ?? '-',
            'olt_port'             => $u->olt_port ?? '-',
            'onu_status'           => $u->onu_status,
            'description'          => $u->description ?? '-',
            'last_deregister_time' => $u->last_deregister_time?->format('d M Y H:i') ?? '-',
            'distance'             => $u->distance ? $u->distance . ' m' : '-',
            'deregister_reason'    => $u->deregister_reason ?? '-',
            'last_synced_at'       => $u->last_synced_at?->format('d M Y H:i') ?? '-',
            'signal_badge'         => $u->signal_badge,
        ]);

        return response()->json(['data' => $users, 'count' => $users->count()]);
    }
}
