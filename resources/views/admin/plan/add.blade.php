@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">{{ $detail ? 'Edit' : 'Add' }} Plan</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.plan.list') }}" class="text-muted text-hover-primary">Plans</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">{{ $detail ? 'Edit' : 'Add' }}</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.plan.list') }}" class="btn btn-sm fw-bold btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <form id="planForm" class="form formSubmit" method="POST" action="{{ route('admin.plan.add', ['uuid' => $detail->uuid ?? '']) }}">
                @csrf
                <input type="hidden" name="id" value="{{ $detail->id ?? '' }}">
                <div class="card card-flush shadow-sm">
                    <div class="card-body">
                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label required">Plan Name</label>
                                <input type="text" name="name" class="form-control form-control-solid" placeholder="e.g. PM Plus" value="{{ $detail->name ?? '' }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Billing Cycle</label>
                                <select name="billing_cycle" class="form-select form-select-solid" required>
                                    <option value="MONTHLY" {{ ($detail->billing_cycle ?? '') == 'MONTHLY' ? 'selected' : '' }}>Monthly</option>
                                    <option value="YEARLY" {{ ($detail->billing_cycle ?? '') == 'YEARLY' ? 'selected' : '' }}>Yearly</option>
                                    <option value="ONE_TIME" {{ ($detail->billing_cycle ?? '') == 'ONE_TIME' ? 'selected' : '' }}>One-Time</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label required">Price</label>
                                <input type="number" step="0.01" name="price" class="form-control form-control-solid" placeholder="9.90" value="{{ $detail->price ?? '' }}" required />
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Currency</label>
                                <select name="currency" class="form-select form-select-solid" required>
                                    <option value="USD" {{ ($detail->currency ?? '') == 'USD' ? 'selected' : '' }}>USD</option>
                                    <option value="EUR" {{ ($detail->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR</option>
                                    <option value="GBP" {{ ($detail->currency ?? '') == 'GBP' ? 'selected' : '' }}>GBP</option>
                                    <option value="CHF" {{ ($detail->currency ?? 'CHF') == 'CHF' ? 'selected' : '' }}>CHF</option>
                                    <option value="CAD" {{ ($detail->currency ?? '') == 'CAD' ? 'selected' : '' }}>CAD</option>
                                    <option value="AUD" {{ ($detail->currency ?? '') == 'AUD' ? 'selected' : '' }}>AUD</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-12">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" id="description" class="form-control form-control-solid" rows="3">{{ $detail->description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-12">
                                <label class="form-label required">Active Status</label>
                                <select name="is_active" class="form-select form-select-solid" required>
                                    <option value="1" {{ ($detail->is_active ?? 1) == 1 ? 'selected' : '' }}>Active - Visible to Users</option>
                                    <option value="0" {{ ($detail->is_active ?? 1) == 0 ? 'selected' : '' }}>Inactive - Hidden</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="card-footer d-flex justify-content-end py-6 px-9">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <span class="indicator-label">Save Plan</span>
                            <span class="indicator-progress">Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/custom_js/cdn/ckeditor.js') }}"></script>
    <script>
        $(document).ready(function() {
            let descriptionEditorInstance;
            ClassicEditor
                .create(document.querySelector('#description'))
                .then(editor => {
                    descriptionEditorInstance = editor;
                    editor.model.document.on('change:data', () => {
                        document.querySelector('#description').value = editor.getData();
                    });
                })
                .catch(error => {
                    console.error('Error initializing CKEditor', error);
                });
        });
    </script>
@endpush
