@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Event List</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Events</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-dark goTo"
                                data-action="{{ route('admin.event.add') }}">Add New Event</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body pt-0 table-responsive">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_events_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="min-w-50px">Sl No</th>
                                        <th class="min-w-200px">Event</th>
                                        <th class="min-w-150px">Date & Time</th>
                                        <th class="min-w-120px">Location</th>
                                        <th class="min-w-150px">Host Details</th>
                                        <th class="min-w-120px">Audience / Tags</th>
                                        <th class="min-w-100px">Status</th>
                                        <th class="text-end min-w-100px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse ($details as $detail)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-50px me-3">
                                                        <img src="{{ $detail->image_path }}" alt="Event Banner" style="object-fit: cover; width: 50px; height: 50px; border-radius: 8px;">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold fs-6">{{ $detail->title }}</span>
                                                        @if($detail->description)
                                                            <span class="text-muted fs-7">{{ Str::limit($detail->description, 50) }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="text-gray-800 fw-semibold">{{ Carbon\Carbon::parse($detail->event_date)->format('M d, Y') }}</span>
                                                    <span class="text-muted fs-7">{{ $detail->start_time }} - {{ $detail->end_time }}</span>
                                                </div>
                                            </td>
                                            <td>{{ $detail->location }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="symbol symbol-35px symbol-circle me-3">
                                                        <img src="{{ $detail->host_image_path }}" alt="Host Avatar" style="object-fit: cover;">
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="text-gray-800 fw-bold fs-7">{{ $detail->host_name }}</span>
                                                        <span class="text-muted fs-8">{{ $detail->host_type }} {{ $detail->host_pronouns ? '('.$detail->host_pronouns.')' : '' }}</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    @if($detail->audience)
                                                        <span class="badge badge-light-primary fs-8 fw-bold align-self-start">{{ $detail->audience }}</span>
                                                    @endif
                                                    @if($detail->tags)
                                                        <div class="d-flex flex-wrap gap-1">
                                                            @foreach(explode(',', $detail->tags) as $tag)
                                                                <span class="badge badge-light-secondary fs-9 fw-semibold">{{ trim($tag) }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" data-uuid="{{ $detail->uuid }}"
                                                        data-table="events" class="form-check-input isVerified"
                                                        id="status_{{ $detail->id }}"
                                                        value="{{ $detail->is_active ?? 0 }}" {{ $detail->is_active == 1 ? 'checked' : '' }}>
                                                    <label class="custom-control-label fs-7"
                                                        for="status_{{ $detail->id }}">{{ $detail->is_active == 1 ? 'Active' : 'Inactive' }}</label>
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
                                                            href="{{ route('admin.event.add', $detail->uuid) }}">Edit</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:void(0)" data-table="events"
                                                            data-uuid="{{ $detail->uuid }}"
                                                            class="menu-link px-3 deleteData text-danger">Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted">No Events Found.</td>
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
@endsection
