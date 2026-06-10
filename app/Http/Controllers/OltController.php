<?php

namespace App\Http\Controllers;

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

    /** GET /olt/{olt} — edit modal এর জন্য */
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
        try {
            // TODO: SNMP / Telnet দিয়ে OLT থেকে ONU data fetch করার logic এখানে
            $olt->update(['last_synced_at' => now()]);

            return response()->json([
                'success' => true,
                'message' => "OLT [{$olt->ip_address}] sync সম্পন্ন।",
            ]);
        } catch (\Exception $e) {
            Log::error("OLT sync failed [{$olt->ip_address}]: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** POST /olt/sync-all */
    public function syncAll(): JsonResponse
    {
        $olts  = Olt::active()->get();
        $count = 0;

        foreach ($olts as $olt) {
            try {
                $olt->update(['last_synced_at' => now()]);
                $count++;
            } catch (\Exception $e) {
                Log::error("OLT sync failed [{$olt->ip_address}]: " . $e->getMessage());
            }
        }

        return response()->json(['success' => true, 'message' => "{$count}টি OLT sync হয়েছে।"]);
    }

    // ══════════════════════════════════════════
    // OLT USERS (ONU List)
    // ══════════════════════════════════════════

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
