@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <!--begin::Title-->
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Customer List</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Customer</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <form action="{{ route('admin.user.list') }}" method="GET"
                            class="d-flex align-items-center my-1 gap-2">
                            <div class="position-relative">
                                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                                <input type="text" name="keyword" value="{{ request()->get('keyword') }}"
                                    class="form-control form-control-sm form-control-solid ps-12 glass-input"
                                    placeholder="Search Customer..." style="width: 250px;">
                            </div>
                            <button type="submit" class="btn btn-sm btn-primary glass-button px-4">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                            <a href="{{ route('admin.user.list') }}" class="btn btn-sm btn-secondary glass-button px-4">
                                <i class="fas fa-sync-alt me-1"></i> Reset
                            </a>
                        </form>
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-sm btn-dark goTo"
                                data-action="{{ route('admin.user.add') }}">
                                <i class="fas fa-plus me-1"></i> Add Customer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="kt_customers_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">Sl No</th>
                                        <th class="min-w-125px">Customer Name</th>
                                        <th class="min-w-70px">Email</th>
                                        <th class="min-w-70px">KYC Status</th>
                                        {{-- <th class="min-w-70px">Mobile Number</th> --}}
                                        <th class="min-w-70px">Since</th>
                                        <th class="min-w-70px">Status</th>
                                        <th class="text-end min-w-70px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse ($details as $detail)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="goTo" data-action="{{ route('admin.user.view', $detail->uuid) }}">
                                                <div class="position-relative d-inline-block">
                                                    <img src="{{ $detail->image_path }}" class="rounded-circle me-2"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                    <span class="position-absolute bottom-0 end-0 {{ $detail->is_online ? 'bg-success' : 'bg-secondary' }} border border-2 border-white rounded-circle me-2"
                                                        style="width: 12px; height: 12px;" title="{{ $detail->is_online ? 'Online' : 'Offline' }}"></span>
                                                </div>
                                                {{ $detail->name ?? 'N/A' }}
                                                @if ($detail->is_online)
                                                    <span class="badge badge-light-success fs-8 ms-1">Online</span>
                                                @else
                                                    <span class="badge badge-light-secondary fs-8 ms-1">Offline</span>
                                                @endif
                                                @if ($detail->is_verified_email == 1)
                                                    <i title="Email Verified" class="fas fa-check-circle text-success ms-1"></i>
                                                @endif
                                                <br>
                                                <span class="text-muted">
                                                    @if($detail->is_online)
                                                        <span class="text-success fw-bold">Active now</span>
                                                    @elseif($detail->last_seen_at)
                                                        Last Seen: {{ \Carbon\Carbon::parse($detail->last_seen_at)->diffForHumans() }}
                                                    @else
                                                        Last Login: {{ $detail->last_login_at ? \Carbon\Carbon::parse($detail->last_login_at, 'GMT')->setTimezone(getCurrentTimeZone())->format('Y-m-d h:i A') : 'Not Logged In Yet' }}
                                                    @endif
                                                </span>
                                            </td>
                                            <td>{{ $detail->email ?? 'N/A' }}</td>
                                            <td>
                                                @if ($detail->kycVerification)
                                                    <div class="d-flex align-items-center justify-content-start gap-2">
                                                        <span class="symbol"
                                                            style="background-color: {{ $detail->kycVerification->badgeColor->color_code ?? '#ccc' }}; border-radius: 50%; padding: 4px; display: inline-flex; justify-content: center; align-items: center; width: 32px; height: 32px; min-width: 32px; min-height: 32px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);"
                                                            title="{{ $detail->kycVerification->badgeStyle->name ?? 'N/A' }} ({{ $detail->kycVerification->badgeColor->name ?? 'N/A' }})">
                                                            <img src="{{ $detail->kycVerification->badgeStyle->icon_path ?? asset('assets/media/images/no-image.png') }}"
                                                                style="max-width: 100%; max-height: 100%; object-fit: contain;">
                                                        </span>
                                                        @if ($detail->kycVerification->status === 'pending')
                                                            <a href="{{ route('admin.kyc.view', $detail->kycVerification->id) }}"
                                                                class="badge badge-light-warning text-warning fw-bold fs-8 border border-warning"
                                                                title="Click to View & Action"
                                                                style="text-decoration: none;">
                                                                Pending Action
                                                            </a>
                                                        @elseif($detail->kycVerification->status === 'approved')
                                                            <span
                                                                class="badge badge-light-success text-success fw-bold fs-8 border border-success">Approved</span>
                                                        @elseif($detail->kycVerification->status === 'rejected')
                                                            <span
                                                                class="badge badge-light-danger text-danger fw-bold fs-8 border border-danger">Rejected</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="badge badge-light fs-8 text-muted border">Not
                                                        Submitted</span>
                                                @endif
                                            </td>
                                            {{-- <td>+{{ $detail->phone_code ?? 91 }} {{ $detail->mobile_number ?? 'N/A' }}</td> --}}
                                            <td>{{ \Carbon\Carbon::parse($detail->created_at)->format('jS F Y') }} </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" data-uuid="{{ $detail->uuid }}"
                                                        data-table="users" class="form-check-input isVerified"
                                                        id="status_{{ $detail->id }}"
                                                        value="{{ $detail->is_active ?? 0 }}"
                                                        {{ $detail->is_active == 1 ? 'checked' : '' }}>
                                                    <label class="custom-control-label"
                                                        for="status_{{ $detail->id }}">{{ $detail->is_active == 1 ? 'Active' : 'In-Active' }}</label>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <a href="javascript:void(0)"
                                                    class="btn btn-sm btn-light btn-active-light-primary"
                                                    data-kt-menu-trigger="click"
                                                    data-kt-menu-placement="bottom-end">Actions</a>
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                                    data-kt-menu="true">
                                                    <div class="menu-item px-3">
                                                        <a class="menu-link px-3"
                                                            href="{{ route('admin.user.view', $detail->uuid) }}">View</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a class="menu-link px-3"
                                                            href="{{ route('admin.user.add', $detail->uuid) }}">Edit</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a class="menu-link px-3 delete-item"
                                                            data-url="{{ route('admin.user.delete', $detail->uuid) }}"
                                                            href="javascript:void(0)">Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                @include('partials.empty_state', [
                                                    'title' => 'No Customers Yet',
                                                    'description' =>
                                                        "We couldn't find any customers in the system. Start by adding your first customer!",
                                                    'action_url' => route('admin.user.add'),
                                                    'action_text' => 'Add Customer',
                                                    'action_icon' => 'fas fa-plus',
                                                ])
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {!! $details->withQueryString()->links('pagination::bootstrap-5') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).on('click', '.delete-item', function() {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this customer!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                })
            });
        </script>
    @endpush

    @push('styles')
        <style>
            .glass-input {
                background: rgba(255, 255, 255, 0.4) !important;
                backdrop-filter: blur(8px) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                border: 1px solid rgba(255, 255, 255, 0.2) !important;
                border-radius: 12px !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
            }

            .glass-input:focus {
                background: rgba(255, 255, 255, 0.6) !important;
                border: 1px solid rgba(255, 255, 255, 0.4) !important;
                box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
                transform: translateY(-1px);
            }

            .glass-button {
                background: rgba(255, 255, 255, 0.2) !important;
                backdrop-filter: blur(8px) !important;
                -webkit-backdrop-filter: blur(8px) !important;
                border: 1px solid rgba(255, 255, 255, 0.3) !important;
                border-radius: 12px !important;
                font-weight: 600 !important;
                transition: all 0.3s ease;
            }

            .glass-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 12px -1px rgba(0, 0, 0, 0.1) !important;
            }

            .btn-primary.glass-button {
                background: rgba(63, 66, 84, 0.85) !important;
                color: white !important;
            }

            .btn-secondary.glass-button {
                background: rgba(244, 246, 250, 0.8) !important;
                color: #3f4254 !important;
            }
        </style>
    @endpush
@endsection
