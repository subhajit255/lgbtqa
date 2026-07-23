@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Bug Report Details</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.bug.list') }}">Bug Reports</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Details</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">

                    @if (session('success'))
                        <div class="alert alert-success d-flex align-items-center p-5 mb-5">
                            <span class="svg-icon svg-icon-2hx svg-icon-success me-4">
                                <i class="fas fa-check-circle" style="font-size: 2rem;"></i>
                            </span>
                            <div class="d-flex flex-column">
                                <h4 class="mb-1 text-success">Success</h4>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="card mb-5 mb-xl-10">
                        <div class="card-header border-0 cursor-pointer" role="button">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Bug Report & User Info</h3>
                            </div>
                            <div class="card-toolbar">
                                <span class="me-3 fs-6 fw-semibold text-muted">Current Status:</span>
                                @if ($bug->status === 'resolve')
                                    <span class="badge badge-lg badge-success">Resolved</span>
                                @elseif ($bug->status === 'working progress')
                                    <span class="badge badge-lg badge-primary">Work in Progress</span>
                                @else
                                    <span class="badge badge-lg badge-warning">Pending</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body border-top p-9">
                            <div class="row mb-7">
                                <label class="col-lg-4 fw-semibold text-muted">User Name</label>
                                <div class="col-lg-8">
                                    <span class="fw-bold fs-6 text-gray-800">{{ $bug->user->name }}</span>
                                    <span class="text-muted fs-7 d-block">{{ $bug->user->email ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="row mb-7 border-top border-gray-200 pt-5 mt-5">
                                <label class="col-lg-4 fw-semibold text-muted">Submission Date</label>
                                <div class="col-lg-8">
                                    <span class="fw-bold fs-6 text-gray-800">{{ \Carbon\Carbon::parse($bug->created_at)->format('jS F Y, h:i A') }}</span>
                                </div>
                            </div>
                            <div class="row mb-7 border-top border-gray-200 pt-5 mt-5">
                                <label class="col-lg-4 fw-semibold text-muted">Bug Description (Text)</label>
                                <div class="col-lg-8">
                                    <p class="fw-semibold fs-6 text-gray-800" style="white-space: pre-wrap;">{{ $bug->text }}</p>
                                </div>
                            </div>
                            
                            <div class="row mb-7 border-top border-gray-200 pt-5 mt-5">
                                <label class="col-lg-4 fw-semibold text-muted">Update Status</label>
                                <div class="col-lg-8">
                                    <form action="{{ route('admin.bug.update-status', $bug->id) }}" method="POST" class="d-inline" id="statusForm">
                                        @csrf
                                        <input type="hidden" name="status" id="statusInput" value="">
                                        
                                        @if ($bug->status !== 'pending')
                                            <button type="button" class="btn btn-sm btn-warning btn-change-status me-2" data-status="pending">Set Pending</button>
                                        @endif
                                        @if ($bug->status !== 'working progress')
                                            <button type="button" class="btn btn-sm btn-primary btn-change-status me-2" data-status="working progress">Set In Progress</button>
                                        @endif
                                        @if ($bug->status !== 'resolve')
                                            <button type="button" class="btn btn-sm btn-success btn-change-status" data-status="resolve">Set Resolved</button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-5 mb-xl-10">
                        <div class="card-header border-0">
                            <div class="card-title m-0">
                                <h3 class="fw-bold m-0">Attachment / Screenshot</h3>
                            </div>
                        </div>
                        <div class="card-body text-center border-top">
                            @if($bug->image_url)
                                <a href="{{ $bug->image_url }}" target="_blank">
                                    <img src="{{ $bug->image_url }}" alt="Bug Screenshot" class="img-fluid rounded border shadow-sm" style="max-height: 500px; max-width: 100%; object-fit: contain;">
                                </a>
                            @else
                                <div class="text-muted p-5 bg-light rounded">
                                    <i class="fas fa-image text-muted d-block fs-1 mb-2"></i>
                                    No image attachment uploaded with this report.
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $('.btn-change-status').on('click', function() {
                var status = $(this).data('status');
                var displayStatus = '';
                
                if (status === 'pending') {
                    displayStatus = 'Pending';
                } else if (status === 'working progress') {
                    displayStatus = 'Work in Progress';
                } else if (status === 'resolve') {
                    displayStatus = 'Resolved';
                }

                Swal.fire({
                    title: 'Update Status?',
                    text: "Are you sure you want to change the status to '" + displayStatus + "'?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, update it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#statusInput').val(status);
                        $('#statusForm').submit();
                    }
                });
            });
        </script>
    @endpush
@endsection
