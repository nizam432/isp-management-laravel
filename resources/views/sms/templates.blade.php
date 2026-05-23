{{-- resources/views/sms/templates.blade.php --}}
@extends('layouts.app')
@section('page_title', 'SMS Templates')
@section('page_content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
    </div>
@endif

<div class="row">

    {{-- Left: Template List --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-alt mr-1"></i> Templates</h3>
                <div class="card-tools">
                    <span class="badge badge-info">{{ $templates->count() }} টি template</span>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th>Title</th>
                            <th>Message Preview</th>
                            <th>Status</th>
                            <th style="width:110px">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $tpl)
                        <tr>
                            <td><strong>{{ $tpl->title }}</strong></td>
                            <td>
                                <small class="text-muted">{{ Str::limit($tpl->body, 70) }}</small>
                            </td>
                            <td>
                                <span class="badge badge-{{ $tpl->is_active ? 'success' : 'secondary' }}">
                                    {{ $tpl->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-xs btn-warning"
                                        onclick="editTemplate({{ $tpl->toJson() }})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('sms.templates.toggle', $tpl) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit"
                                            class="btn btn-xs btn-{{ $tpl->is_active ? 'secondary' : 'success' }}"
                                            title="{{ $tpl->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ $tpl->is_active ? 'pause' : 'play' }}"></i>
                                    </button>
                                </form>
                                <form action="{{ route('sms.templates.destroy', $tpl) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-danger"
                                            onclick="return confirm('এই template মুছবেন?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                <i class="fas fa-file-alt fa-2x d-block mb-2"></i>
                                কোনো template নেই। ডানে form থেকে তৈরি করুন।
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Variable Guide --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-code mr-1"></i> Available Variables</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-2">
                    Message-এ এই variables ব্যবহার করলে SMS পাঠানোর সময় auto replace হবে।
                    Click করলে copy হবে।
                </p>
                <div class="row">
                    @foreach(\App\Models\SmsTemplate::VARIABLES as $var => $label)
                    <div class="col-md-4 mb-2">
                        <code class="text-primary copy-var px-1"
                              style="cursor:pointer; border:1px dashed #007bff; border-radius:3px"
                              title="Click to copy">{{ $var }}</code>
                        <small class="text-muted ml-1">{{ $label }}</small>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Create / Edit Form --}}
    <div class="col-md-4">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title" id="formTitle">
                    <i class="fas fa-plus mr-1"></i> New Template
                </h3>
            </div>
            <form id="templateForm" action="{{ route('sms.templates.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="card-body">

                    <div class="form-group">
                        <label class="font-weight-bold">Template Title</label>
                        <input type="text" name="title" id="tplTitle"
                               class="form-control" placeholder="যেমন: Bill Due Notice" required>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold">Message Body</label>
                        <textarea name="body" id="tplBody" class="form-control" rows="6"
                                  maxlength="500" required
                                  placeholder="প্রিয় {name}, আপনার {month} মাসের বিল {amount} টাকা বাকি আছে।"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted">Variables use করতে পারো</small>
                            <small class="text-muted"><span id="charCount">0</span>/500</small>
                        </div>
                    </div>

                    {{-- Live Preview --}}
                    <div class="form-group">
                        <label class="font-weight-bold small">
                            <i class="fas fa-eye mr-1"></i> Live Preview
                        </label>
                        <div id="preview"
                             class="p-2 bg-light rounded border small"
                             style="min-height:55px; white-space:pre-wrap; color:#333;">
                            Message preview এখানে দেখাবে...
                        </div>
                    </div>

                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                        <i class="fas fa-save mr-1"></i> Save Template
                    </button>
                    <button type="button" class="btn btn-secondary btn-block btn-sm mt-1 d-none"
                            id="cancelBtn" onclick="resetForm()">
                        <i class="fas fa-times mr-1"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection

@push('js')
<script>
const sampleData = {
    name: 'রহিম সাহেব', mobile: '01712345678',
    amount: '৫০০', date: '{{ now()->format("d M Y") }}',
    month: '{{ now()->format("F Y") }}',
    package: 'Home 10Mbps', company: 'ABC Internet'
};

document.getElementById('tplBody').addEventListener('input', function() {
    document.getElementById('charCount').textContent = this.value.length;
    updatePreview(this.value);
});

function updatePreview(text) {
    let preview = text;
    Object.entries(sampleData).forEach(([key, val]) => {
        preview = preview.replaceAll('{' + key + '}', '<strong class="text-success">' + val + '</strong>');
    });
    document.getElementById('preview').innerHTML = preview || '<span class="text-muted">Message preview এখানে দেখাবে...</span>';
}

function editTemplate(tpl) {
    document.getElementById('formTitle').innerHTML  = '<i class="fas fa-edit mr-1"></i> Edit Template';
    document.getElementById('formMethod').value     = 'PUT';
    document.getElementById('tplTitle').value       = tpl.title;
    document.getElementById('tplBody').value        = tpl.body;
    document.getElementById('charCount').textContent = tpl.body.length;
    document.getElementById('submitBtn').innerHTML  = '<i class="fas fa-save mr-1"></i> Update Template';
    document.getElementById('cancelBtn').classList.remove('d-none');
    document.getElementById('templateForm').action  = '/sms/templates/' + tpl.id;
    updatePreview(tpl.body);
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetForm() {
    document.getElementById('formTitle').innerHTML  = '<i class="fas fa-plus mr-1"></i> New Template';
    document.getElementById('formMethod').value     = 'POST';
    document.getElementById('templateForm').action  = '{{ route("sms.templates.store") }}';
    document.getElementById('templateForm').reset();
    document.getElementById('charCount').textContent = '0';
    document.getElementById('preview').innerHTML    = '<span class="text-muted">Message preview এখানে দেখাবে...</span>';
    document.getElementById('submitBtn').innerHTML  = '<i class="fas fa-save mr-1"></i> Save Template';
    document.getElementById('cancelBtn').classList.add('d-none');
}

document.querySelectorAll('.copy-var').forEach(function(el) {
    el.addEventListener('click', function() {
        navigator.clipboard.writeText(this.textContent.trim()).then(() => {
            this.style.background = '#d4edda';
            setTimeout(() => this.style.background = '', 700);
        });
    });
});
</script>
@endpush
