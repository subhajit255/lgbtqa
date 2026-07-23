@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Badge Color {{ !empty($details) ? 'Edit' : 'Add' }}</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.badge-color.list') }}"
                                    class="text-muted text-hover-primary">Badge Color</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body pt-6">
                            <div class="container">
                                <form id="badgeColorForm" action="{{ route('admin.badge-color.add') }}" method="POST"
                                    class="formSubmit" enctype="multipart/form-data">
                                    <input type="hidden" name="id" value="{{ $details->id ?? null }}">
                                    <div class="row pt-2">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name" class="label-style">Name</label>
                                                <span class="asterisk_sign">*</span>
                                                <input type="text" class="form-control"
                                                    placeholder="Enter Name (e.g. Blue, LGBTQ Gradient)" name="name" id="name"
                                                    value="{{ $details->name ?? null }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="color_code" class="label-style">Color Code / Gradient</label>
                                                <span class="asterisk_sign">*</span>
                                                <div class="input-group">
                                                    <input type="text" class="form-control"
                                                        placeholder="e.g. #0000FF or linear-gradient(...)" name="color_code" id="color_code"
                                                        value="{{ $details->color_code ?? '#00AABB' }}">
                                                    <input type="color" class="form-control form-control-color" id="color_picker" 
                                                        value="{{ (isset($details->color_code) && strpos($details->color_code, '#') === 0) ? $details->color_code : '#00AABB' }}" 
                                                        title="Choose a color" style="max-width: 50px; padding: 0; height: 43px;">
                                                </div>
                                                <div class="form-text">Enter a hex code or a CSS gradient string.</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-4">
                                        <div class="col-md-12">
                                            <label class="label-style">Preview</label>
                                            <div id="color_preview" style="width: 100px; height: 50px; border-radius: 8px; border: 2px solid #ddd; background: {{ $details->color_code ?? '#00AABB' }};"></div>
                                        </div>
                                    </div>

                                    <div class="button add-btn-div-save-style pt-8">
                                        <button type="submit" id="submitBtn" class="btn btn-dark">
                                            <span
                                                class="indicator-label">{{ !empty($details) ? 'Update' : 'Save' }}</span>
                                            <span class="indicator-progress">Please wait...
                                                <span
                                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const colorCodeInput = document.getElementById('color_code');
        const colorPicker = document.getElementById('color_picker');
        const colorPreview = document.getElementById('color_preview');

        colorCodeInput.addEventListener('input', function() {
            const val = colorCodeInput.value;
            colorPreview.style.background = val;
            if (val.startsWith('#') && val.length === 7) {
                colorPicker.value = val;
            }
        });

        colorPicker.addEventListener('input', function() {
            const val = colorPicker.value;
            colorCodeInput.value = val;
            colorPreview.style.background = val;
        });
    });
</script>
@endpush
