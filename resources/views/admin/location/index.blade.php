@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Locations</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Locations</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.location.add') }}" class="btn btn-sm fw-bold btn-primary"><i class="fas fa-plus me-1"></i> Add Location</a>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-header border-0 pt-6">
                    <div class="card-title">
                        <form action="" method="GET" class="d-flex align-items-center gap-3">
                            <div class="position-relative">
                                <span class="svg-icon svg-icon-1 position-absolute ms-6 mt-3"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="keyword" class="form-control form-control-solid w-250px ps-14" placeholder="Search locations..." value="{{ request('keyword') }}" />
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body pt-0">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-50px">#</th>
                                    <th class="min-w-200px">Location Name</th>
                                    <th class="min-w-200px">Address</th>
                                    <th class="min-w-100px">Lifecycle Status</th>
                                    <th class="min-w-100px">Active</th>
                                    <th class="min-w-120px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $key => $item)
                                    <tr>
                                        <td>{{ $details->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('admin.location.add', $item->uuid) }}" class="text-dark fw-bold text-hover-primary fs-6">{{ $item->name }}</a>
                                        </td>
                                        <td><span class="text-gray-600 fs-7">{{ $item->address ?? '-' }}</span></td>
                                        <td><span class="badge badge-light-primary fs-8">{{ str_replace('_', ' ', $item->status) }}</span></td>
                                        <td>
                                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid justify-content-center">
                                                <input class="form-check-input isVerified" type="checkbox" value="{{ $item->is_active ? 1 : 0 }}" data-uuid="{{ $item->uuid }}" data-table="locations" {{ $item->is_active ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.location.add', $item->uuid) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Edit"><i class="fas fa-pencil-alt fs-4"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-10"><span class="text-muted">No locations found.</span></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">{{ $details->links() }}</div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            function deleteItem(url) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get(url, function(response) {
                            if (response.status) {
                                toastr.success(response.message);
                                setTimeout(() => location.reload(), 1000);
                            } else {
                                toastr.error(response.message);
                            }
                        });
                    }
                });
            }
        </script>
    @endpush
@endsection