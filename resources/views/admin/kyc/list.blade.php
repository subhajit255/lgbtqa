@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            KYC Verifications</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">KYC Verification</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-dark goTo"
                                data-action="{{ route('admin.kyc.add') }}">
                                <i class="fas fa-plus me-1"></i> Add Manual KYC
                            </button>
                        </div>
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

                    <div class="card">
                        <div class="card-body">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">Sl No</th>
                                        <th class="min-w-125px">User Info</th>
                                        <th class="min-w-100px">Badge Preference</th>
                                        <th class="min-w-100px">Submission Date</th>
                                        <th class="min-w-70px">Status</th>
                                        <th class="text-end min-w-100px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse ($kycRequests as $kyc)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="goTo" data-action="{{ route('admin.user.view', $kyc->user->uuid) }}">
                                                <img src="{{ $kyc->user->image_path }}" class="rounded-circle me-2"
                                                    style="width: 40px; height: 40px; object-fit: cover;">
                                                {{ $kyc->user->name }}
                                                <br>
                                                <span class="text-muted fs-7">{{ $kyc->user->email ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-40px me-3" style="background-color: {{ $kyc->badgeColor->color_code ?? '#ccc' }}; border-radius: 50%; padding: 5px; display: inline-flex; justify-content: center; align-items: center; width: 40px; height: 40px; min-width: 40px; min-height: 40px;">
                                                        <img src="{{ $kyc->badgeStyle->icon_path ?? asset('assets/media/images/no-image.png') }}" class="" alt="" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold">{{ $kyc->badgeStyle->name ?? 'N/A' }}</span>
                                                        <span class="text-muted fs-7">{{ $kyc->badgeColor->name ?? 'N/A' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($kyc->created_at)->format('jS F Y') }}</td>
                                            <td>
                                                @if ($kyc->status === 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @elseif ($kyc->status === 'rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.kyc.view', $kyc->id) }}" class="btn btn-sm btn-light btn-active-light-primary">View / Actions</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-id-card-alt text-muted mb-3" style="font-size: 3rem;"></i>
                                                <h4 class="text-muted">No KYC Verifications Found</h4>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {!! $kycRequests->links('pagination::bootstrap-5') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
