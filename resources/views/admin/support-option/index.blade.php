@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Support Options List</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Support Options</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="javascript:void(0)" class="btn btn-sm fw-bold btn-primary" data-bs-toggle="modal" data-bs-target="#supportOptionModal" id="addOptionBtn"><i class="fas fa-plus me-1"></i> Add Option</a>
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-body py-4">
                    <table class="table align-middle table-row-dashed fs-6 gy-5" id="dataTable">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Sl.No</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Label</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($details as $key => $item)
                                <tr>
                                    <td>{{ $details->firstItem() + $key }}</td>
                                    <td>{{ ucwords(str_replace('_', ' ', $item->type)) }}</td>
                                    <td><span class="text-gray-600 fw-bold">{{ $item->currency }} {{ $item->amount }}</span></td>
                                    <td>{{ $item->label }}</td>
                                    <td class="text-end">
                                        <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 editOptionBtn" data-id="{{ $item->id }}" data-type="{{ $item->type }}" data-amount="{{ $item->amount }}" data-currency="{{ $item->currency }}" data-label="{{ $item->label }}" title="Edit"><i class="fas fa-pencil-alt fs-4"></i></a>
                                        <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm deleteData" data-uuid="{{ $item->uuid }}" data-table="support_options" title="Delete"><i class="fas fa-trash fs-4"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No support options found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="d-flex justify-content-end mt-5">
                        {{ $details->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="supportOptionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form class="form formSubmit" action="{{ route('admin.support-option.add') }}" method="POST" id="supportOptionForm">
                    @csrf
                    <div class="modal-header" id="supportOptionModal_header">
                        <h2 class="fw-bold" id="modalTitle">Add Support Option</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <input type="hidden" name="id" id="option_id" value="">
                        
                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label required">Type</label>
                                <select name="type" id="option_type" class="form-select form-select-solid" required>
                                    <option value="">Select Type</option>
                                    <option value="one_time">One Time</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="one_time">Add On</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Amount</label>
                                <input type="number" step="0.01" name="amount" id="option_amount" class="form-control form-control-solid" placeholder="e.g. 10.00" required />
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label required">Currency</label>
                                <select name="currency" id="option_currency" class="form-select form-select-solid" required>
                                    <option value="CHF">CHF</option>
                                    <option value="USD">USD</option>
                                    <option value="EUR">EUR</option>
                                    <option value="GBP">GBP</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Label</label>
                                <input type="text" name="label" id="option_label" class="form-control form-control-solid" placeholder="e.g. CHF 10 / Month" required />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="reset" id="supportOptionModal_cancel" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" id="supportOptionModal_submit" class="btn btn-primary">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            $('#addOptionBtn').click(function() {
                $('#modalTitle').text('Add Support Option');
                $('#supportOptionForm')[0].reset();
                $('#option_id').val('');
            });

            $('.editOptionBtn').click(function() {
                $('#modalTitle').text('Edit Support Option');
                $('#option_id').val($(this).data('id'));
                $('#option_type').val($(this).data('type'));
                $('#option_amount').val($(this).data('amount'));
                $('#option_currency').val($(this).data('currency'));
                $('#option_label').val($(this).data('label'));
                
                $('#supportOptionModal').modal('show');
            });
        });
    </script>
@endpush
