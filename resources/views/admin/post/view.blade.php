@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6 border-bottom border-gray-200 shadow-sm bg-white">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack flex-wrap gap-3">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Post Details</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.post.list') }}">Posts</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-dark">Details</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <a href="{{ route('admin.post.list') }}" class="btn btn-sm btn-light-primary fw-bold">
                            <i class="bi bi-arrow-left me-1"></i> Back to Feed
                        </a>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid mt-5">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    
                    <div class="row g-6 g-xl-9">
                        <!-- Left Column: Post Content & Media -->
                        <div class="col-lg-8">
                            <div class="card card-flush shadow-sm mb-6 border-0">
                                <!-- Card Header: Author Info -->
                                <div class="card-header pt-7 pb-5">
                                    <div class="d-flex align-items-center">
                                        <div class="symbol symbol-50px symbol-circle me-4">
                                            @if ($detail->user)
                                                <img src="{{ $detail->user->image_path }}" class="object-fit-cover" alt="User" onerror="this.src='{{ asset('assets/media/avatars/blank.png') }}'">
                                            @else
                                                <div class="symbol-label fs-2 bg-light-primary text-primary fw-bold">A</div>
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column">
                                            <a href="javascript:void(0)" class="fs-4 fw-bold text-gray-900 text-hover-primary mb-1">
                                                {{ $detail->user->name ?? 'Admin' }}
                                            </a>
                                            <span class="text-gray-500 fw-semibold fs-7 d-flex align-items-center">
                                                <i class="bi bi-calendar3 me-1 fs-8"></i>{{ $detail->created_at->format('M d, Y') }}<span class="mx-2">•</span><i class="bi bi-clock me-1 fs-8"></i>{{ $detail->created_at->format('h:i A') }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        <span class="badge badge-light-{{ $detail->status == 'active' ? 'success' : 'warning' }} fw-bold px-4 py-3 rounded-pill fs-7">
                                            <i class="bi bi-{{ $detail->status == 'active' ? 'check-circle' : 'exclamation-triangle' }} text-{{ $detail->status == 'active' ? 'success' : 'warning' }} me-2"></i>{{ ucfirst($detail->status) }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Card Body: Description -->
                                <div class="card-body pt-0 pb-7">
                                    <h3 class="text-gray-900 fw-bolder fs-1 mb-6 lh-sm" style="letter-spacing: -0.5px;">{{ $detail->title }}</h3>
                                    <div class="fs-5 text-gray-800 fw-normal lh-lg mb-8 ps-4 border-start border-4 border-primary bg-light-primary py-4 pe-4 rounded-end">
                                        {!! strip_tags($detail->description) !!}
                                    </div>

                                    <!-- Media Grid -->
                                    @if ($detail->media->count() > 0)
                                        <h4 class="fw-bold text-dark mb-4 fs-4"><i class="bi bi-images me-2 text-primary"></i>Media Attachments ({{ $detail->media->count() }})</h4>
                                        <div class="post-media-grid row g-3">
                                            @php $mediaCount = $detail->media->count(); @endphp
                                            @foreach ($detail->media as $index => $media)
                                                <div class="{{ $mediaCount == 1 ? 'col-12' : ($mediaCount == 2 ? 'col-6' : ($index == 0 && $mediaCount >= 3 ? 'col-12' : 'col-6')) }}">
                                                    @if ($media->file_type == 'image')
                                                        <a class="d-block bgi-no-repeat bgi-size-cover bgi-position-center rounded-3 position-relative preview-gallery hover-elevate-up shadow-sm overflow-hidden border border-gray-300"
                                                            data-fslightbox="post-{{ $detail->id }}"
                                                            href="{{ $media->file_path }}"
                                                            style="background-image: url('{{ $media->file_path }}'); height: {{ $mediaCount == 1 ? '500px' : ($mediaCount == 2 ? '350px' : ($index == 0 ? '400px' : '250px')) }};">
                                                            <div class="position-absolute bottom-0 start-0 w-100 p-3 bg-gradient-dark-bottom opacity-0 hover-opacity-100 transition-300ms text-white fs-7 fw-semibold">
                                                                Click to enlarge
                                                            </div>
                                                        </a>
                                                    @else
                                                        <div class="rounded-3 position-relative shadow-sm overflow-hidden border border-gray-300 bg-black"
                                                            style="height: {{ $mediaCount == 1 ? '500px' : ($mediaCount == 2 ? '350px' : ($index == 0 ? '400px' : '250px')) }};">
                                                            <video controls preload="metadata" playsinline
                                                                class="w-100 h-100 object-fit-contain bg-black">
                                                                <source src="{{ $media->file_path }}">
                                                                Your browser does not support playing videos.
                                                            </video>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Stats & Comments -->
                        <div class="col-lg-4">
                            <!-- Engagement Stats Widget -->
                            <div class="card card-flush shadow-sm mb-6 border-0">
                                <div class="card-header pt-6 pb-2">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark fs-3"><i class="bi bi-activity text-primary me-2"></i>Engagement</span>
                                        <span class="text-gray-400 mt-2 fw-semibold fs-7">Metrics overview from users</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="row g-4">
                                        <!-- Loves -->
                                        <div class="col-6">
                                            <div class="bg-light-danger rounded-4 p-5 text-center hover-elevate-up border border-danger border-dashed">
                                                <i class="bi bi-heart-fill text-danger fs-1 mb-3"></i>
                                                <div class="fs-2 fw-bolder text-gray-900 mb-1">{{ $detail->loves->count() }}</div>
                                                <div class="fs-8 text-gray-600 fw-bold text-uppercase tracking-wider">Loves</div>
                                            </div>
                                        </div>
                                        <!-- Stars -->
                                        <div class="col-6">
                                            <div class="bg-light-warning rounded-4 p-5 text-center hover-elevate-up border border-warning border-dashed">
                                                <i class="bi bi-star-fill text-warning fs-1 mb-3"></i>
                                                <div class="fs-2 fw-bolder text-gray-900 mb-1">{{ $detail->stars->count() }}</div>
                                                <div class="fs-8 text-gray-600 fw-bold text-uppercase tracking-wider">Stars</div>
                                            </div>
                                        </div>
                                        <!-- Emojis -->
                                        <div class="col-6">
                                            <div class="bg-light-success rounded-4 p-5 text-center hover-elevate-up border border-success border-dashed">
                                                <i class="bi bi-emoji-smile-fill text-success fs-1 mb-3"></i>
                                                <div class="fs-2 fw-bolder text-gray-900 mb-1">{{ $detail->emojis->count() }}</div>
                                                <div class="fs-8 text-gray-600 fw-bold text-uppercase tracking-wider">Emojis</div>
                                            </div>
                                        </div>
                                        <!-- Comments -->
                                        <div class="col-6">
                                            <div class="bg-light-info rounded-4 p-5 text-center hover-elevate-up border border-info border-dashed">
                                                <i class="bi bi-chat-fill text-info fs-1 mb-3"></i>
                                                <div class="fs-2 fw-bolder text-gray-900 mb-1">{{ $detail->comments->count() }}</div>
                                                <div class="fs-8 text-gray-600 fw-bold text-uppercase tracking-wider">Comments</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-7">
                                        <a href="{{ route('admin.post.engagements.list', $detail->uuid) }}" class="btn btn-primary d-block fw-bold py-3 shadow-sm hover-elevate-up">
                                            <i class="bi bi-kanban me-2"></i> Manage Engagements
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Recent Comments Widget -->
                            <div class="card card-flush shadow-sm border-0">
                                <div class="card-header pt-6 pb-2">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold text-dark fs-3"><i class="bi bi-chat-quote text-info me-2"></i>Recent Comments</span>
                                        <span class="text-gray-400 mt-2 fw-semibold fs-7">{{ $detail->comments->count() }} comments total</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="timeline-label">
                                        @forelse ($detail->comments->sortByDesc('created_at')->take(5) as $comment)
                                            <div class="timeline-item mb-5">
                                                <div class="timeline-label fw-bold text-gray-500 fs-8 w-40px text-end pe-3">{{ $comment->created_at->format('H:i') }}</div>
                                                <div class="timeline-badge">
                                                    <i class="fa fa-genderless text-info fs-1"></i>
                                                </div>
                                                <div class="timeline-content d-flex flex-column ps-3 bg-light rounded px-3 py-2 ms-2">
                                                    <span class="fw-bold text-gray-900 fs-7 mb-1">{{ $comment->user->name ?? 'Unknown' }}</span>
                                                    <span class="text-gray-700 fw-normal fs-7">{{ $comment->comment }}</span>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="d-flex flex-column align-items-center justify-content-center py-7">
                                                <i class="bi bi-chat-square-text text-gray-300 fs-5x mb-3"></i>
                                                <span class="text-gray-500 fw-semibold">No comments yet.</span>
                                            </div>
                                        @endforelse
                                    </div>
                                    @if($detail->comments->count() > 5)
                                        <div class="text-center mt-5">
                                            <a href="{{ route('admin.post.engagements.list', $detail->uuid) }}" class="btn btn-sm btn-light-primary fw-bold">View All</a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <style>
        .hover-elevate-up {
            transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hover-elevate-up:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
            z-index: 10;
        }
        .symbol-circle img {
            border: 3px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .bg-gradient-dark-bottom {
            background: linear-gradient(180deg, rgba(0,0,0,0) 0%, rgba(0,0,0,0.7) 100%);
        }
        .transition-300ms {
            transition: all 0.3s ease;
        }
        .hover-opacity-100 {
            opacity: 0;
        }
        .preview-gallery:hover .hover-opacity-100 {
            opacity: 1;
        }
        .tracking-wider {
            letter-spacing: 0.05em;
        }
        .timeline-label .timeline-item {
            display: flex;
            align-items: flex-start;
            position: relative;
        }
        .timeline-label .timeline-item:not(:last-child)::before {
            content: '';
            position: absolute;
            left: 48px;
            top: 20px;
            bottom: -20px;
            width: 2px;
            background-color: #E4E6EF;
            z-index: 0;
        }
        .timeline-label .timeline-badge {
            position: relative;
            z-index: 1;
            margin-top: 2px;
        }
        .border-dashed {
            border-style: dashed !important;
        }
    </style>
@endpush

@push('script')
    <script src="//cdnjs.cloudflare.com/ajax/libs/fslightbox/3.0.9/index.min.js"></script>
    <script>
        $(document).ready(function() {
            // Optional: Re-init lightbox if needed dynamically
            if(typeof refreshFsLightbox === "function") {
                refreshFsLightbox();
            }
        });
    </script>
@endpush
