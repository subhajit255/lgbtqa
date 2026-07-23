@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Bug Reports</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Bug Reports</li>
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

                    <div class="card">
                        <div class="card-body">
                            <table class="table align-middle table-row-dashed fs-6 gy-5">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">Sl No</th>
                                        <th class="min-w-125px">User Info</th>
                                        <th class="min-w-200px">Bug Description</th>
                                        <th class="min-w-100px">Submission Date</th>
                                        <th class="min-w-100px">Status</th>
                                        <th class="text-end min-w-100px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse ($bugs as $bug)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td class="goTo" data-action="{{ route('admin.user.view', $bug->user->uuid) }}">
                                                @if($bug->user->image_path)
                                                    <img src="{{ $bug->user->image_path }}" class="rounded-circle me-2"
                                                        style="width: 40px; height: 40px; object-fit: cover;">
                                                @endif
                                                {{ $bug->user->name }}
                                                <br>
                                                <span class="text-muted fs-7">{{ $bug->user->email ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <span class="text-gray-800" title="{{ $bug->text }}">
                                                    {{ Str::limit($bug->text, 60) }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($bug->created_at)->format('jS F Y') }}</td>
                                            <td>
                                                @if ($bug->status === 'resolve')
                                                    <span class="badge badge-success">Resolved</span>
                                                @elseif ($bug->status === 'working progress')
                                                    <span class="badge badge-primary">Work in Progress</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('admin.bug.view', $bug->id) }}" class="btn btn-sm btn-light btn-active-light-primary">View / Actions</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <i class="fas fa-bug text-muted mb-3" style="font-size: 3rem;"></i>
                                                <h4 class="text-muted">No Bug Reports Found</h4>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {!! $bugs->links('pagination::bootstrap-5') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
