@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Campaign Blocks</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Featured & Weekend Picks</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <form action="{{ route('admin.campaign-block.list') }}" method="GET" class="d-flex align-items-center gap-2 me-3">
                    <select name="status" class="form-select form-select-sm form-select-solid w-150px" onchange="this.form.submit()">
                        <option value="SCHEDULED" {{ $status == 'SCHEDULED' ? 'selected' : '' }}>Scheduled</option>
                        <option value="ACTIVE" {{ $status == 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="COMPLETED" {{ $status == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                        <option value="CANCELLED" {{ $status == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </form>
                <a href="{{ route('admin.campaign-block.add') }}" class="btn btn-sm fw-bold btn-primary"><i class="fas fa-calendar-plus me-1"></i> Schedule Block</a>
            </div>
        </div>
    </div>
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-body py-4">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                <th>Sl.No</th>
                                <th>Target Item</th>
                                <th>Campaign Type</th>
                                <th>Dates</th>
                                <th>Status</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($details as $key => $item)
                                <tr>
                                    <td>{{ $details->firstItem() + $key }}</td>
                                    <td>
                                        <span class="badge badge-light-primary mb-1">{{ class_basename($item->target_type) }}</span>
                                        <br>
                                        <span class="fw-bold text-gray-800">{{ $item->target->title ?? $item->target->name ?? 'Unknown' }}</span>
                                    </td>
                                    <td>
                                        @if($item->type == 'FEATURED')
                                            <span class="badge badge-light-info">Featured Event</span>
                                        @else
                                            <span class="badge badge-light-warning">Weekend Pick</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($item->start_date)->format('d M, Y') }} <br>
                                        to {{ \Carbon\Carbon::parse($item->end_date)->format('d M, Y') }}
                                    </td>
                                    <td>
                                        <span class="badge badge-light-{{ $item->status == 'ACTIVE' ? 'success' : ($item->status == 'SCHEDULED' ? 'primary' : 'secondary') }}">
                                            {{ $item->status }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 editStatusBtn" 
                                           data-id="{{ $item->id }}" 
                                           data-status="{{ $item->status }}" 
                                           title="Update Status"><i class="fas fa-edit fs-4"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No campaign blocks found for this status.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="d-flex justify-content-end mt-5">
                        {{ $details->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for updating status -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-400px">
            <div class="modal-content">
                <form class="form formSubmit" action="{{ route('admin.campaign-block.update-status') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h2 class="fw-bold">Update Status</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <input type="hidden" name="id" id="block_id" value="">
                        
                        <div class="mb-6">
                            <label class="form-label required">Status</label>
                            <select name="status" id="block_status" class="form-select form-select-solid" required>
                                <option value="ACTIVE">Active</option>
                                <option value="COMPLETED">Completed</option>
                                <option value="CANCELLED">Cancelled</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer flex-center">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Discard</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="indicator-label">Save Changes</span>
                            <span class="indicator-progress">Please wait...<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
            $('.editStatusBtn').click(function() {
                $('#block_id').val($(this).data('id'));
                $('#block_status').val($(this).data('status'));
                $('#statusModal').modal('show');
            });
        });
    </script>
@endpush
