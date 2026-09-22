@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Subscription Plans</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Subscription Plans</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.plan.add') }}" class="btn btn-sm fw-bold btn-primary"><i class="fas fa-plus me-1"></i> Add Plan</a>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-body pt-6">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-50px">#</th>
                                    <th class="min-w-200px">Plan Name</th>
                                    <th class="min-w-100px">Price</th>
                                    <th class="min-w-100px">Billing Cycle</th>
                                    <th class="min-w-100px">Active</th>
                                    <th class="min-w-120px text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $key => $item)
                                    <tr>
                                        <td>{{ $details->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('admin.plan.add', $item->uuid) }}" class="text-dark fw-bold text-hover-primary fs-6">{{ $item->name }}</a>
                                        </td>
                                        <td><span class="text-gray-600 fw-bold">{{ $item->currency }} {{ $item->price }}</span></td>
                                        <td><span class="badge badge-light-info fs-8">{{ $item->billing_cycle }}</span></td>
                                        <td>
                                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid justify-content-center">
                                                <input class="form-check-input isVerified" type="checkbox" value="{{ $item->is_active ? 1 : 0 }}" data-uuid="{{ $item->uuid }}" data-table="plans" {{ $item->is_active ? 'checked' : '' }} />
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.plan.add', $item->uuid) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="Edit"><i class="fas fa-pencil-alt fs-4"></i></a>
                                            @if($item->subscriptions_count == 0)
                                                <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm delete-item" data-url="{{ route('admin.plan.delete', $item->uuid) }}" title="Delete"><i class="fas fa-trash fs-4"></i></a>
                                            @else
                                                <button class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" title="Cannot delete (Active subscriptions)" disabled><i class="fas fa-trash fs-4 text-muted"></i></button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No plans found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($details->hasPages())
                        <div class="d-flex justify-content-center mt-5">
                            {{ $details->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).on('click', '.delete-item', function() {
                var url = $(this).data('url');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You want to delete this plan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: url,
                            type: 'GET',
                            success: function(response) {
                                if (response.status) {
                                    Swal.fire(
                                        'Deleted!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error!',
                                        response.message,
                                        'error'
                                    );
                                }
                            }
                        });
                    }
                })
            });
        </script>
    @endpush
@endsection
