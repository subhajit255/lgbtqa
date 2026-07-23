@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Role Permission</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.role.list') }}">Role</a>
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
            <form id="permissionForm" action="{{ route('admin.role.permission', $roleData->uuid) }}" method="POST"
                class="formSubmit fileUpload" enctype="multipart/form-data">
                <div class="row">
                    @forelse ($permissions as $group => $chunk)
                        @php
                            $allChecked = collect($chunk)->every(function ($permission) use ($roleData) {
                                return $roleData->hasPermission($permission->slug);
                            });
                        @endphp
                        <div class="col-md-4 mb-3">
                            <div class="permission-card card p-5">
                                <h3 class="mb-6">{{ ucwords(str_replace('_', ' ', $group)) . ' Block :' }}
                                    <label class="flex items-center float-end">
                                        <span class="text-sm ml-2">All</span>
                                        ->
                                        <input type="checkbox" class="form-check-input form-checkbox check-all"
                                            data-group="{{ $group }}" @if ($allChecked) checked @endif />
                                    </label>
                                </h3>
                                @forelse ($chunk as $permission)
                                    <div class="p-2">
                                        <label class="flex items-center">
                                            <input type="checkbox" class="form-check-input form-checkbox permission-checkbox"
                                                name="permission[]" value="{{ $permission->slug }}"
                                                data-group="{{ $group }}"
                                                @if ($roleData->hasPermission($permission->slug)) checked @endif />
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
    @push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.check-all').forEach(function (checkAllBox) {
                checkAllBox.addEventListener('change', function () {
                    let group = this.dataset.group;
                    let checkboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                    checkboxes.forEach(function (checkbox) {
                        checkbox.checked = checkAllBox.checked;
                    });
                });
            });

            document.querySelectorAll('.permission-checkbox').forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    let group = this.dataset.group;
                    let checkAllBox = document.querySelector(`.check-all[data-group="${group}"]`);
                    let checkboxes = document.querySelectorAll(`.permission-checkbox[data-group="${group}"]`);
                    let allChecked = Array.from(checkboxes).every(function (checkbox) {
                        return checkbox.checked;
                    });
                    checkAllBox.checked = allChecked;
                });
            });
        });
    </script>
    @endpush
@endsection
