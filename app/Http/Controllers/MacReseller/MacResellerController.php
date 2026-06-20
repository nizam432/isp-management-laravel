<?php

namespace App\Http\Controllers\MacReseller;

use App\Http\Controllers\Controller;
use App\Models\MacReseller;
use App\Models\MacResellerTariff;
use App\Models\Zone;
use Devfaysal\BangladeshGeocode\Models\District;
use Devfaysal\BangladeshGeocode\Models\Upazila;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class MacResellerController extends Controller
{
    const MENUS = [
        'CONFIGURATION', 'MIKROTIK CLIENT', 'EMPLOYEES', 'CLIENT',
        'BILLING', 'MONITORING', 'CLIENT SUPPORT', 'SMS SERVICE',
        'REPORT', 'FUND HISTORY', 'TUTORIALS',
    ];

    /**
     * Add/Edit MAC Reseller form থেকে Zone dropdown-এর পাশের "+" বাটনে
     * quick-add করার জন্য — পুরো page reload ছাড়াই নতুন Zone তৈরি করে
     * dropdown-এ যোগ করে দেয় (AJAX)।
     */
    public function quickAddZone(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:zones,name',
        ]);

        $zone = Zone::create([
            'name'      => $data['name'],
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'id'      => $zone->id,
            'name'    => $zone->name,
        ]);
    }

    /**
     * District select করলে AJAX দিয়ে সেই District-এর সব Upazila লোড করে
     * (devfaysal/laravel-bangladesh-geocode package থেকে)।
     */
    public function getUpazilas(Request $request)
    {
        $request->validate(['district_id' => 'required|integer']);

        $upazilas = Upazila::where('district_id', $request->district_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($upazilas);
    }

    /**
     * Add/Edit MAC Reseller form থেকে Upazila dropdown-এর পাশের "+" বাটনে
     * quick-add করার জন্য। নোট: bangladesh-geocode প্যাকেজের Upazila টেবিল
     * একটা নির্দিষ্ট District-এর অধীনে থাকে, তাই district_id লাগবে।
     */
    public function quickAddUpazila(Request $request)
    {
        $data = $request->validate([
            'district_id' => 'required|integer|exists:districts,id',
            'name'        => 'required|string|max:255',
        ]);

        $upazila = Upazila::create([
            'district_id' => $data['district_id'],
            'name'        => $data['name'],
        ]);

        return response()->json([
            'success' => true,
            'id'      => $upazila->id,
            'name'    => $upazila->name,
        ]);
    }

    public function index(Request $request)
    {
        $query = MacReseller::with('tariff')->withCount([
            'fundings as clients_running'  => fn($q) => $q->where('transaction_status', 'paid'),
        ]);

        if ($request->pop_type)       $query->where('pop_type', $request->pop_type);
        if ($request->pop_status)     $query->where('is_active', $request->pop_status === 'active');
        if ($request->client_enabled) $query->where('client_enabled', $request->client_enabled === '1');
        if ($request->login_status)   $query->where('is_locked', $request->login_status === 'locked');
        if ($request->creation_from)  $query->whereDate('created_at', '>=', $request->creation_from);
        if ($request->creation_to)    $query->whereDate('created_at', '<=', $request->creation_to);

        $resellers = $query->latest()->paginate(25);

        $totalPops        = MacReseller::count();
        $totalPopClients  = 0; // Customer count (from Mikrotik or billing)
        $onlineClients    = 0;

        return view('mac-reseller.reseller.index', compact(
            'resellers', 'totalPops', 'totalPopClients', 'onlineClients'
        ));
    }

    public function create()
    {
        $tariffs   = MacResellerTariff::where('is_active', true)->orderBy('name')->get();
        $zones     = Zone::active()->orderBy('name')->get();
        $districts = District::orderBy('name')->get(['id', 'name']);
        $menus     = self::MENUS;
        $nextCode  = MacReseller::generateCode();
        return view('mac-reseller.reseller.create', compact('tariffs', 'zones', 'districts', 'menus', 'nextCode'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'contact_person'                  => 'required|string|max:255',
            'email'                           => 'nullable|email',
            'mobile'                          => 'required|string|max:20',
            'phone'                           => 'nullable|string|max:20',
            'national_id'                     => 'nullable|string|max:50',
            'district'                        => 'nullable|string',
            'upazila'                         => 'nullable|string',
            'zone'                            => 'nullable|string',
            'pop_prefix'                      => 'nullable|string|max:20',
            'use_prefix_in_mikrotik_username' => 'nullable|boolean',
            'pop_type'                        => 'required|in:prepaid,postpaid',
            'min_rechargeable_amount'         => 'required|numeric|min:0',
            'address'                         => 'required|string',
            'logo'                            => 'nullable|image|max:2048',
            'business_name'                   => 'required|string|max:255',
            'tariff_id'                       => 'nullable|exists:mac_reseller_tariffs,id',
            'want_to_disable_clients'         => 'nullable|boolean',
            'min_balance'                     => 'required|numeric|min:0',
            'username'                        => 'required|string|unique:mac_resellers,username',
            'password'                        => 'required|string|min:6|confirmed',
            'allowed_menus'                   => 'nullable|array',
        ]);

        // Logo upload
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('mac-reseller/logos', 'public');
        }

        $data['password']    = Hash::make($data['password']);
        $data['code']        = MacReseller::generateCode();
        $data['created_by']  = auth()->id();
        $data['allowed_menus'] = $request->input('allowed_menus', []);

        MacReseller::create($data);

        return redirect()->route('mac-reseller.list.index')
            ->with('success', 'MAC Reseller added successfully.');
    }

    public function edit(MacReseller $macReseller)
    {
        $tariffs   = MacResellerTariff::where('is_active', true)->orderBy('name')->get();
        $zones     = Zone::active()->orderBy('name')->get();
        $districts = District::orderBy('name')->get(['id', 'name']);

        // বর্তমান reseller-এর district অনুযায়ী আগে থেকেই upazila list লোড করছি
        $currentDistrict = District::where('name', $macReseller->district)->first();
        $upazilas = $currentDistrict
            ? Upazila::where('district_id', $currentDistrict->id)->orderBy('name')->get(['id', 'name'])
            : collect();

        $menus = self::MENUS;
        return view('mac-reseller.reseller.edit', compact('macReseller', 'tariffs', 'zones', 'districts', 'upazilas', 'menus'));
    }

    public function update(Request $request, MacReseller $macReseller)
    {
        $data = $request->validate([
            'contact_person'                  => 'required|string|max:255',
            'email'                           => 'nullable|email',
            'mobile'                          => 'required|string|max:20',
            'phone'                           => 'nullable|string|max:20',
            'national_id'                     => 'nullable|string|max:50',
            'district'                        => 'nullable|string',
            'upazila'                         => 'nullable|string',
            'zone'                            => 'nullable|string',
            'pop_prefix'                      => 'nullable|string|max:20',
            'use_prefix_in_mikrotik_username' => 'nullable|boolean',
            'pop_type'                        => 'required|in:prepaid,postpaid',
            'min_rechargeable_amount'         => 'required|numeric|min:0',
            'address'                         => 'required|string',
            'logo'                            => 'nullable|image|max:2048',
            'business_name'                   => 'required|string|max:255',
            'tariff_id'                       => 'nullable|exists:mac_reseller_tariffs,id',
            'want_to_disable_clients'         => 'nullable|boolean',
            'min_balance'                     => 'required|numeric|min:0',
            'username'                        => 'required|string|unique:mac_resellers,username,' . $macReseller->id,
            'password'                        => 'nullable|string|min:6|confirmed',
            'allowed_menus'                   => 'nullable|array',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('mac-reseller/logos', 'public');
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['allowed_menus'] = $request->input('allowed_menus', []);
        $macReseller->update($data);

        return redirect()->route('mac-reseller.list.index')
            ->with('success', 'MAC Reseller updated successfully.');
    }

    public function toggleClientEnabled(MacReseller $macReseller)
    {
        $macReseller->update(['client_enabled' => !$macReseller->client_enabled]);
        return response()->json(['success' => true]);
    }

    public function toggleFundStart(MacReseller $macReseller)
    {
        $macReseller->update(['fund_start' => !$macReseller->fund_start]);
        return response()->json(['success' => true]);
    }

    public function toggleLocked(MacReseller $macReseller)
    {
        $macReseller->update(['is_locked' => !$macReseller->is_locked]);
        return response()->json(['success' => true]);
    }
}
