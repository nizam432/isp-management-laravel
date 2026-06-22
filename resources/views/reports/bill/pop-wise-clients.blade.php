@extends('layouts.app')
@section('page_title', 'POP Wise Clients')
@section('page_actions')
    <a href="{{ route('reports.bill.pop-wise.pdf') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-file-pdf mr-1"></i> Generate PDF
    </a>
    <a href="{{ route('reports.bill.pop-wise.xlsx') }}" class="btn btn-success btn-sm">
        <i class="fas fa-file-excel mr-1"></i> Generate Excel
    </a>
@endsection
@section('page_content')
<style>
.cust-stat-card { border-radius:4px;color:#fff;padding:14px 16px;margin-bottom:16px;height:80px;display:flex;align-items:center;justify-content:space-between;overflow:hidden; }
.cust-stat-card .sc-left .sc-label { font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.85);margin-bottom:4px; }
.cust-stat-card .sc-left .sc-value { font-size:26px;font-weight:700;line-height:1;color:#fff; }
.cust-stat-card .sc-icon { font-size:52px;color:rgba(255,255,255,.18); }
.pop-badge { display:inline-block;min-width:34px;text-align:center;padding:3px 8px;border-radius:12px;font-size:13px;font-weight:700;border:2px solid; }
.pop-badge.total   { border-color:#17a2b8;color:#17a2b8; }
.pop-badge.active  { border-color:#00a65a;color:#00a65a; }
.pop-badge.expired { border-color:#dd4b39;color:#dd4b39; }
.pop-badge.left    { border-color:#f39c12;color:#f39c12; }
.pop-badge.pending { border-color:#605ca8;color:#605ca8; }
.pop-badge.pppoe   { border-color:#333;color:#333; }
.pop-badge.hotspot { border-color:#333;color:#333; }
.pop-badge.free    { border-color:#17a2b8;color:#17a2b8; }
.pop-badge.vip     { border-color:#dd4b39;color:#dd4b39; }
</style>

<div class="row mb-3">
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#17a2b8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-network-wired mr-1"></i> Total POPs</div>
                <div class="sc-value">{{ $rows->count() }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-network-wired"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#00a65a;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-users mr-1"></i> Total Clients</div>
                <div class="sc-value">{{ number_format($totals['total']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-users"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#605ca8;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-check mr-1"></i> Active</div>
                <div class="sc-value">{{ number_format($totals['active']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-check"></i></div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="cust-stat-card" style="background:#dd4b39;">
            <div class="sc-left">
                <div class="sc-label"><i class="fas fa-user-times mr-1"></i> Expired</div>
                <div class="sc-value">{{ number_format($totals['expired']) }}</div>
            </div>
            <div class="sc-icon"><i class="fas fa-user-times"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title"><i class="fas fa-sitemap mr-1"></i> POP Wise Clients</h3>
        <span class="badge badge-info">{{ $rows->count() }} POPs</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th>SL</th>
                        <th>Reseller</th>
                        <th class="text-center">Total</th>
                        <th class="text-center">Active</th>
                        <th class="text-center">Expired</th>
                        <th class="text-center">Left</th>
                        <th class="text-center">Pending</th>
                        <th class="text-center">PPPOE</th>
                        <th class="text-center">Hotspot</th>
                        <th class="text-center">Free</th>
                        <th class="text-center">VIP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $i => $row)
                    <tr>
                        <td class="text-muted small">{{ $i + 1 }}</td>
                        <td class="font-weight-bold">{{ $row['reseller'] }}</td>
                        <td class="text-center"><span class="pop-badge total">{{ $row['total'] }}</span></td>
                        <td class="text-center"><span class="pop-badge active">{{ $row['active'] }}</span></td>
                        <td class="text-center"><span class="pop-badge expired">{{ $row['expired'] }}</span></td>
                        <td class="text-center"><span class="pop-badge left">{{ $row['left'] }}</span></td>
                        <td class="text-center"><span class="pop-badge pending">{{ $row['pending'] }}</span></td>
                        <td class="text-center"><span class="pop-badge pppoe">{{ $row['pppoe'] }}</span></td>
                        <td class="text-center"><span class="pop-badge hotspot">{{ $row['hotspot'] }}</span></td>
                        <td class="text-center"><span class="pop-badge free">{{ $row['free'] }}</span></td>
                        <td class="text-center"><span class="pop-badge vip">{{ $row['vip'] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="11" class="text-center text-muted py-4">No POP/Reseller found.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold" style="background:#f8f9fa;">
                        <td colspan="2">{{ $rows->count() }} POPs Total</td>
                        <td class="text-center">{{ $totals['total'] }}</td>
                        <td class="text-center">{{ $totals['active'] }}</td>
                        <td class="text-center">{{ $totals['expired'] }}</td>
                        <td class="text-center">{{ $totals['left'] }}</td>
                        <td class="text-center">{{ $totals['pending'] }}</td>
                        <td class="text-center">{{ $totals['pppoe'] }}</td>
                        <td class="text-center">{{ $totals['hotspot'] }}</td>
                        <td class="text-center">{{ $totals['free'] }}</td>
                        <td class="text-center">{{ $totals['vip'] }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
