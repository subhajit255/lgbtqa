@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Gallery Management</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Gallery</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card glass-card mb-5 mb-xl-8">
                        <div class="card-header border-0 pt-6 pb-3">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold fs-3 mb-1">Filter Gallery</span>
                                <span class="text-muted mt-1 fw-semibold fs-7">Browse images by uploader</span>
                            </h3>
                        </div>
                        <div class="card-body pt-3 pb-6">
                            <form action="{{ route('admin.gallery.list') }}" method="GET"
                                class="row g-3 g-md-4 align-items-end">
                                <div class="col-12 col-md-6 col-lg-8">
                                    <label class="form-label fw-bold fs-6 mb-2 text-dark">Select User</label>
                                    <select name="user_id" class="form-select glass-input" data-control="select2"
                                        data-placeholder="Select User">
                                        <option value="">All Users</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}"
                                                {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <button type="submit"
                                        class="btn glass-btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2"
                                        style="height: 40px;">
                                        <i class="fas fa-filter"></i>
                                        <span>Filter</span>
                                    </button>
                                </div>
                                <div class="col-6 col-md-3 col-lg-2">
                                    <a href="{{ route('admin.gallery.list') }}"
                                        class="btn glass-btn btn-secondary w-100 d-flex align-items-center justify-content-center gap-2"
                                        style="height: 40px;">
                                        <i class="fas fa-sync"></i>
                                        <span>Reset</span>
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="row g-6 g-xl-9">
                        @forelse($details as $gallery)
                            <div class="col-md-6 col-lg-4 col-xl-3" id="gallery-item-{{ $gallery->id }}">
                                <div class="card card-flush shadow-sm overlay overflow-hidden rounded-3 glass-card">
                                    <div class="overlay-wrapper">
                                        <img src="{{ $gallery->image_path }}" alt="Gallery Image"
                                            class="w-100 h-250px object-fit-cover shadow-sm transition-transform duration-500 hover-scale"
                                            style="object-position: center;">
                                    </div>
                                    <div
                                        class="overlay-layer bg-dark bg-opacity-25 align-items-center justify-content-center p-5 gap-2">
                                        <a href="{{ $gallery->image_path }}" data-fslightbox="gallery"
                                            class="btn btn-primary btn-icon btn-sm fw-bold">
                                            <i class="fas fa-search-plus"></i>
                                        </a>
                                        <button type="button" class="btn btn-danger btn-icon btn-sm delete-gallery-img"
                                            data-uuid="{{ $gallery->uuid }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center">
                                            <div class="symbol symbol-35px me-3">
                                                <img src="{{ $gallery->user->image_path }}" alt="User">
                                            </div>
                                            <div class="d-flex flex-column">
                                                <a href="{{ route('admin.user.view', $gallery->user->uuid) }}"
                                                    class="text-gray-900 text-hover-primary fw-bold fs-7">{{ $gallery->user->name }}</a>
                                                <span
                                                    class="text-muted fw-semibold fs-9">{{ $gallery->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card glass-card p-10 text-center">
                                    <h3 class="text-white">No images found in gallery.</h3>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <div class="mt-10 pagination-custom">
                        {{ $details->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).on('click', '.delete-gallery-img', function() {
                let uuid = $(this).data('uuid');
                let item = $(this).closest('.col-md-6');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.gallery.delete') }}",
                            type: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                uuid: uuid
                            },
                            success: function(response) {
                                if (response.status) {
                                    item.fadeOut(500, function() {
                                        $(this).remove();
                                        refreshFsLightbox();
                                    });
                                    Swal.fire('Deleted!', response.message, 'success');
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('Error!', 'Something went wrong.', 'error');
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection
