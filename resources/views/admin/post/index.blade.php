@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Post Feed</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Posts</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <button type="button" class="btn btn-primary btn-sm goTo"
                            data-action="{{ route('admin.post.add') }}">
                            <i class="bi bi-plus-lg fs-4 me-1"></i>Create New Post
                        </button>
                    </div>
                </div>
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="row g-6 g-xl-9">
                        @forelse ($details as $detail)
                            <div class="col-md-6 col-xl-4">
                                <div class="card h-100 border-0 shadow-sm hover-elevate-up overflow-hidden">
                                    <div class="card-header border-0 pt-6 px-7 bg-transparent">
                                        <div class="card-title m-0">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-45px symbol-circle me-3">
                                                    @if ($detail->user)
                                                        <img src="{{ $detail->user->image_path }}" alt="User">
                                                    @else
                                                        <div
                                                            class="symbol-label fs-3 bg-light-primary text-primary fw-bold">
                                                            A</div>
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <a href="#"
                                                        class="fs-6 fw-bold text-gray-900 text-hover-primary lh-1 mb-1">
                                                        {{ $detail->user->name ?? 'Admin' }}
                                                    </a>
                                                    <span class="text-gray-400 fw-semibold fs-7 ls-1">
                                                        {{ $detail->created_at->diffForHumans() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-toolbar">
                                            <button type="button"
                                                class="btn btn-clean btn-sm btn-icon btn-icon-primary btn-active-light-primary"
                                                data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                                                <i class="bi bi-three-dots-vertical fs-3"></i>
                                            </button>
                                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-150px py-3"
                                                data-kt-menu="true">
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('admin.post.view', $detail->uuid) }}"
                                                        class="menu-link px-3">
                                                        <i class="bi bi-eye me-2"></i>View Details
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('admin.post.add', $detail->uuid) }}"
                                                        class="menu-link px-3">
                                                        <i class="bi bi-pencil me-2"></i>Edit Post
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="{{ route('admin.post.engagements.list', $detail->uuid) }}"
                                                        class="menu-link px-3">
                                                        <i class="bi bi-chat-right-text me-2"></i>Engagements
                                                    </a>
                                                </div>
                                                <div class="menu-item px-3">
                                                    <a href="javascript:void(0)" data-table="posts"
                                                        data-uuid="{{ $detail->uuid }}"
                                                        class="menu-link px-3 deleteData text-danger">
                                                        <i class="bi bi-trash me-2 text-danger"></i>Delete Post
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card-body p-0">
                                        <div class="px-7 pt-4 pb-2">
                                            <h4 class="text-gray-900 fw-bold mb-3 fs-5">{{ $detail->title }}</h4>
                                            <div class="text-gray-600 fw-semibold fs-6 mb-4 post-description">
                                                {!! Str::limit(strip_tags($detail->description), 120) !!}
                                            </div>
                                        </div>

                                        @if ($detail->media->count() > 0)
                                            <div class="post-media-grid row g-1 px-1">
                                                @php $mediaCount = $detail->media->count(); @endphp
                                                @foreach ($detail->media->take(4) as $index => $media)
                                                    <div
                                                        class="{{ $mediaCount == 1 ? 'col-12' : ($mediaCount == 2 ? 'col-6' : ($index == 0 && $mediaCount >= 3 ? 'col-12' : 'col-4')) }}">
                                                        @if ($media->file_type == 'image')
                                                            <a class="d-block w-100 h-100 position-relative overflow-hidden cursor-pointer preview-gallery"
                                                                data-fslightbox="post-{{ $detail->id }}"
                                                                href="{{ $media->file_path }}"
                                                                style="height: {{ $mediaCount == 1 ? '250px' : ($mediaCount == 2 ? '150px' : ($index == 0 ? '180px' : '100px')) }}">
                                                                <img src="{{ $media->file_path }}"
                                                                    class="w-100 h-100 object-fit-cover rounded-sm"
                                                                    alt="Post media">
                                                            </a>
                                                        @else
                                                            <div class="w-100 position-relative overflow-hidden rounded-sm bg-black"
                                                                style="height: {{ $mediaCount == 1 ? '250px' : ($mediaCount == 2 ? '150px' : ($index == 0 ? '180px' : '100px')) }}">
                                                                <video controls preload="metadata" playsinline
                                                                    class="w-100 h-100 object-fit-cover rounded-sm" style="background-color: #000;">
                                                                    <source src="{{ $media->file_path }}">
                                                                    Your browser does not support the video tag.
                                                                </video>
                                                            </div>
                                                        @endif

                                                        @if ($index == 3 && $mediaCount > 4)
                                                            <div
                                                                class="position-absolute top-0 start-0 w-100 h-100 bg-black bg-opacity-50 d-flex align-items-center justify-content-center pointer-events-none">
                                                                <span
                                                                    class="text-white fw-bold fs-2">+{{ $mediaCount - 4 }}</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="card-footer border-0 px-7 pb-6 pt-4 bg-transparent">
                                        <div class="d-flex flex-stack">
                                            <div class="d-flex align-items-center">
                                                <span
                                                    class="badge badge-light-{{ $detail->status == 'active' ? 'success' : 'warning' }} fw-bold px-4 py-3">
                                                    {{ ucfirst($detail->status) }}
                                                </span>
                                            </div>

                                            <div class="d-flex align-items-center">
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input h-20px w-30px isVerified" type="checkbox"
                                                        data-uuid="{{ $detail->uuid }}" data-table="posts"
                                                        value="{{ $detail->status == 'active' ? 1 : 0 }}"
                                                        {{ $detail->status == 'active' ? 'checked' : '' }} />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                @include('partials.empty_state', [
                                    'title' => 'No Posts Found',
                                    'description' =>
                                        'Your feed is currently empty. Be the first to share something with the community!',
                                    'action_url' => route('admin.post.add'),
                                    'action_text' => 'Create New Post',
                                    'action_icon' => 'bi bi-plus-lg',
                                ])
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .post-description {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .hover-elevate-up {
            transition: transform 0.3s ease;
        }

        .hover-elevate-up:hover {
            transform: translateY(-8px);
        }

        .object-fit-cover {
            object-fit: cover;
        }

        .rounded-sm {
            border-radius: 4px;
        }
    </style>
@endpush
@push('script')
    <script src="//cdnjs.cloudflare.com/ajax/libs/fslightbox/3.0.9/index.min.js"></script>
@endpush
