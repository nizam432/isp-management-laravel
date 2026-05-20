{{-- resources/views/super-admin/plans/index.blade.php --}}
@extends('layouts.app')
@section('page_title', 'Plan Management')
@section('page_content')

<div class="row">
    {{-- Plans List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags mr-1"></i> Plans</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Plan</th>
                            <th>Price</th>
                            <th>Customers</th>
                            <th>Routers</th>
                            <th>SMS</th>
                            <th>Reseller</th>
                            <th>Trial</th>
                            <th>ISPs</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plans as $plan)
                        <tr>
                            <td>
                                <strong>{{ $plan->name }}</strong>
                                <br><small class="text-muted">{{ $plan->description }}</small>
                            </td>
                            <td>৳{{ number_format($plan->price) }}</td>
                            <td>{{ $plan->max_customers_label }}</td>
                            <td>{{ $plan->max_routers_label }}</td>
                            <td>
                                <span class="badge badge-{{ $plan->sms_enabled ? 'success' : 'danger' }}">
                                    {{ $plan->sms_enabled ? '✓' : '✗' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-{{ $plan->reseller_enabled ? 'success' : 'danger' }}">
                                    {{ $plan->reseller_enabled ? '✓' : '✗' }}
                                </span>
                            </td>
                            <td>{{ $plan->trial_days }} দিন</td>
                            <td>{{ $plan->tenants_count }}</td>
                            <td>
                                <button class="btn btn-xs btn-warning"
                                        onclick="editPlan({{ $plan->toJson() }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('super-admin.plans.toggle', $plan) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-xs btn-{{ $plan->is_active ? 'danger' : 'success' }}">
                                        {{ $plan->is_active ? 'Disable' : 'Enable' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Add/Edit Plan Form --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title" id="formTitle"><i class="fas fa-plus mr-1"></i> Add Plan</h3>
            </div>
            <form id="planForm" action="{{ route('super-admin.plans.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="card-body">
                    <div class="form-group">
                        <label>Plan Name</label>
                        <input type="text" name="name" id="planName" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Slug</label>
                        <input type="text" name="slug" id="planSlug" class="form-control" required>
                        <small class="text-muted">unique, lowercase (e.g. basic-plus)</small>
                    </div>
                    <div class="form-group">
                        <label>Price (৳/মাস)</label>
                        <input type="number" name="price" id="planPrice" class="form-control" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Max Customers (-1 = Unlimited)</label>
                        <input type="number" name="max_customers" id="planMaxCustomers" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Max Routers (-1 = Unlimited)</label>
                        <input type="number" name="max_routers" id="planMaxRouters" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Trial Days</label>
                        <input type="number" name="trial_days" id="planTrialDays" class="form-control" min="0" value="0">
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="sms_enabled" id="planSms" class="form-check-input" value="1">
                        <label class="form-check-label">SMS Enabled</label>
                    </div>
                    <div class="form-check mb-2">
                        <input type="checkbox" name="reseller_enabled" id="planReseller" class="form-check-input" value="1">
                        <label class="form-check-label">Reseller Enabled</label>
                    </div>
                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" id="planDesc" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                        <i class="fas fa-save mr-1"></i> Save Plan
                    </button>
                    <button type="button" class="btn btn-secondary btn-block mt-1" onclick="resetForm()">
                        Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPlan(plan) {
    document.getElementById('formTitle').innerHTML = '<i class="fas fa-edit mr-1"></i> Edit Plan';
    document.getElementById('planForm').action = `/super-admin/plans/${plan.id}`;
    document.getElementById('formMethod').value = 'PUT';
    document.getElementById('planName').value = plan.name;
    document.getElementById('planSlug').value = plan.slug;
    document.getElementById('planSlug').readOnly = true;
    document.getElementById('planPrice').value = plan.price;
    document.getElementById('planMaxCustomers').value = plan.max_customers;
    document.getElementById('planMaxRouters').value = plan.max_routers;
    document.getElementById('planTrialDays').value = plan.trial_days;
    document.getElementById('planSms').checked = plan.sms_enabled;
    document.getElementById('planReseller').checked = plan.reseller_enabled;
    document.getElementById('planDesc').value = plan.description || '';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Update Plan';
    window.scrollTo(0, 0);
}

function resetForm() {
    document.getElementById('formTitle').innerHTML = '<i class="fas fa-plus mr-1"></i> Add Plan';
    document.getElementById('planForm').action = '{{ route("super-admin.plans.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('planForm').reset();
    document.getElementById('planSlug').readOnly = false;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save mr-1"></i> Save Plan';
}
</script>

@endsection
