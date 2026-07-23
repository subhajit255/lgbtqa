@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <!--begin::Toolbar container-->
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <!--begin::Page title-->
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Group Chat Management</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Groups</li>
                        </ul>
                    </div>
                    <!--end::Page title-->
                    <!--begin::Actions-->
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="{{ route('admin.groups.create') }}" class="btn btn-sm fw-bold btn-primary">
                            <i class="fas fa-plus me-1"></i> Create New Group
                        </a>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Toolbar container-->
            </div>
            <!--end::Toolbar-->
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <!--begin::Content container-->
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <!--begin::Row-->
                    <div class="row g-6 g-xl-9">
                        @forelse ($groups as $group)
                            <!--begin::Col-->
                            <div class="col-md-6 col-xl-4">
                                <!--begin::Card-->
                                <div class="card border-hover-primary shadow-sm h-100">
                                    <!--begin::Card header-->
                                    <div class="card-header border-0 pt-9">
                                        <!--begin::Card Title-->
                                        <div class="card-title m-0">
                                            <!--begin::Avatar-->
                                            <div class="symbol symbol-75px symbol-circle m-0">
                                                <div class="symbol-label"
                                                    style="background-image: url('{{ $group->image_path }}'); background-size: cover; background-position: center;">
                                                </div>
                                            </div>
                                            <!--end::Avatar-->
                                        </div>
                                        <!--end::Car Title-->
                                        <!--begin::Card toolbar-->
                                        <div class="card-toolbar">
                                            @if ($group->is_locked)
                                                <span
                                                    class="badge badge-light-danger fw-bold me-auto px-4 py-3">Locked</span>
                                            @elseif($group->is_public)
                                                <span
                                                    class="badge badge-light-success fw-bold me-auto px-4 py-3">Public</span>
                                            @else
                                                <span
                                                    class="badge badge-light-warning fw-bold me-auto px-4 py-3">Private</span>
                                            @endif
                                        </div>
                                        <!--end::Card toolbar-->
                                    </div>
                                    <!--end:: Card header-->
                                    <!--begin:: Card body-->
                                    <div class="card-body p-9">
                                        <!--begin::Name-->
                                        <div class="fs-3 fw-bold text-dark">{{ $group->name }}</div>
                                        <!--end::Name-->
                                        <!--begin::Description-->
                                        <p class="text-gray-400 fw-semibold fs-5 mt-1 mb-7">
                                            {{ Str::limit($group->description, 80) }}</p>
                                        <!--end::Description-->
                                        <!--begin::Info-->
                                        <div class="d-flex flex-wrap mb-5">
                                            <!--begin::Due-->
                                            <div
                                                class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 me-7 mb-3">
                                                <div class="fs-6 text-gray-800 fw-bold participants-count">
                                                    {{ $group->participants_count }}
                                                </div>
                                                <div class="fw-semibold text-gray-400">Participants</div>
                                            </div>
                                            <!--end::Due-->
                                            <!--begin::Budget-->
                                            <div
                                                class="border border-gray-300 border-dashed rounded min-w-125px py-3 px-4 mb-3">
                                                <div class="fs-6 text-gray-800 fw-bold">{{ $group->member_limit }}</div>
                                                <div class="fw-semibold text-gray-400">Limit</div>
                                            </div>
                                            <!--end::Budget-->
                                        </div>
                                        <!--end::Info-->

                                        @if ($group->tags)
                                            <div class="mb-5">
                                                @foreach (explode(',', $group->tags) as $tag)
                                                    <span
                                                        class="badge badge-secondary fs-8 fw-bold me-1">{{ trim($tag) }}</span>
                                                @endforeach
                                            </div>
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mt-auto">
                                            <div class="symbol-group symbol-hover mb-0">
                                                <!-- Optional: Add some user avatars here if needed -->
                                            </div>
                                            <div>
                                                <a href="#" class="btn btn-sm btn-light btn-active-light-primary"
                                                    data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                    Actions
                                                </a>
                                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
                                                    data-kt-menu="true">
                                                    <div class="menu-item px-3">
                                                        <a href="{{ route('admin.groups.edit', $group->id) }}"
                                                            class="menu-link px-3">Edit</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="{{ route('admin.groups.lock', $group->id) }}"
                                                            class="menu-link px-3">
                                                            {{ $group->is_locked ? 'Unlock' : 'Lock' }}
                                                        </a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="javascript:void(0)"
                                                            class="menu-link px-3 manage-members-btn"
                                                            data-group-id="{{ $group->id }}"
                                                            data-group-name="{{ $group->name }}">Manage Members</a>
                                                    </div>
                                                    <div class="menu-item px-3">
                                                        <a href="{{ route('admin.groups.delete', $group->id) }}"
                                                            class="menu-link px-3 text-danger"
                                                            onclick="return confirm('Are you sure?')">Delete</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end:: Card body-->
                                </div>
                                <!--end::Card-->
                            </div>
                            <!--end::Col-->
                        @empty
                            <div class="col-12 text-center py-20">
                                <i class="fas fa-users-slash fs-3x text-gray-300 mb-5"></i>
                                <h3 class="text-gray-600">No groups found</h3>
                                <p class="text-muted">Start by creating your first group chat!</p>
                                <a href="{{ route('admin.groups.create') }}" class="btn btn-primary mt-3">
                                    Create Group
                                </a>
                            </div>
                        @endforelse
                    </div>
                    <!--end::Row-->
                </div>
                <!--end::Content container-->
            </div>
            <!--end::Content-->
        </div>
    </div>
    <!-- Manage Members Modal -->
    <div class="modal fade" id="kt_modal_manage_members" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="fas fa-times fs-2"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-18 pt-0 pb-15">
                    <div class="text-center mb-13">
                        <h1 class="mb-3">Manage Members</h1>
                        <div class="text-muted fw-semibold fs-5">Group: <span id="modal_group_name"
                                class="text-primary"></span></div>
                    </div>

                    <div class="mb-10">
                        <label class="fs-6 fw-semibold mb-2">Search Users to Add</label>
                        <div class="position-relative">
                            <i class="fas fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                            <input type="text" class="form-control form-control-solid px-12" id="user_search_input"
                                placeholder="Search by name or email..." />
                            <div id="search_results"
                                class="position-absolute w-100 bg-white shadow-sm rounded-bottom z-index-2 d-none"
                                style="max-height: 200px; overflow-y: auto; border: 1px solid #eee;"></div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h4 class="fs-5 fw-bold text-gray-800 mb-4">Current Members</h4>
                        <div id="current_members_list" class="scroll-y me-n7 pe-7" style="max-height: 300px;">
                            <!-- Members will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            let activeGroupId = null;

            $(document).on('click', '.manage-members-btn', function() {
                activeGroupId = $(this).data('group-id');
                $('#modal_group_name').text($(this).data('group-name'));
                $('#current_members_list').html(
                    '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>');
                $('#kt_modal_manage_members').modal('show');
                loadMembers();
            });

            function loadMembers() {
                $.get(`/admin/groups/${activeGroupId}/members`, function(res) {
                    if (res.status) {
                        let html = '';
                        res.data.forEach(member => {
                            html += `
                            <div class="d-flex flex-stack py-4 border-bottom border-gray-200">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-35px symbol-circle">
                                        <img src="${member.profile_image}" alt="pic" />
                                    </div>
                                    <div class="ms-3">
                                        <a href="#" class="fs-6 fw-bold text-gray-900 text-hover-primary">${member.name}</a>
                                        <div class="text-muted fs-7">${member.email} ${member.role === 'admin' ? '<span class="badge badge-light-primary fs-9 px-2 py-1 ms-1">Admin</span>' : ''}</div>
                                    </div>
                                </div>
                                ${member.role !== 'admin' ? `
                                                            <button class="btn btn-icon btn-sm btn-light-danger remove-member-btn" data-user-id="${member.id}">
                                                                <i class="fas fa-trash fs-8"></i>
                                                            </button>
                                                        ` : ''}
                            </div>
                        `;
                        });
                        $('#current_members_list').html(html ||
                            '<div class="text-center text-muted py-5">No members found.</div>');
                    }
                });
            }

            $('#user_search_input').on('keyup', function() {
                let search = $(this).val();
                if (search.length < 2) {
                    $('#search_results').addClass('d-none').html('');
                    return;
                }

                $.get(`/admin/groups/${activeGroupId}/search-users`, {
                    search: search
                }, function(res) {
                    if (res.status && res.data.length > 0) {
                        let html = '';
                        res.data.forEach(user => {
                            html += `
                            <div class="d-flex align-items-center p-3 cursor-pointer hover-bg-light add-member-item" data-user-id="${user.id}">
                                <div class="symbol symbol-30px symbol-circle me-3">
                                    <img src="${user.image_path}" />
                                </div>
                                <div>
                                    <div class="fs-7 fw-bold text-gray-900">${user.name}</div>
                                    <div class="fs-8 text-muted">${user.email}</div>
                                </div>
                                <i class="fas fa-plus-circle text-primary ms-auto fs-5"></i>
                            </div>
                        `;
                        });
                        $('#search_results').removeClass('d-none').html(html);
                    } else {
                        $('#search_results').addClass('d-none');
                    }
                });
            });

            $(document).on('click', '.add-member-item', function() {
                let userId = $(this).data('user-id');
                let $item = $(this);

                $.post(`/admin/groups/${activeGroupId}/add-member`, {
                    _token: '{{ csrf_token() }}',
                    user_id: userId
                }, function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        $('#user_search_input').val('');
                        $('#search_results').addClass('d-none');
                        loadMembers();
                        // Update count on card
                        updateParticipantCount(activeGroupId, 1);
                    } else {
                        toastr.error(res.message);
                    }
                });
            });

            $(document).on('click', '.remove-member-btn', function() {
                let userId = $(this).data('user-id');

                Swal.fire({
                    title: "Are you sure?",
                    text: "You want to remove this member from the group?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Yes, remove them!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/admin/groups/${activeGroupId}/remove-member/${userId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(res) {
                                if (res.status) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Success",
                                        text: res.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    });
                                    loadMembers();
                                    // Update count on card
                                    updateParticipantCount(activeGroupId, -1);
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: res.message,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary"
                                        }
                                    });
                                }
                            }
                        });
                    }
                });
            });

            function updateParticipantCount(groupId, delta) {
                let $badge = $(`.manage-members-btn[data-group-id="${groupId}"]`).closest('.card').find('.participants-count');
                let count = parseInt($badge.text()) + delta;
                $badge.text(count);
            }

            // Close search results when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('#user_search_input, #search_results').length) {
                    $('#search_results').addClass('d-none');
                }
            });
        </script>
        <style>
            .hover-bg-light:hover {
                background-color: #f9f9f9;
            }

            .cursor-pointer {
                cursor: pointer;
            }
        </style>
    @endpush
@endsection
