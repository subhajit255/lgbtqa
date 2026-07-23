@extends('layout.app')
@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        Community Profile</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.community.list') }}" class="text-muted text-hover-primary">Communities</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">{{ $community->name }}</li>
                    </ul>
                </div>
            </div>
        </div>

        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Community Header Card -->
                <div class="card mb-9">
                    <div class="card-body pt-9 pb-0">
                        <div class="d-flex flex-wrap flex-sm-nowrap mb-6">
                            <div class="me-7 mb-4">
                                <div class="symbol symbol-100px symbol-lg-160px symbol-fixed position-relative">
                                    <img src="{{ $community->image_path }}" alt="Community Logo" style="object-fit: cover; border-radius: 8px;">
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
                                    <div class="d-flex flex-column">
                                        <div class="d-flex align-items-center mb-1">
                                            <span class="text-gray-800 text-hover-primary fs-2 fw-bold me-3">{{ $community->name }}</span>
                                            @if($community->type == 'private')
                                                <span class="badge badge-light-danger me-auto">Private</span>
                                            @else
                                                <span class="badge badge-light-success me-auto">Public</span>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-wrap fw-semibold mb-4 fs-6 text-gray-400 pe-2">
                                            <span class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                <i class="fa-regular fa-user me-1"></i> Creator: {{ $community->creator->name }}
                                            </span>
                                            <span class="d-flex align-items-center text-gray-400 text-hover-primary me-5 mb-2">
                                                <i class="fa-solid fa-tags me-1"></i> {{ $community->tags ?? 'No tags' }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="d-flex my-4">
                                        <a href="{{ route('admin.community.add', $community->uuid) }}" class="btn btn-sm btn-dark me-2">Edit Details</a>
                                    </div>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="d-flex flex-column flex-grow-1 pe-8">
                                        <div class="d-flex flex-wrap fs-6 text-gray-600 mb-4">
                                            {{ $community->description ?? 'No description provided.' }}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center w-200px w-sm-300px flex-column mt-3">
                                        <div class="d-flex align-items-center w-100 mb-2">
                                            <span class="fs-6 fw-semibold text-gray-400">Total Members</span>
                                            <span class="ms-auto fw-bold fs-5 text-dark">{{ $activeMembers->count() }} Active</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabs Navigation -->
                <div class="card">
                    <div class="card-header card-header-stretch border-bottom border-gray-200">
                        <div class="card-title">
                            <ul class="nav nav-custom nav-tabs nav-tabs-line border-0 fs-6 fw-semibold mb-n2">
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4 active" data-bs-toggle="tab" href="#kt_members_tab">Active Members ({{ $activeMembers->count() }})</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link text-active-primary pb-4" data-bs-toggle="tab" href="#kt_requests_tab">
                                        Pending Join Requests 
                                        @if($pendingRequests->count() > 0)
                                            <span class="badge badge-light-danger ms-2">{{ $pendingRequests->count() }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Active Members Tab -->
                            <div class="tab-pane fade show active" id="kt_members_tab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th>Member Name</th>
                                                <th>Email</th>
                                                <th>Role</th>
                                                <th>Joined At</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            @forelse($activeMembers as $member)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                                <img src="{{ $member->user->image_path }}" alt="User Avatar" style="object-fit: cover;">
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-800 fw-bold fs-6">{{ $member->user->name }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $member->user->email }}</td>
                                                    <td>
                                                        @if($member->role == 'creator')
                                                            <span class="badge badge-light-primary fw-bold">Creator</span>
                                                        @else
                                                            <span class="badge badge-light-secondary fw-bold">Member</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $member->created_at->format('M d, Y') }}</td>
                                                    <td class="text-end">
                                                        @if($member->role != 'creator')
                                                            <a href="javascript:void(0)" data-table="community_members" data-uuid="{{ $member->id }}" class="btn btn-sm btn-light-danger deleteData">Remove Member</a>
                                                        @else
                                                            <span class="text-muted fs-7 italic">Owner</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted">No members found in this community.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Pending Requests Tab -->
                            <div class="tab-pane fade" id="kt_requests_tab" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable">
                                        <thead>
                                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                                <th>User Name</th>
                                                <th>Email</th>
                                                <th>Requested At</th>
                                                <th class="text-end">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600">
                                            @forelse($pendingRequests as $request)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="symbol symbol-35px symbol-circle me-3">
                                                                <img src="{{ $request->user->image_path }}" alt="User Avatar" style="object-fit: cover;">
                                                            </div>
                                                            <div>
                                                                <span class="text-gray-800 fw-bold fs-6">{{ $request->user->name }}</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>{{ $request->user->email }}</td>
                                                    <td>{{ $request->created_at->format('M d, Y h:i A') }}</td>
                                                    <td class="text-end">
                                                        <a href="{{ route('admin.community.approve', $request->id) }}" class="btn btn-sm btn-light-success me-2">
                                                            <i class="fa fa-check"></i> Approve
                                                        </a>
                                                        <a href="{{ route('admin.community.reject', $request->id) }}" class="btn btn-sm btn-light-danger">
                                                            <i class="fa fa-times"></i> Reject
                                                        </a>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted">No pending join requests.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
