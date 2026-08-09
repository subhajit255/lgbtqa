@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Community Category List</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Community Category</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <div class="d-flex justify-content-end" data-kt-customer-table-toolbar="base">
                            <button type="button" class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body pt-0">
                            <table class="table align-middle table-row-dashed fs-6 gy-5 dataTable" id="kt_customers_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th>Sl No</th>
                                        <th>Group</th>
                                        <th>Status</th>
                                        <th class="text-end min-w-70px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse ($details as $detail)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td style="text-transform: capitalize;">{{ $detail->group ?? 'N/A' }}</td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input type="checkbox" data-uuid="{{ $detail->id }}"
                                                        data-table="community_categories" class="form-check-input isVerified"
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
                                                        <a class="menu-link px-3 editCategory"
                                                            href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#addCategoryModal" data-id='{{ json_encode($detail) }}'>Edit</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="{{ route('admin.community-category.delete', $detail->id) }}"
                                                            class="menu-link px-3 text-danger">Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
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

@section('pageModal')
    <div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCategoryLabel">Category Add</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="addCategoryFrm" class="form formSubmit" method="POST" action="{{ route('admin.community-category.add') }}">
                    @csrf
                    <input type="hidden" name="id" id="categoryId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-4">
                                    <label for="groupInput" class="label-style fw-bold mb-2">Group</label>
                                    <span class="asterisk_sign text-danger">*</span>
                                    <input type="text" class="form-control" placeholder="Enter Group (e.g. Identity, Lifestyle)" name="group" id="groupInput" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="addCategoryBtn" class="btn btn-dark">
                            <span class="indicator-label">Save</span>
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
    <script>
        $(document).ready(function() {
            // Reset form when modal is opened for "Add"
            $('#addCategoryModal').on('show.bs.modal', function(e) {
                if (!$(e.relatedTarget).hasClass('editCategory')) {
                    $('#addCategoryFrm')[0].reset();
                    $('#categoryId').val('');
                    $('#addCategoryLabel').text('Category Add');
                }
            });

            $(document).on("click", ".editCategory", function() {
                const details = JSON.parse($(this).attr('data-id'));
                $('#categoryId').val(details.id);
                $('#groupInput').val(details.group);
                $('#addCategoryLabel').text('Category Edit');
            });
        });
    </script>
@endpush
