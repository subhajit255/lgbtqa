@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            KYC Verification Details</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.kyc.list') }}">KYC List</a>
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
                                <h3 class="fw-bold m-0">User & Request Info</h3>
                            </div>
                            <div class="card-toolbar">
                                @if ($kyc->status === 'pending')
                                    <form action="{{ route('admin.kyc.approve', $kyc->id) }}" method="POST" class="me-2 d-inline" id="approveForm">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-success btn-approve">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.kyc.reject', $kyc->id) }}" method="POST" class="d-inline" id="rejectForm">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger btn-reject">Reject</button>
                                    </form>
                                @else
                                    <span class="badge badge-lg {{ $kyc->status === 'approved' ? 'badge-success' : 'badge-danger' }}">
                                        {{ ucfirst($kyc->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="card-body border-top p-9">
                            <div class="row mb-7">
                                <label class="col-lg-4 fw-semibold text-muted">User Name</label>
                                <div class="col-lg-8">
                                    <span class="fw-bold fs-6 text-gray-800">{{ $kyc->user->name }}</span>
                                </div>
                            </div>
                            <div class="row mb-7 border-top border-gray-200 pt-5 mt-5">
                                <label class="col-lg-4 fw-semibold text-muted">Visual Badge Preview</label>
                                <div class="col-lg-8">
                                    <div class="d-flex align-items-center mb-1" title="{{ $kyc->badgeStyle->name ?? 'N/A' }} ({{ $kyc->badgeColor->name ?? 'N/A' }})">
                                        <span class="symbol symbol-45px me-3 shadow-sm" style="background-color: {{ $kyc->badgeColor->color_code ?? '#ccc' }}; border-radius: 50%; padding: 6px; display: inline-flex; justify-content: center; align-items: center; width: 45px; height: 45px; min-width: 45px; min-height: 45px;">
                                            <img src="{{ $kyc->badgeStyle->icon_path ?? asset('assets/media/images/no-image.png') }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                        </span>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold fs-5 text-gray-800">{{ $kyc->badgeStyle->name ?? 'N/A' }}</span>
                                            <span class="fw-semibold fs-7 text-muted">{{ $kyc->badgeColor->name ?? 'N/A' }} ({{ $kyc->badgeColor->color_code ?? '#ccc' }})</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-5">
                            <div class="card h-100">
                                <div class="card-header border-0">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bold m-0">Government ID Image</h3>
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    @if($kyc->govt_id_image_url)
                                        <a href="{{ $kyc->govt_id_image_url }}" target="_blank">
                                            <img src="{{ $kyc->govt_id_image_url }}" alt="Govt ID" class="img-fluid rounded border" style="max-height: 250px; max-width: 100%; object-fit: contain;">
                                        </a>
                                    @else
                                        <div class="text-muted p-5 bg-light rounded">No image uploaded</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-5">
                            <div class="card h-100">
                                <div class="card-header border-0">
                                    <div class="card-title m-0">
                                        <h3 class="fw-bold m-0">Identity Image (Selfie)</h3>
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    @if($kyc->identity_image_url)
                                        <a href="{{ $kyc->identity_image_url }}" target="_blank">
                                            <img src="{{ $kyc->identity_image_url }}" alt="Identity Selfie" class="img-fluid rounded border" style="max-height: 250px; max-width: 100%; object-fit: contain;">
                                        </a>
                                    @else
                                        <div class="text-muted p-5 bg-light rounded">No image uploaded</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $('.btn-approve').on('click', function() {
                Swal.fire({
                    title: 'Approve KYC?',
                    text: "Are you sure you want to approve this verification?",
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonColor: '#00cc66',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#approveForm').submit();
                    }
                });
            });

            $('.btn-reject').on('click', function() {
                Swal.fire({
                    title: 'Reject KYC?',
                    text: "Are you sure you want to reject this verification?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ff3333',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Reject'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#rejectForm').submit();
                    }
                });
            });
        </script>
    @endpush
@endsection
