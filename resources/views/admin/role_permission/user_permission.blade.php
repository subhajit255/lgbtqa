@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    User Role Permission</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.role.user.list') }}">Admin User</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Permission</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl">
        <div class="container">
            <form id="permissionForm" action="{{ route('admin.role.user.permission', $user->uuid) }}" method="POST"
                class="formSubmit fileUpload" enctype="multipart/form-data">
                <div class="row">
                    @forelse ($permissions as $group => $chunk)
                        <div class="col-md-4 mb-3">
                            <div class="permission-card card p-5">
                                <h3 class="mb-6">{{ ucwords(str_replace('_', ' ', $group)) . ' Block ::' }}</h3>
                                @forelse ($chunk as $permission)
                                    <div class="p-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" class="form-check-input form-checkbox"
                                                name="permission[]" value="{{ $permission->id }}"
                                                @if ($user->hasUserPermission($permission->slug)) checked @endif />
                                            &nbsp;&nbsp;&nbsp;
                                            <span class="text-sm ml-2">{{ $permission->name }}</span>
                                        </label>
                                    </div>
                                @empty
                                @endforelse
                            </div>
                        </div>
                    @empty
                    @endforelse
                    @error('permission')
                        <span class="text-danger text-sm">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="button add-btn-div-save-style">
                    <button type="submit" id="submitBtn" class="btn btn-dark">
                        <span class="indicator-label">Update</span>
                        <span class="indicator-progress">Please wait...
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
