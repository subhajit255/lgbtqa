@extends('layout.app')
@section('content')
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ !empty($details) ? 'Edit' : 'Add' }} Community</h1>
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
                    </ul>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card shadow-sm border-0 glass-card">
                    <div class="card-body p-10">
                        <form id="communityForm" action="{{ route('admin.community.add') }}" method="POST" class="formSubmit fileUpload" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="id" value="{{ $details->id ?? null }}">
                            
                            <div class="row mb-8">
                                <!-- Community Image -->
                                <div class="col-md-12 mb-5">
                                    <label class="d-block fw-bold fs-6 mb-2">
                                        <span class="required">Community Banner Image</span>
                                    </label>
                                    <div class="fv-row">
                                        @if (!empty($details->image))
                                        <style>
                                            .community-image-placeholder {
                                                background-image: url('{{ $details->image_path }}');
                                            }
                                        </style>
                                        @else
                                        <style>
                                            .community-image-placeholder {
                                                background-image: url('/assets/media/svg/files/blank-image.png');
                                            }
                                        </style>
                                        @endif
                                        <div class="image-input image-input-empty image-input-outline community-image-placeholder" data-kt-image-input="true" style="width: 100%; max-width: 250px;">
                                            <div class="image-input-wrapper w-100 h-150px" style="background-size: cover; background-position: center;"></div>
                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Upload Community Banner">
                                                <div>
                                                    <i class="fa-solid fa-pen"></i>
                                                </div>
                                                <input type="file" name="file" accept=".png, .jpg, .jpeg" id="file" />
                                                <input type="hidden" name="avatar_remove" />
                                            </label>
                                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                                                <i class="fa-solid fa-xmark"></i>
                                            </span>
                                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="form-text mt-2 text-muted fs-7">Allowed types: png, jpg, jpeg. Recommended ratio 1:1 or 16:9</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6">
                                    <div class="fv-row mb-6">
                                        <label class="required fw-bold fs-6 mb-2">Community Name</label>
                                        <input type="text" name="name" class="form-control form-control-solid" placeholder="Sapphic Space" value="{{ $details->name ?? '' }}" required />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fv-row mb-6">
                                        <label class="required fw-bold fs-6 mb-2">Creator (Primary Admin)</label>
                                        <select name="creator_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select creator user" required>
                                            <option value=""></option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}" {{ (isset($details->creator_id) && $details->creator_id == $user->id) || (!isset($details) && $loop->first) ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-9 mb-8">
                                <div class="col-md-6">
                                    <div class="fv-row mb-6">
                                        <label class="required fw-bold fs-6 mb-2">Community Privacy Type</label>
                                        <select name="type" class="form-select form-select-solid" data-hide-search="true" required>
                                            <option value="public" {{ !isset($details) || (isset($details->type) && $details->type == 'public') ? 'selected' : '' }}>Public (Direct Join)</option>
                                            <option value="private" {{ isset($details->type) && $details->type == 'private' ? 'selected' : '' }}>Private (Requires approval request)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Community Categories (Groups)</label>
                                        <select name="categories[]" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Categories" data-allow-clear="true" multiple>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" 
                                                    {{ isset($details) && $details->categories->contains($cat->id) ? 'selected' : '' }}>
                                                    {{ ucfirst($cat->group) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="fv-row mb-8">
                                <label class="fw-bold fs-6 mb-2">Tags / Search Keywords (Comma separated)</label>
                                <input type="text" name="tags" class="form-control form-control-solid" placeholder="Lesbian Community, Queer Circle" value="{{ $details->tags ?? '' }}" />
                            </div>

                            <div class="fv-row mb-8">
                                <label class="fw-bold fs-6 mb-2">Community Description</label>
                                <textarea name="description" class="form-control form-control-solid" rows="4" placeholder="Briefly describe what this community is about...">{{ $details->description ?? '' }}</textarea>
                            </div>

                            <div class="fv-row mb-8">
                                <label class="fw-bold fs-6 mb-2">Status</label>
                                <select name="is_active" class="form-select form-select-solid" data-hide-search="true">
                                    <option value="1" {{ !isset($details) || (isset($details->is_active) && $details->is_active == 1) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ isset($details->is_active) && $details->is_active == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end border-top pt-8">
                                <button type="button" class="btn btn-light me-3" onclick="window.history.back()">Cancel</button>
                                <button type="submit" id="submitBtn" class="btn btn-dark">
                                    <span class="indicator-label">{{ !empty($details) ? 'Update' : 'Save' }} Community</span>
                                    <span class="indicator-progress">Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
