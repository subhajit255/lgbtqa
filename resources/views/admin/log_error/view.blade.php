@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Error Log Detail</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.log-error.list') }}">Error Logs</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">View</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Error Details</h3>
                        </div>
                        <div class="card-body py-6">
                            <div class="row mb-7">
                                <label class="col-lg-2 fw-semibold text-muted">Message</label>
                                <div class="col-lg-10">
                                    <div
                                        class="alert alert-danger bg-light-danger border-danger border-dashed d-flex flex-column p-5 mb-0">
                                        <div class="fs-6 fw-bold text-gray-800">{{ $detail->message }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-7">
                                <label class="col-lg-2 fw-semibold text-muted">File Path</label>
                                <div class="col-lg-10">
                                    <span class="fw-bold fs-6 text-gray-800 text-break">{{ $detail->file_path }}</span>
                                </div>
                            </div>
                            <div class="row mb-7">
                                <label class="col-lg-2 fw-semibold text-muted">Line Number</label>
                                <div class="col-lg-10">
                                    <span class="fw-bold fs-6 text-gray-800">{{ $detail->line_number }}</span>
                                </div>
                            </div>
                            <div class="row mb-7">
                                <label class="col-lg-2 fw-semibold text-muted">Log Date</label>
                                <div class="col-lg-10">
                                    <span
                                        class="fw-bold fs-6 text-gray-800">{{ \Carbon\Carbon::parse($detail->created_at)->format('jS F Y, h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer d-flex justify-content-end py-6 px-9">
                            <a href="{{ route('admin.log-error.list') }}" class="btn btn-light me-3">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
