@extends('layout.app')
@section('content')
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Roles List
                    </h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-hover-primary text-muted">Roles</li>
                    </ul>
                </div>
                <div class="d-flex align-items-center gap-2 gap-lg-3">
                    <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                        <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addRole">Add
                            Role</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
                    @forelse ($roles as $role)
                        <div class="col-md-4">
                            <div class="card card-flush h-md-100">
                                <div class="card-header">
                                    <div class="card-title">
                                        <h2>{{ $role->name }}</h2>&nbsp;&nbsp;
                                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addRole"
                                            class="editRole" data-id='{{ json_encode($role) }}'><i
                                                class="fa-solid fa-pen-to-square"></i></a>
                                    </div>
                                </div>
                                <div class="card-body pt-1">
                                    <div class="fw-bold text-gray-600 mb-5">Total Permission with this role:
                                        {{ $role->permissions->count() }}
                                    </div>
                                    <div class="d-flex flex-column text-gray-600">
                                        @foreach ($role->permissions->take(5) as $permission)
                                            <div class="d-flex align-items-center py-2">
                                                <span class="bullet bg-primary me-3"></span>{{ $permission->name }}
                                            </div>
                                        @endforeach
                                        @if ($role->permissions->count() > 5)
                                            <div class='d-flex align-items-center py-2'>
                                                <span class='bullet bg-primary me-3'></span>
                                                <em>more...</em>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="card-footer flex-wrap pt-0">
                                    <button type="button" class="btn btn-light btn-active-light-primary my-1 get-role"
                                        data-name={{ $role->slug }} data-id={{ $role->uuid }}
                                        data-action={{ route('admin.role.permission', $role->uuid) }}>Edit
                                        Permission</button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-md-12">
                            <h1 class="">
                                No Role Found
                            </h1>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@section('pageModal')
    <div class="modal fade" id="addRole" tabindex="-1" aria-labelledby="addRoleLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRoleLabel">Role Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addRoleFrm" class="form formSubmit" action="{{ route('admin.role.add') }}">
                    <input type="hidden" name="id" id="id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="title" class="label-style">Role Name</label>
                                    <span class="astrict_sign">*</span>
                                    <input type="text" class="form-control" placeholder="Enter Role" name="name"
                                        id="name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="addRoleBtn" class="btn btn-dark">
                            <span class="indicator-label">Submit</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="{{ asset('assets/js/rolePermission.js') }}"></script>
    <script>
        $(document).on("click", ".editRole", function() {
            const details = JSON.parse($(this).attr('data-id'));
            $('#id').val(details.id);
            $('#name').val(details.name);
        });
    </script>
@endpush
