@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Error Log List</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Error Logs</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <button type="button" class="btn btn-danger bulk-delete-btn" style="display:none;">Bulk
                            Delete</button>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body pt-0">
                            <table class="table align-middle table-row-dashed fs-6 gy-5" id="error_logs_table">
                                <thead>
                                    <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                        <th class="w-10px pe-2">
                                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                                    data-kt-check-target="#error_logs_table .form-check-input"
                                                    value="1" />
                                            </div>
                                        </th>
                                        <th class="min-w-50px">Sl No</th>
                                        <th class="min-w-200px">Message</th>
                                        <th class="min-w-150px" style="max-width: 300px;">File Path</th>
                                        <th class="min-w-70px">Line No</th>
                                        <th class="min-w-125px">Date</th>
                                        <th class="text-end min-w-70px">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="fw-semibold text-gray-600">
                                    @forelse ($details as $detail)
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                    <input class="form-check-input log-checkbox" type="checkbox"
                                                        value="{{ $detail->id }}" />
                                                </div>
                                            </td>
                                            <td>{{ $loop->iteration + ($details->currentPage() - 1) * $details->perPage() }}
                                            </td>
                                            <td>
                                                <div class="text-gray-800 text-hover-primary mb-1">
                                                    {{ Str::limit($detail->message, 100) }}</div>
                                            </td>
                                            <td>
                                                <div class="text-gray-800 mb-1"
                                                    style="word-break: break-all; max-width: 300px;">
                                                    {{ $detail->file_path }}
                                                </div>
                                            </td>
                                            <td>{{ $detail->line_number }}</td>
                                            <td>{{ \Carbon\Carbon::parse($detail->created_at)->format('jS F Y, h:i A') }}
                                            </td>
                                            <td class="text-end">
                                                <a href="javascript:void(0)"
                                                    class="btn btn-sm btn-light btn-active-light-primary"
                                                    data-kt-menu-trigger="click"
                                                    data-kt-menu-placement="bottom-end">Actions</a>
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                                    data-kt-menu="true">
                                                    <div class="menu-item px-3">
                                                        <a class="menu-link px-3"
                                                            href="{{ route('admin.log-error.view', $detail->id) }}">View</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a class="menu-link px-3 delete-confirm"
                                                            href="{{ route('admin.log-error.delete', $detail->id) }}"
                                                            data-message="Are you sure you want to delete this log?">Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7">
                                                @include('partials.empty_state', [
                                                    'title' => 'No Error Logs',
                                                    'description' =>
                                                        "Everything is running smoothly! We couldn't find any error logs in the system.",
                                                ])
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {!! $details->withQueryString()->links('pagination::bootstrap-5') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).ready(function() {
                // Checkbox handling for bulk delete button visibility
                $(document).on('change', '.form-check-input', function() {
                    var checkedCount = $('.log-checkbox:checked').length;
                    if (checkedCount > 0) {
                        $('.bulk-delete-btn').show();
                    } else {
                        $('.bulk-delete-btn').hide();
                    }
                });

                // Bulk Delete Click
                $('.bulk-delete-btn').click(function() {
                    var ids = [];
                    $('.log-checkbox:checked').each(function() {
                        ids.push($(this).val());
                    });

                    if (ids.length > 0) {
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You want to delete selected error logs!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: "{{ route('admin.log-error.bulk-delete') }}",
                                    type: 'POST',
                                    data: {
                                        ids: ids,
                                        _token: '{{ csrf_token() }}'
                                    },
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
                        });
                    }
                });

                // Single Delete confirmation (if not handled globally)
                $(document).on('click', '.delete-confirm', function(e) {
                    e.preventDefault();
                    var url = $(this).attr('href');
                    var message = $(this).data('message') || "Are you sure you want to delete this record?";

                    Swal.fire({
                        title: 'Are you sure?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, delete it!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = url;
                        }
                    });
                });
            });
        </script>
    @endpush
@endsection
