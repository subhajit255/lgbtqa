@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Setting {{ !empty($details) ? 'Edit' : 'Add' }}</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.setting.update') }}"
                                    class="text-muted text-hover-primary">Setting</a>
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
                                <form id="uomForm" action="{{ route('admin.setting.update') }}" method="POST"
                                    class="formSubmit fileUpload" enctype="multipart/form-data">
                                    <input type="hidden" name="id" name="id" value="{{ $details->id ?? null }}">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="instagram" class="label-style">Instagram</label>
                                                {{-- <span class="astrict_sign">*</span> --}}
                                                <input type="text" class="form-control" placeholder="Enter instagram url"
                                                    name="instagram" id="instagram"
                                                    value="{{ $details->instagram ?? null }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="facebook" class="label-style">Facebook</label>
                                                {{-- <span class="astrict_sign">*</span> --}}
                                                <input type="text" class="form-control" placeholder="Enter facebook url"
                                                    name="facebook" id="facebook" value="{{ $details->facebook ?? null }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="twitter" class="label-style">Twitter</label>
                                                {{-- <span class="astrict_sign">*</span> --}}
                                                <input type="text" class="form-control" placeholder="Enter twitter url"
                                                    name="twitter" id="twitter" value="{{ $details->twitter ?? null }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="linkedin" class="label-style">Linkedin</label>
                                                {{-- <span class="astrict_sign">*</span> --}}
                                                <input type="text" class="form-control" placeholder="Enter linkedin url"
                                                    name="linkedin" id="linkedin" value="{{ $details->linkedin ?? null }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-3">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contact_email" class="label-style">Contact Email</label>
                                                {{-- <span class="astrict_sign">*</span> --}}
                                                <input type="text" class="form-control" placeholder="Enter contact email"
                                                    name="contact_email" id="contact_email"
                                                    value="{{ $details->contact_email ?? null }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="contact_number" class="label-style">Contact Number</label>
                                                {{-- <span class="astrict_sign">*</span> --}}
                                                <input type="text" class="form-control number-only"
                                                    placeholder="Enter contact number" name="contact_number"
                                                    id="contact_number" maxlength="10"
                                                    value="{{ $details->contact_number ?? null }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-4">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="term_and_condition" class="label-style">Term and
                                                    Condition</label>
                                                <textarea class="form-control" name="term_and_condition" id="term_and_condition" cols="30" rows="4">{{ $details->term_and_condition ?? null }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="privacy_policy" class="label-style">Privacy Policy</label>
                                                <textarea class="form-control" name="privacy_policy" id="privacy_policy" cols="30" rows="4">{{ $details->privacy_policy ?? null }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="about_us" class="label-style">About us</label>
                                                <textarea class="form-control" name="about_us" id="about_us" cols="30" rows="4">{{ $details->about_us ?? null }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-4">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="child_safety" class="label-style">Child Safety</label>
                                                <textarea class="form-control" name="child_safety" id="child_safety" cols="30" rows="4">{{ $details->child_safety ?? null }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="button add-btn-div-save-style">
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

    @push('script')
        <script src="{{ asset('assets/js/custom_js/cdn/ckeditor.js') }}"></script>
        <script>
            $(document).ready(function() {
                let termAndConditionEditorInstance;
                ClassicEditor
                    .create(document.querySelector('#term_and_condition'))
                    .then(editor => {
                        termAndConditionEditorInstance = editor;
                        console.log('Editor initialized', editor);
                    })
                    .catch(error => {
                        console.error('Error initializing CKEditor', error);
                    });

                let privacyPolicyEditorInstance;
                ClassicEditor
                    .create(document.querySelector('#privacy_policy'))
                    .then(editor => {
                        privacyPolicyEditorInstance = editor;
                        console.log('Editor initialized', editor);
                    })
                    .catch(error => {
                        console.error('Error initializing CKEditor', error);
                    });

                let aboutUsEditorInstance;
                ClassicEditor
                    .create(document.querySelector('#about_us'))
                    .then(editor => {
                        aboutUsEditorInstance = editor;
                        console.log('Editor initialized', editor);
                    })
                    .catch(error => {
                        console.error('Error initializing CKEditor', error);
                    });
                
                let childSafetyEditorInstance;
                ClassicEditor
                    .create(document.querySelector('#child_safety'))
                    .then(editor => {
                        childSafetyEditorInstance = editor;
                        console.log('Editor initialized', editor);
                    })
                    .catch(error => {
                        console.error('Error initializing CKEditor', error);
                    });

                $(document).on("click", "#submitBtn", function() {
                    document.getElementById('term_and_condition').value = termAndConditionEditorInstance
                        .getData();
                    document.getElementById('privacy_policy').value = privacyPolicyEditorInstance.getData();
                    document.getElementById('about_us').value = aboutUsEditorInstance.getData();
                    document.getElementById('child_safety').value = childSafetyEditorInstance.getData();
                });
            });
        </script>
    @endpush
@endsection
