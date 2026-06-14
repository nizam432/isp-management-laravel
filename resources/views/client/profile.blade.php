{{-- resources/views/client/profile.blade.php --}}
@extends('client.layout')
@section('title', 'Profile')

@section('content')

<div class="page-title">My Profile</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

    {{-- Profile Info --}}
    <div class="card">
        <div class="card-header"><i class="fas fa-user-circle"></i> Account Info</div>
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
                <div style="width:64px; height:64px; background:#1a1f36; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:700; color:#00c897; flex-shrink:0;">
                    {{ strtoupper(substr($customer->name, 0, 2)) }}
                </div>
                <div>
                    <div style="font-size:18px; font-weight:700; color:#1a1f36;">{{ $customer->name }}</div>
                    <div style="font-size:12px; color:#888;">ID: {{ $customer->customer_code }}</div>
                </div>
            </div>

            <table class="info-table">
                <tr>
                    <td>Full Name :</td>
                    <td>{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td>Mobile :</td>
                    <td>{{ $customer->phone }}</td>
                </tr>
                @if($customer->email)
                <tr>
                    <td>Email :</td>
                    <td>{{ $customer->email }}</td>
                </tr>
                @endif
                <tr>
                    <td>Address :</td>
                    <td>{{ $customer->address ?? '—' }}</td>
                </tr>
                @if($customer->nid_number)
                <tr>
                    <td>NID :</td>
                    <td>{{ $customer->nid_number }}</td>
                </tr>
                @endif
                <tr>
                    <td>Zone :</td>
                    <td>{{ $customer->zone->name ?? '—' }}{{ $customer->subZone ? ' / '.$customer->subZone->name : '' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- Connection Info --}}
    <div class="card">
        <div class="card-header"><i class="fas fa-wifi"></i> Connection Info</div>
        <div class="card-body">
            <table class="info-table">
                <tr>
                    <td>Package :</td>
                    <td>{{ $customer->package->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Monthly Bill :</td>
                    <td>Tk{{ number_format($customer->monthly_bill_amount ?: ($customer->package->price ?? 0), 0) }}</td>
                </tr>
                <tr>
                    <td>PPPoE User :</td>
                    <td><code style="background:#f4f6f9; padding:2px 8px; border-radius:4px; font-size:12px;">{{ $customer->pppoe_username ?? '—' }}</code></td>
                </tr>
                <tr>
                    <td>Connection :</td>
                    <td>{{ $customer->connectionType->name ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Connected :</td>
                    <td>{{ $customer->connection_date?->format('d M Y') ?? '—' }}</td>
                </tr>
                <tr>
                    <td>Status :</td>
                    <td>
                        @php
                            $statusMap = ['active'=>['badge-success','Active'],'suspended'=>['badge-danger','Suspended'],'expired'=>['badge-warning','Expired'],'inactive'=>['badge-secondary','Inactive']];
                            [$badgeClass,$statusText] = $statusMap[$customer->status] ?? ['badge-secondary', ucfirst($customer->status)];
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
                    </td>
                </tr>
                @if($customer->expire_date)
                <tr>
                    <td>Expire :</td>
                    <td class="{{ \Carbon\Carbon::parse($customer->expire_date)->isPast() ? 'expire-urgent' : '' }}">
                        {{ \Carbon\Carbon::parse($customer->expire_date)->format('d M Y H:i') }}
                    </td>
                </tr>
                @endif
            </table>
        </div>
    </div>

</div>

{{-- Change Password --}}
<div class="card" style="max-width:480px;">
    <div class="card-header"><i class="fas fa-lock"></i> Change Password</div>
    <div class="card-body">
        <form method="POST" action="{{ route('client.password.change') }}">
            @csrf
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password"
                    class="form-control {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                    placeholder="Current password" required>
                @error('current_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password"
                    class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                    placeholder="Min 6 characters" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="password_confirmation"
                    class="form-control" placeholder="Repeat new password" required>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Password
            </button>
        </form>
    </div>
</div>

{{-- Contact --}}
<div style="background:#f0faf7; border:1px solid #b0e8d0; border-radius:10px; padding:14px 18px; font-size:13px; color:#1a7a50; max-width:480px;">
    <i class="fas fa-headset"></i>
    <strong> Need help?</strong> Call us at <strong>{{ \App\Models\Setting::get('company_phone', 'N/A') }}</strong>
    @if(\App\Models\Setting::get('company_email'))
        or email <strong>{{ \App\Models\Setting::get('company_email') }}</strong>
    @endif
</div>

@endsection
