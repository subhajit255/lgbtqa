@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Moderation Queue</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Reports</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <form action="{{ route('admin.report.list') }}" method="GET" class="d-flex align-items-center gap-2">
                    <select name="status" class="form-select form-select-sm form-select-solid w-150px" onchange="this.form.submit()">
                        <option value="PENDING" {{ $status == 'PENDING' ? 'selected' : '' }}>Pending</option>
                        <option value="REVIEWED" {{ $status == 'REVIEWED' ? 'selected' : '' }}>Reviewed</option>
                        <option value="ACTION_TAKEN" {{ $status == 'ACTION_TAKEN' ? 'selected' : '' }}>Action Taken</option>
                        <option value="DISMISSED" {{ $status == 'DISMISSED' ? 'selected' : '' }}>Dismissed</option>
                    </select>
                </form>
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
                                <th>Reporter</th>
                                <th>Target Type</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th class="text-end min-w-100px">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 fw-semibold">
                            @forelse($details as $key => $item)
                                <tr>
                                    <td>{{ $details->firstItem() + $key }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-40px me-3">
                                                <img src="{{ $item->reporter->image_path ?? asset('assets/media/avatars/blank.png') }}" class="" alt="" />
                                            </div>
                                            <div class="d-flex justify-content-start flex-column">
                                                <a href="javascript:void(0)" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">{{ $item->reporter->name ?? 'Unknown User' }}</a>
                                                <span class="text-gray-400 fw-semibold d-block fs-7">{{ $item->reporter->email ?? '' }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-light-primary">{{ class_basename($item->reportable_type) }}</span>
                                        <span class="d-block fs-7 mt-1 text-muted">ID: {{ $item->reportable_id }}</span>
                                    </td>
                                    <td>{{ $item->reason }}</td>
                                    <td>
                                        @if($item->status == 'PENDING')
                                            <span class="badge badge-light-warning">Pending</span>
                                        @elseif($item->status == 'REVIEWED')
                                            <span class="badge badge-light-info">Reviewed</span>
                                        @elseif($item->status == 'ACTION_TAKEN')
                                            <span class="badge badge-light-success">Action Taken</span>
                                        @else
                                            <span class="badge badge-light-danger">Dismissed</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="javascript:void(0)" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 editReportBtn" 
                                           data-id="{{ $item->id }}" 
                                           data-status="{{ $item->status }}" 
                                           data-notes="{{ $item->admin_notes }}" 
                                           title="Manage"><i class="fas fa-edit fs-4"></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No reports found for this status.</td>
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

    <!-- Modal for managing report -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <form class="form formSubmit" action="{{ route('admin.report.update-status') }}" method="POST" id="reportForm">
                    @csrf
                    <div class="modal-header">
                        <h2 class="fw-bold">Manage Report</h2>
                        <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                            <i class="fas fa-times fs-1"></i>
                        </div>
                    </div>
                    <div class="modal-body py-10 px-lg-17">
                        <input type="hidden" name="id" id="report_id" value="">
                        
                        <div class="row mb-6">
                            <div class="col-md-12 mb-6">
                                <label class="form-label required">Update Status</label>
                                <select name="status" id="report_status" class="form-select form-select-solid" required>
                                    <option value="PENDING">Pending</option>
                                    <option value="REVIEWED">Reviewed</option>
                                    <option value="ACTION_TAKEN">Action Taken</option>
                                    <option value="DISMISSED">Dismissed</option>
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Admin Notes</label>
                                <textarea name="admin_notes" id="report_notes" class="form-control form-control-solid" rows="4" placeholder="Leave notes about the action taken..."></textarea>
                            </div>
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
            $('.editReportBtn').click(function() {
                $('#report_id').val($(this).data('id'));
                $('#report_status').val($(this).data('status'));
                $('#report_notes').val($(this).data('notes'));
                $('#reportModal').modal('show');
            });
        });
    </script>
@endpush
