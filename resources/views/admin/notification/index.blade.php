@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Notifications</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Notifications</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card card-flush glass-card">
                        <div class="card-header align-items-center py-5 gap-2 gap-md-5">
                            <div class="card-title">
                                <h3 class="card-label">Notification List</h3>
                            </div>
                            <div class="card-toolbar">
                                <button type="button" class="btn btn-danger btn-sm glass-btn d-none" id="bulk_delete_btn">
                                    <i class="fa fa-trash"></i> Bulk Delete
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <div class="table-responsive">
                                <table class="table align-middle table-row-dashed fs-6 gy-5" id="notification_table">
                                    <thead>
                                        <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                            <th class="w-10px pe-2">
                                                <div
                                                    class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                    <input class="form-check-input glass-checkbox" type="checkbox"
                                                        id="check_all" />
                                                </div>
                                            </th>
                                            <th>Sl No</th>
                                            <th style="width:50%">Notification</th>
                                            <th>Status</th>
                                            <th>Created At</th>
                                            <th class="text-end min-w-70px">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="fw-semibold text-gray-600">
                                        @forelse ($details as $detail)
                                            <tr>
                                                <td>
                                                    <div
                                                        class="form-check form-check-sm form-check-custom form-check-solid">
                                                        <input class="form-check-input glass-checkbox row-checkbox"
                                                            type="checkbox" value="{{ $detail->id }}" />
                                                    </div>
                                                </td>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="d-flex flex-column">
                                                        <span
                                                            class="text-gray-800 fw-bold">{{ $detail->title ?? 'N/A' }}</span>
                                                        <span class="text-muted fs-7">{{ $detail->message ?? '' }}</span>
                                                        @if (isset($detail->chat_id) && $detail->chat_id)
                                                            <a href="{{ route('admin.chat.index', ['chat_id' => $detail->chat_id]) }}"
                                                                class="text-primary fs-7 mt-1">
                                                                View Chat <i class="fa fa-external-link-alt fs-8"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    {!! $detail->is_read == 1
                                                        ? '<span class="badge badge-light-success glass-badge">Read</span>'
                                                        : '<span class="badge badge-light-danger glass-badge">Unread</span>' !!}
                                                </td>
                                                <td>{{ $detail->created_at->format('d M Y, h:i A') }}</td>
                                                <td class="text-end">
                                                    <button type="button"
                                                        class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 glass-btn delete-notification"
                                                        data-id="{{ $detail->id }}">
                                                        <i class="fa fa-trash text-danger"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-10">
                                                    <div class="d-flex flex-column align-items-center">
                                                        <i class="fa fa-bell-slash fs-1 text-muted mb-3"></i>
                                                        <span class="text-muted">No Notifications Found</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                <div class="mt-5">
                                    {!! $details->withQueryString()->links('pagination::bootstrap-5') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        $(document).ready(function() {
            // Handle Check All
            $('#check_all').on('change', function() {
                $('.row-checkbox').prop('checked', $(this).is(':checked'));
                toggleBulkDeleteBtn();
            });

            // Handle Row Checkbox
            $('.row-checkbox').on('change', function() {
                toggleBulkDeleteBtn();
                if ($('.row-checkbox:checked').length == $('.row-checkbox').length) {
                    $('#check_all').prop('checked', true);
                } else {
                    $('#check_all').prop('checked', false);
                }
            });

            function toggleBulkDeleteBtn() {
                if ($('.row-checkbox:checked').length > 0) {
                    $('#bulk_delete_btn').removeClass('d-none');
                } else {
                    $('#bulk_delete_btn').addClass('d-none');
                }
            }

            // Single Delete
            $('.delete-notification').on('click', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    background: 'rgba(255, 255, 255, 0.8)',
                    backdrop: `rgba(0,0,0,0.4)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.notification.delete', '') }}/" + id,
                            type: 'GET',
                            success: function(res) {
                                if (res.status) {
                                    row.fadeOut(300, function() {
                                        $(this).remove();
                                        if ($('#notification_table tbody tr')
                                            .length == 0) {
                                            location.reload();
                                        }
                                    });
                                    toastr.success(res.message);
                                } else {
                                    toastr.error(res.message);
                                }
                            }
                        });
                    }
                });
            });

            // Bulk Delete
            $('#bulk_delete_btn').on('click', function() {
                const ids = [];
                $('.row-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                Swal.fire({
                    title: 'Delete selected notifications?',
                    text: `${ids.length} notifications will be removed forever.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete all!',
                    background: 'rgba(255, 255, 255, 0.8)',
                    backdrop: `rgba(0,0,0,0.4)`
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.notification.bulk-delete') }}",
                            type: 'POST',
                            data: {
                                ids: ids,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(res) {
                                if (res.status) {
                                    toastr.success(res.message);
                                    location.reload();
                                } else {
                                    toastr.error(res.message);
                                }
                            }
                        });
                    }
                });
            });
        });

        function readNotification(notificationId) {
            $.ajax({
                method: 'post',
                url: "{{ route('admin.read.notification') }}",
                data: {
                    notificationId,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    // Optional: Update UI to show as read
                }
            });
        }
    </script>
@endpush
