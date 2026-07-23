@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Badge Style Grid</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Badge Style</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-dark goTo"
                                data-action="{{ route('admin.badge-style.add') }}">Add
                                Badge Style</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="row g-6 g-xl-9">
                        @forelse ($details as $detail)
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="card h-100">
                                    <div class="card-body d-flex flex-center flex-column p-9">
                                        <div class="symbol symbol-65px symbol-circle mb-5">
                                            <img src="{{ $detail->icon_path }}" alt="image" />
                                        </div>
                                        <a href="{{ route('admin.badge-style.add', $detail->uuid) }}" class="fs-4 text-gray-800 text-hover-primary fw-bold mb-0">{{ $detail->name }}</a>
                                        <div class="fw-semibold text-gray-400 mb-6">Badge Style</div>
                                        <div class="d-flex flex-center flex-wrap mb-5">
                                            <div class="form-check form-switch form-check-custom form-check-solid">
                                                <input class="form-check-input h-20px w-30px isVerified" type="checkbox" 
                                                    data-uuid="{{ $detail->uuid }}" data-table="badge_styles"
                                                    id="status_{{ $detail->id }}" value="{{ $detail->status ?? 0 }}"
                                                    {{ $detail->status == 1 ? 'checked' : '' }} />
                                                <label class="form-check-label fs-7 fw-bold text-gray-400" for="status_{{ $detail->id }}">
                                                    {{ $detail->status == 1 ? 'Active' : 'Inactive' }}
                                                </label>
                                            </div>
                                        </div>
                                        <div class="d-flex flex-center">
                                            <a href="{{ route('admin.badge-style.add', $detail->uuid) }}" class="btn btn-sm btn-light-primary btn-flex btn-center mr-2" style="margin-right: 5px;">
                                                <i class="fa-solid fa-pen-to-square fs-3"></i> Edit
                                            </a>
                                            <a href="javascript:void(0)" data-table="badge_styles" data-uuid="{{ $detail->uuid }}" class="btn btn-sm btn-light-danger btn-flex btn-center deleteData">
                                                <i class="fa-solid fa-trash fs-3"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body text-center p-20">
                                        <h3 class="text-gray-600">No badge styles found.</h3>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
