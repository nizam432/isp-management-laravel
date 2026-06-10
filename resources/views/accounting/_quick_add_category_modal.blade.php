{{-- resources/views/accounting/_quick_add_category_modal.blade.php --}}

<div class="modal fade" id="quickAddCategoryModal" tabindex="-1" data-backdrop="false" style="z-index:99999;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title" id="quickAddCategoryTitle">
                    <i class="fas fa-plus mr-1"></i> Add Category
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">

                {{-- Name --}}
                <div class="form-group">
                    <label class="font-weight-bold small">Name <span class="text-danger">*</span></label>
                    <input type="text"
                           id="quickCategoryName"
                           class="form-control form-control-sm"
                           placeholder="e.g. Connection Fee"
                           maxlength="100">
                    <div class="invalid-feedback" id="quickCategoryNameError"></div>
                </div>

                {{-- Color + Icon --}}
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Color</label>
                            <input type="color"
                                   id="quickCategoryColor"
                                   class="form-control"
                                   value="#185FA5"
                                   style="height:36px;">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold small">Icon (FontAwesome class)</label>
                            <input type="text"
                                   id="quickCategoryIcon"
                                   class="form-control form-control-sm"
                                   placeholder="fas fa-box">
                        </div>
                    </div>
                </div>

                {{-- Description --}}
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Description</label>
                    <textarea id="quickCategoryDescription"
                              rows="2"
                              class="form-control form-control-sm"
                              placeholder="Short description..."></textarea>
                </div>

            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary btn-sm" id="btnSaveQuickCategory">
                    <i class="fas fa-save mr-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('css')
<style>
#quickAddCategoryModal { z-index: 99999 !important; }
#quickAddCategoryModal + .modal-backdrop { z-index: 99998 !important; }
</style>
@endpush

@push('js')
<script>
var quickCategoryType   = null;
var quickCategoryTarget = null;

$(document).on('click', '.btn-quick-add-category', function () {
    quickCategoryType   = $(this).data('type');
    quickCategoryTarget = $(this).data('target');

    var label = quickCategoryType === 'income' ? 'Income' : 'Expense';
    $('#quickAddCategoryTitle').html('<i class="fas fa-plus mr-1"></i> Add ' + label + ' Category');

    // Reset fields
    $('#quickCategoryName').val('').removeClass('is-invalid');
    $('#quickCategoryNameError').text('');
    $('#quickCategoryColor').val('#185FA5');
    $('#quickCategoryIcon').val('');
    $('#quickCategoryDescription').val('');

    // Move to body to avoid z-index stacking issues inside other modals
    $('body').append($('#quickAddCategoryModal'));
    $('#quickAddCategoryModal').modal('show');

    $('#quickAddCategoryModal').one('shown.bs.modal', function () {
        $('#quickCategoryName').focus();
    });
});

// Save on Enter key
$('#quickCategoryName').on('keypress', function (e) {
    if (e.which === 13) $('#btnSaveQuickCategory').click();
});

$('#btnSaveQuickCategory').on('click', function () {
    var name = $('#quickCategoryName').val().trim();

    if (!name) {
        $('#quickCategoryName').addClass('is-invalid');
        $('#quickCategoryNameError').text('Category name is required.');
        return;
    }

    var btn = $(this).prop('disabled', true)
                     .html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

    var url = quickCategoryType === 'income'
        ? '{{ route("accounting.income-categories.quick-add") }}'
        : '{{ route("accounting.expense-categories.quick-add") }}';

    $.ajax({
        url:    url,
        method: 'POST',
        data: {
            _token:      '{{ csrf_token() }}',
            name:        name,
            color:       $('#quickCategoryColor').val(),
            icon:        $('#quickCategoryIcon').val().trim(),
            description: $('#quickCategoryDescription').val().trim(),
        },
        success: function (res) {
            if (res.success) {
                $('#' + quickCategoryTarget)
                    .append('<option value="' + res.category.id + '">' + res.category.name + '</option>')
                    .val(res.category.id);

                $('#quickAddCategoryModal').modal('hide');
                toastr.success(res.message);
            }
        },
        error: function (xhr) {
            var errors = xhr.responseJSON?.errors || {};
            if (errors.name) {
                $('#quickCategoryName').addClass('is-invalid');
                $('#quickCategoryNameError').text(errors.name[0]);
            } else {
                toastr.error(xhr.responseJSON?.message || 'Could not save category.');
            }
        },
        complete: function () {
            btn.prop('disabled', false)
               .html('<i class="fas fa-save mr-1"></i> Save');
        }
    });
});
</script>
@endpush
@endonce
