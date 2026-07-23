@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Status (Stories) Management</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Status Management</li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Live Feed</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <button type="button" class="btn btn-light-primary btn-sm" id="btnPlayAll">
                            <i class="bi bi-play-fill fs-3 me-1"></i>Watch Active Stories
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createStatusModal">
                            <i class="bi bi-plus-lg fs-4 me-1"></i>Post New Status
                        </button>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    
                    <!-- Navigation Tabs -->
                    <div class="d-flex flex-stack flex-wrap mb-7">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary py-4 me-10 {{ Route::currentRouteName() == 'admin.status.list' ? 'active' : '' }}" href="{{ route('admin.status.list') }}">Live Feed</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary py-4 {{ Route::currentRouteName() == 'admin.status.students' ? 'active' : '' }}" href="{{ route('admin.status.students') }}">Student Directory</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Statistics Bar -->
                    <div class="row g-5 mb-7">
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0 bg-light-primary">
                                <div class="card-body p-5 d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-white text-primary"><i class="bi bi-broadcast fs-2 text-primary"></i></div>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-gray-800">Live & Active</div>
                                        <div class="fs-7 text-muted fw-semibold">Current stories visible to users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0 bg-light-danger">
                                <div class="card-body p-5 d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-white text-danger"><i class="bi bi-clock-history fs-2 text-danger"></i></div>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-gray-800">History Log</div>
                                        <div class="fs-7 text-muted fw-semibold">Database of expired status updates</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 shadow-sm border-0 bg-light-success">
                                <div class="card-body p-5 d-flex align-items-center">
                                    <div class="symbol symbol-40px me-3">
                                        <div class="symbol-label bg-white text-success"><i class="bi bi-people fs-2 text-success"></i></div>
                                    </div>
                                    <div>
                                        <div class="fs-4 fw-bold text-gray-800">Multi-User Posting</div>
                                        <div class="fs-7 text-muted fw-semibold">Admins posting on behalf of users</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters & Search Card -->
                    <div class="card mb-7 shadow-sm border-0">
                        <div class="card-body py-4 d-flex flex-stack flex-wrap gap-4">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.status.list') }}" class="btn btn-sm btn-light-primary fw-bold {{ !request('filter') ? 'active' : '' }}">All Statuses</a>
                                <a href="{{ route('admin.status.list', ['filter' => 'active']) }}" class="btn btn-sm btn-light-success fw-bold {{ request('filter') == 'active' ? 'active' : '' }}">Active</a>
                                <a href="{{ route('admin.status.list', ['filter' => 'expired']) }}" class="btn btn-sm btn-light-danger fw-bold {{ request('filter') == 'expired' ? 'active' : '' }}">Expired</a>
                            </div>

                            <form action="{{ route('admin.status.list') }}" method="GET" class="d-flex align-items-center position-relative my-1">
                                <i class="bi bi-search position-absolute ms-3"></i>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid w-250px ps-10" placeholder="Search by Student Name...">
                                @if(request('filter'))
                                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                                @endif
                                @if(request('user_id'))
                                    <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                                @endif
                                <button type="submit" class="btn btn-secondary btn-sm ms-2">Go</button>
                                @if(request('search') || request('user_id'))
                                    <a href="{{ route('admin.status.list') }}" class="btn btn-icon btn-light-danger btn-sm ms-2" title="Clear Filters"><i class="bi bi-x fs-3"></i></a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Filter Title (If user_id is set) -->
                    @if(request('user_id') && $statuses->count() > 0)
                        <div class="mb-5 bg-light-primary p-4 rounded d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-35px symbol-circle me-3">
                                    <img src="{{ $statuses->first()->user->image_path }}" alt="">
                                </div>
                                <span class="fw-bold fs-5 text-gray-800">Showing Status History for: {{ $statuses->first()->user->name }}</span>
                            </div>
                        </div>
                    @endif

                    <!-- Status Grid -->
                    <div class="row g-6 g-xl-9">
                        @forelse ($statuses as $status)
                            <div class="col-md-6 col-xl-3 col-xxl-3">
                                <div class="card h-100 border-0 shadow-sm hover-elevate-up overflow-hidden status-card {{ $status->expires_at < now() ? 'expired' : '' }}">
                                    <!-- Card Header -->
                                    <div class="card-header border-0 pt-6 px-7 bg-transparent flex-nowrap align-items-start">
                                        <div class="card-title m-0 flex-grow-1">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-35px symbol-circle me-3 flex-shrink-0">
                                                    @if ($status->user)
                                                        <img src="{{ $status->user->image_path }}" alt="User">
                                                    @endif
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <a href="{{ route('admin.status.list', ['user_id' => $status->user_id]) }}" class="fs-7 fw-bold text-gray-900 text-hover-primary lh-1 mb-1">
                                                        {{ $status->user->name ?? 'System' }}
                                                    </a>
                                                    @if($status->taggedUser)
                                                        <span class="text-primary fw-bold fs-10">
                                                            tagged <a href="{{ route('admin.status.list', ['user_id' => $status->tagged_user_id]) }}" class="text-primary text-hover-dark">{{ $status->taggedUser->name }}</a>
                                                        </span>
                                                    @else
                                                        <!-- Placeholder for consistent height -->
                                                        <span class="fs-10 opacity-0">&nbsp;</span>
                                                    @endif
                                                    <span class="text-gray-400 fw-semibold fs-10 mt-1">
                                                        {{ $status->created_at->format('M d, h:i A') }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card-toolbar m-0 gap-1 flex-shrink-0">
                                            <button type="button" class="btn btn-icon btn-sm btn-color-gray-400 btn-active-color-primary viewStory" data-id="{{ $status->id }}" title="View Story">
                                                <i class="bi bi-play-circle fs-4"></i>
                                            </button>
                                            <button type="button" class="btn btn-icon btn-sm btn-color-gray-400 btn-active-color-info viewEngagement" data-id="{{ $status->id }}" title="View Engagement">
                                                <i class="bi bi-graph-up fs-4"></i>
                                            </button>
                                            @if(auth()->user()->role == 'admin')
                                                <button type="button" class="btn btn-icon btn-sm btn-color-gray-400 btn-active-color-danger deleteStatus" data-id="{{ $status->id }}" title="Delete Story">
                                                    <i class="bi bi-trash fs-4"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Card Body -->
                                    <div class="card-body px-7 py-5">
                                        <div class="status-preview-container rounded-3 mb-4 d-flex align-items-center justify-content-center overflow-hidden cursor-pointer viewStory" 
                                             data-id="{{ $status->id }}"
                                             style="height: 180px; background-color: {{ $status->background_color ?? '#f8f9fa' }}; position: relative;">
                                            @if($status->type == 'text')
                                                <div class="px-5 text-center">
                                                    <p class="fs-6 fw-bold mb-0" style="color: {{ $status->background_color ? '#fff' : '#333' }}; text-shadow: {{ $status->background_color ? '0 1px 2px rgba(0,0,0,0.2)' : 'none' }};">
                                                        {{ Str::limit($status->content, 120) }}
                                                    </p>
                                                </div>
                                            @elseif($status->type == 'image')
                                                <img src="{{ asset('storage/' . $status->content) }}" class="w-100 h-100 object-fit-cover" alt="Status Image">
                                            @elseif($status->type == 'video')
                                                <video class="w-100 h-100 object-fit-cover">
                                                    <source src="{{ asset('storage/' . $status->content) }}" type="video/mp4">
                                                </video>
                                                <div class="position-absolute top-50 start-50 translate-middle">
                                                    <i class="bi bi-play-circle-fill fs-1 text-white opacity-75"></i>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Meta Info -->
                                        <div class="d-flex flex-stack border-top pt-4 border-gray-100">
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center mb-1">
                                                    <span class="badge badge-light-{{ $status->expires_at < now() ? 'danger' : 'success' }} fw-bold fs-10 px-2 py-1">
                                                        {{ $status->expires_at < now() ? 'Expired' : 'Live' }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-3">
                                                <div class="text-center">
                                                    <span class="d-block fw-bold text-gray-800 fs-7">{{ $status->reactions_count }}</span>
                                                    <span class="text-gray-400 fs-10"><i class="bi bi-heart-fill me-1 text-danger"></i>React</span>
                                                </div>
                                                <div class="text-center">
                                                    <span class="d-block fw-bold text-gray-800 fs-7">{{ $status->comments_count }}</span>
                                                    <span class="text-gray-400 fs-10"><i class="bi bi-chat-fill me-1 text-primary"></i>Comm</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card border-0 shadow-sm">
                                    <div class="card-body p-20 text-center">
                                        <img src="{{ asset('assets/media/svg/illustrations/easy/1.svg') }}" class="h-100px mb-10" alt="">
                                        <h3 class="fs-2 fw-bold mb-5">No Stories Found</h3>
                                        <p class="text-gray-400 fs-5 fw-semibold">Try adjusting your filters or post a new status above.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex flex-stack flex-wrap pt-10">
                        <div class="fs-6 fw-semibold text-gray-700">
                            Showing {{ $statuses->firstItem() ?? 0 }} to {{ $statuses->lastItem() ?? 0 }} of {{ $statuses->total() }} statuses
                        </div>
                        {{ $statuses->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Status Modal -->
    <div class="modal fade" id="createStatusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content rounded">
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg fs-2"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                    <form id="createStatusForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-13 text-center">
                            <h1 class="mb-3">Post New Status</h1>
                            <div class="text-muted fw-semibold fs-5">Publish a temporary update for a user.</div>
                        </div>

                        <!-- Quick Toggle: Post as Me -->
                        <div class="d-flex flex-stack mb-8">
                            <div class="me-5">
                                <label class="fs-6 fw-bold">Post as Yourself (Admin)?</label>
                                <div class="fs-7 text-muted">Toggle this to automatically post as {{ auth()->user()->name ?? 'Super Admin' }}</div>
                            </div>
                            <label class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="post_as_me_toggle" />
                            </label>
                        </div>

                        <!-- User Selection -->
                        <div class="d-flex flex-column mb-8 fv-row" id="user_search_wrapper">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                <span class="required">Select Target Student</span>
                            </label>
                            <div class="position-relative">
                                <i class="bi bi-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                                <input type="text" id="user_search_input" class="form-control form-control-solid ps-12" placeholder="Start typing name or email...">
                                <input type="hidden" name="user_id" id="selected_user_id">
                                <div id="user_search_results" class="position-absolute w-100 bg-white shadow-lg rounded-bottom z-index-2 d-none mt-1" style="max-height: 250px; overflow-y: auto; border: 1px solid #eee;"></div>
                            </div>
                            <div id="selected_user_preview" class="mt-3 p-3 bg-light-primary rounded d-none justify-content-between align-items-center border border-primary border-dashed">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-30px symbol-circle me-3">
                                        <div class="symbol-label bg-primary text-white fs-9" id="preview_user_letter"></div>
                                    </div>
                                    <div class="fs-7 fw-bold text-gray-800" id="preview_user_name"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-icon btn-active-light-danger" id="clear_selected_user"><i class="bi bi-x-circle fs-3"></i></button>
                            </div>
                        </div>
                        
                        <!-- Tagged User Selection (Optional) -->
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                                <span>Tag Another Student (Optional)</span>
                            </label>
                            <div class="position-relative">
                                <i class="bi bi-person-plus position-absolute top-50 translate-middle-y ms-4 text-gray-400"></i>
                                <input type="text" id="tag_user_search_input" class="form-control form-control-solid ps-12" placeholder="Search to tag someone...">
                                <input type="hidden" name="tagged_user_id" id="selected_tag_user_id">
                                <div id="tag_user_search_results" class="position-absolute w-100 bg-white shadow-lg rounded-bottom z-index-2 d-none mt-1" style="max-height: 250px; overflow-y: auto; border: 1px solid #eee;"></div>
                            </div>
                            <div id="selected_tag_user_preview" class="mt-3 p-3 bg-light-info rounded d-none justify-content-between align-items-center border border-info border-dashed">
                                <div class="d-flex align-items-center">
                                    <div class="symbol symbol-30px symbol-circle me-3">
                                        <div class="symbol-label bg-info text-white fs-9" id="preview_tag_user_letter"></div>
                                    </div>
                                    <div class="fs-7 fw-bold text-info" id="preview_tag_user_name"></div>
                                </div>
                                <button type="button" class="btn btn-sm btn-icon btn-active-light-danger" id="clear_selected_tag_user"><i class="bi bi-x-circle fs-3"></i></button>
                            </div>
                        </div>

                        <!-- Status Type -->
                        <div class="row g-9 mb-8">
                            <div class="col-md-4">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-stack text-start p-3 mb-5 active" id="btn_type_text">
                                    <div class="d-flex align-items-center me-2">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm me-3">
                                            <input class="form-check-input" type="radio" name="type" value="text" checked="checked" />
                                        </div>
                                        <div class="flex-grow-1"><span class="d-block fw-bold fs-8">Text</span></div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-stack text-start p-3 mb-5" id="btn_type_image">
                                    <div class="d-flex align-items-center me-2">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm me-3">
                                            <input class="form-check-input" type="radio" name="type" value="image" />
                                        </div>
                                        <div class="flex-grow-1"><span class="d-block fw-bold fs-8">Image</span></div>
                                    </div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <label class="btn btn-outline btn-outline-dashed btn-active-light-primary d-flex flex-stack text-start p-3 mb-5" id="btn_type_video">
                                    <div class="d-flex align-items-center me-2">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm me-3">
                                            <input class="form-check-input" type="radio" name="type" value="video" />
                                        </div>
                                        <div class="flex-grow-1"><span class="d-block fw-bold fs-8">Video</span></div>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- Text Content -->
                        <div id="text_wrapper" class="mb-8">
                            <label class="fs-6 fw-semibold mb-2">Message</label>
                            <textarea name="content" class="form-control form-control-solid mb-4" rows="3" placeholder="Type your story message..."></textarea>
                            <div class="d-flex align-items-center">
                                <label class="fs-7 fw-bold me-3">Background:</label>
                                <div class="d-flex gap-3 mt-1">
                                    <div class="color-circle active" style="background:#6f42c1" data-color="#6f42c1"></div>
                                    <div class="color-circle" style="background:#009ef7" data-color="#009ef7"></div>
                                    <div class="color-circle" style="background:#50cd89" data-color="#50cd89"></div>
                                    <div class="color-circle" style="background:#f1416c" data-color="#f1416c"></div>
                                    <div class="color-circle" style="background:#ffc107" data-color="#ffc107"></div>
                                    <input type="color" name="background_color" id="bg_color_input" class="visually-hidden" value="#6f42c1">
                                </div>
                            </div>
                        </div>

                        <!-- Media Content -->
                        <div id="media_wrapper" class="mb-8 d-none">
                            <label class="fs-6 fw-semibold mb-2">Upload File</label>
                            <input type="file" name="media_file" class="form-control form-control-solid" accept="image/*">
                        </div>

                        <div class="text-center">
                            <button type="submit" id="submitBtn" class="btn btn-primary w-100">
                                <span class="indicator-label">Post Status</span>
                                <span class="indicator-progress">Processing... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Engagement Details Modal -->
    <div class="modal fade" id="statusDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="fw-bold">Story Engagement</h2>
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg fs-2"></i>
                    </div>
                </div>
                <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                    <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                        <li class="nav-item">
                            <a class="nav-link active fw-bold" data-bs-toggle="tab" href="#kt_tab_pane_reactions">Reactions</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold" data-bs-toggle="tab" href="#kt_tab_pane_comments">Comments</a>
                        </li>
                    </ul>
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade show active" id="kt_tab_pane_reactions" role="tabpanel">
                            <div id="reactionsList" style="max-height: 400px; overflow-y: auto;"></div>
                        </div>
                        <div class="tab-pane fade" id="kt_tab_pane_comments" role="tabpanel">
                            <div id="commentsList" style="max-height: 400px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Immersive Story Viewer Modal -->
    <div class="modal fade" id="storyViewerModal" tabindex="-1" aria-hidden="true" style="background: rgba(0,0,0,0.98);">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 d-flex align-items-center justify-content-center position-relative">
                    <!-- Top Overlay (Progress + User Info) -->
                    <div class="position-absolute top-0 start-0 w-100 px-5 px-lg-10 pt-5 pt-lg-7" style="z-index: 110;">
                        <!-- Progress Bar (Absolute Top) -->
                        <div class="d-flex gap-1 mb-5" id="story_segments"></div>
                        
                        <!-- User Info (Right below progress) -->
                        <div class="d-flex align-items-center">
                            <div class="symbol symbol-40px symbol-circle me-3 border border-2 border-white shadow-sm">
                                <img id="viewer_avatar" src="" alt="">
                            </div>
                            <div class="d-flex flex-column flex-grow-1">
                                <span class="text-white fw-bold fs-6 shadow-sm" id="viewer_name"></span>
                                <span class="text-white-50 fs-8" id="viewer_time"></span>
                            </div>
                            <button type="button" class="btn btn-icon btn-sm btn-color-white btn-active-color-primary ms-5" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg fs-2"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Navigation Arrows -->
                    <button class="btn btn-icon btn-sm btn-white btn-active-primary position-absolute start-0 ms-5 ms-lg-10 d-none d-lg-flex" id="prevStory" style="z-index: 101; top: 50%;">
                        <i class="bi bi-chevron-left fs-1"></i>
                    </button>
                    <button class="btn btn-icon btn-sm btn-white btn-active-primary position-absolute end-0 me-5 ms-lg-10 d-none d-lg-flex" id="nextStory" style="z-index: 101; top: 50%;">
                        <i class="bi bi-chevron-right fs-1"></i>
                    </button>

                    <!-- Content Container -->
                    <div id="viewer_content" class="w-100 h-100 d-flex align-items-center justify-content-center px-4" style="max-width: 500px;">
                        <!-- Text/Image/Video injected here -->
                    </div>

                    <!-- Interactions (Bottom) -->
                    <div class="position-absolute bottom-0 w-100 p-10 text-center">
                        <div class="d-flex justify-content-center gap-10">
                            <div class="text-white">
                                <i class="bi bi-heart-fill fs-2 text-danger mb-1 d-block"></i>
                                <span class="fw-bold fs-6" id="viewer_reactions">0</span>
                            </div>
                            <div class="text-white">
                                <i class="bi bi-chat-fill fs-2 text-primary mb-1 d-block"></i>
                                <span class="fw-bold fs-6" id="viewer_comments">0</span>
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
        .hover-elevate-up { transition: all 0.3s ease; }
        .hover-elevate-up:hover { transform: translateY(-8px); box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075) !important; }
        .status-card.expired { opacity: 0.8; }
        .status-card.expired::after {
            content: 'EXPIRED'; position: absolute; top: 12px; right: -25px;
            background: #adb5bd; color: white; padding: 2px 30px;
            transform: rotate(45deg); font-weight: bold; font-size: 0.6rem; z-index: 10;
        }
        .object-fit-cover { object-fit: cover; }
        .user-search-item { transition: all 0.2s; border-radius: 6px; }
        .user-search-item:hover { background: #f1faff; cursor: pointer; color: #009ef7; }
        .color-circle { width: 28px; height: 28px; border-radius: 50%; cursor: pointer; border: 3px solid #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.2s; }
        .color-circle:hover { transform: scale(1.15); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .color-circle.active { border-color: #333; transform: scale(1.25); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .bg-light-soft { background-color: rgba(248, 249, 250, 0.5); }
        
        .story-progress-segment { height: 3px; background: rgba(255,255,255,0.2); flex-grow: 1; border-radius: 2px; overflow: hidden; }
        .story-progress-inner { height: 100%; width: 0%; background: #fff; transition: width 0.1s linear; }
        .story-progress-inner.filled { width: 100%; transition: none; }
    </style>
@endpush

@push('script')
    <script>
        // Modal toggling and UI
        $('input[name="type"]').on('change', function() {
            let val = $(this).val();
            $('.btn-outline').removeClass('active');
            $(this).closest('label').addClass('active');

            if (val == 'text') {
                $('#text_wrapper').removeClass('d-none');
                $('#media_wrapper').addClass('d-none');
            } else {
                $('#text_wrapper').addClass('d-none');
                $('#media_wrapper').removeClass('d-none');
                $('input[name="media_file"]').attr('accept', val == 'image' ? 'image/*' : 'video/*');
            }
        });

        $('.color-circle').on('click', function() {
            let color = $(this).data('color');
            $('.color-circle').removeClass('active');
            $(this).addClass('active');
            $('#bg_color_input').val(color);
        });

        // User Search
        $('#user_search_input').on('keyup', function() {
            let search = $(this).val();
            if (search.length < 2) {
                $('#user_search_results').addClass('d-none').html('');
                return;
            }

            $.get("{{ route('admin.status.search-users') }}", { search: search }, function(res) {
                let html = '';
                if (res.length > 0) {
                    res.forEach(user => {
                        html += `
                            <div class="d-flex align-items-center p-3 user-search-item" data-id="${user.id}" data-name="${user.name}">
                                <div class="symbol symbol-30px symbol-circle me-3">
                                    <div class="symbol-label bg-primary text-white fs-9">${user.name.charAt(0)}</div>
                                </div>
                                <div>
                                    <div class="fs-7 fw-bold text-gray-900">${user.name}</div>
                                    <div class="fs-8 text-muted">${user.email}</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#user_search_results').removeClass('d-none').html(html);
                } else {
                    $('#user_search_results').addClass('d-none');
                }
            });
        });

        $(document).on('click', '.user-search-item', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            $('#selected_user_id').val(id);
            $('#preview_user_name').text(name);
            $('#preview_user_letter').text(name.charAt(0));
            $('#selected_user_preview').removeClass('d-none').addClass('d-flex');
            $('#user_search_results').addClass('d-none');
            $('#user_search_input').addClass('d-none');
        });

        $('#clear_selected_user').on('click', function() {
            $('#selected_user_id').val('');
            $('#selected_user_preview').addClass('d-none').removeClass('d-flex');
            $('#user_search_input').removeClass('d-none').val('');
        });

        // Tag User Search
        $('#tag_user_search_input').on('keyup', function() {
            let search = $(this).val();
            if (search.length < 2) {
                $('#tag_user_search_results').addClass('d-none').html('');
                return;
            }

            $.get("{{ route('admin.status.search-users') }}", { search: search }, function(res) {
                let html = '';
                if (res.length > 0) {
                    res.forEach(user => {
                        html += `
                            <div class="d-flex align-items-center p-3 user-search-item tag-search-item" data-id="${user.id}" data-name="${user.name}">
                                <div class="symbol symbol-30px symbol-circle me-3">
                                    <div class="symbol-label bg-info text-white fs-9">${user.name.charAt(0)}</div>
                                </div>
                                <div>
                                    <div class="fs-7 fw-bold text-gray-900">${user.name}</div>
                                    <div class="fs-8 text-muted">${user.email}</div>
                                </div>
                            </div>
                        `;
                    });
                    $('#tag_user_search_results').removeClass('d-none').html(html);
                } else {
                    $('#tag_user_search_results').addClass('d-none');
                }
            });
        });

        $(document).on('click', '.tag-search-item', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');
            $('#selected_tag_user_id').val(id);
            $('#preview_tag_user_name').text(name);
            $('#preview_tag_user_letter').text(name.charAt(0));
            $('#selected_tag_user_preview').removeClass('d-none').addClass('d-flex');
            $('#tag_user_search_results').addClass('d-none');
            $('#tag_user_search_input').addClass('d-none');
        });

        $('#clear_selected_tag_user').on('click', function() {
            $('#selected_tag_user_id').val('');
            $('#selected_tag_user_preview').addClass('d-none').removeClass('d-flex');
            $('#tag_user_search_input').removeClass('d-none').val('');
        });

        // AJAX Form Submit
        $('#createStatusForm').on('submit', function(e) {
            e.preventDefault();
            if(!$('#selected_user_id').val()) {
                toastr.error('Please select a student first');
                return;
            }

            let formData = new FormData(this);
            let btn = $('#submitBtn');
            btn.attr('data-kt-indicator', 'on').attr('disabled', true);

            $.ajax({
                url: "{{ route('admin.status.store') }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.status) {
                        toastr.success(res.message);
                        location.reload();
                    } else {
                        toastr.error(res.message);
                        btn.removeAttr('data-kt-indicator').attr('disabled', false);
                    }
                },
                error: function(err) {
                    toastr.error('Validation failed. Please check your inputs.');
                    btn.removeAttr('data-kt-indicator').attr('disabled', false);
                }
            });
        });

        // Engagement Details
        $(document).on('click', '.viewEngagement', function() {
            let id = $(this).data('id');
            $('#reactionsList, #commentsList').html('<div class="p-10 text-center"><span class="spinner-border text-primary spinner-border-sm"></span></div>');
            $('#statusDetailsModal').modal('show');

            $.get("{{ url('admin/status/get-details') }}/" + id, function(res) {
                if (res.status) {
                    let reactHtml = '';
                    if (res.reactions.length > 0) {
                        res.reactions.forEach(r => {
                            reactHtml += `
                                <div class="d-flex align-items-center mb-5 p-3 bg-light rounded-3">
                                    <div class="symbol symbol-40px symbol-circle me-3">
                                        <img src="${r.user.image_path || '/assets/media/avatars/blank.png'}" alt="">
                                    </div>
                                    <div class="d-flex flex-column flex-grow-1">
                                        <span class="text-gray-900 fw-bold fs-7">${r.user.name}</span>
                                        <span class="text-muted fs-9">${new Date(r.created_at).toLocaleDateString()}</span>
                                    </div>
                                    <div class="fs-2">${r.emoji}</div>
                                </div>
                            `;
                        });
                    } else { reactHtml = '<div class="text-center text-muted p-10">No interactions yet</div>'; }
                    $('#reactionsList').html(reactHtml);

                    let commentHtml = '';
                    if (res.comments.length > 0) {
                        res.comments.forEach(c => {
                            commentHtml += `
                                <div class="mb-5 p-4 bg-light border-start border-primary border-4 rounded shadow-sm">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="symbol symbol-30px symbol-circle me-3">
                                            <img src="${c.user.image_path}" alt="">
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-900 fw-bold fs-8">${c.user.name}</span>
                                            <span class="text-gray-400 fs-10">${new Date(c.created_at).toLocaleTimeString()}</span>
                                        </div>
                                    </div>
                                    <p class="text-gray-800 fs-8 mb-0">${c.comment}</p>
                                </div>
                            `;
                        });
                    } else { commentHtml = '<div class="text-center text-muted p-10">No comments yet</div>'; }
                    $('#commentsList').html(commentHtml);
                }
            });
        });

        $(document).on('click', '.deleteStatus', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Confirm Deletion?',
                text: "Removing this status will lose all associated reactions and comments.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it',
                customClass: { confirmButton: 'btn btn-danger', cancelButton: 'btn btn-light' }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.get("{{ url('admin/status/delete') }}/" + id, function(res) {
                        if (res.status) {
                            toastr.success(res.message);
                            location.reload();
                        }
                    });
                }
            });
        });

        // Post as Me logic
        $('#post_as_me_toggle').on('change', function() {
            if ($(this).is(':checked')) {
                $('#user_search_wrapper').addClass('d-none');
                $('#selected_user_id').val("{{ auth()->id() }}");
                $('#preview_user_name').text("{{ auth()->user()->name }} (You)");
                $('#preview_user_letter').text("{{ substr(auth()->user()->name, 0, 1) }}");
                $('#selected_user_preview').removeClass('d-none').addClass('d-flex');
            } else {
                $('#user_search_wrapper').removeClass('d-none');
                $('#selected_user_id').val('');
                $('#selected_user_preview').addClass('d-none').removeClass('d-flex');
                $('#user_search_input').removeClass('d-none').val('');
            }
        });

        // Story Viewer Logic
        let activeStories = @json($statuses->items());
        let currentStoryIndex = 0;
        let storyInterval;

        $('#btnPlayAll').on('click', function() {
            if (activeStories.length === 0) {
                toastr.info('No active stories to play');
                return;
            }
            playStory(0);
        });

        // Click on preview or eye button to play specific story
        $(document).on('click', '.status-preview-container, .viewStory', function(e) {
            e.stopPropagation();
            let id = $(this).data('id');
            let index = activeStories.findIndex(s => s.id == id);
            if (index !== -1) playStory(index);
        });

        function playStory(index) {
            currentStoryIndex = index;
            let story = activeStories[index];
            $('#storyViewerModal').modal('show');
            
            // Build progress segments
            let segmentsHtml = '';
            for (let i = 0; i < activeStories.length; i++) {
                segmentsHtml += `<div class="story-progress-segment"><div class="story-progress-inner ${i < index ? 'filled' : ''}" id="seg_${i}"></div></div>`;
            }
            $('#story_segments').html(segmentsHtml);

            updateViewerContent(story);
            startProgress(index);
        }

        function updateViewerContent(story) {
            $('#viewer_avatar').attr('src', story.user.image_path);
            $('#viewer_name').text(story.user.name);
            $('#viewer_time').text(moment(story.created_at).fromNow());
            $('#viewer_reactions').text(story.reactions_count || 0);
            $('#viewer_comments').text(story.comments_count || 0);

            let html = '';
            if (story.type == 'text') {
                html = `<div class="w-100 d-flex align-items-center justify-content-center p-12 rounded-4 shadow-lg text-center" style="background:${story.background_color || '#6f42c1'}; min-height: 450px; border: 1px solid rgba(255,255,255,0.1);">
                            <h2 class="text-white fw-bolder fs-1x mb-0 lh-base">${story.content}</h2>
                        </div>`;
            } else if (story.type == 'image') {
                html = `<img src="/storage/${story.content}" class="w-100 rounded-4 shadow-2xl" style="max-height: 80vh; object-fit: contain;">`;
            } else if (story.type == 'video') {
                html = `<video id="activeVideo" class="w-100 rounded-4 shadow-2xl" style="max-height: 80vh;" autoplay>
                            <source src="/storage/${story.content}" type="video/mp4">
                        </video>`;
            }
            $('#viewer_content').html(html);
        }

        function startProgress(index) {
            clearInterval(storyInterval);
            let duration = 5000; // 5 seconds default
            let start = Date.now();
            let bar = $(`#seg_${index}`);
            
            storyInterval = setInterval(() => {
                let elapsed = Date.now() - start;
                let percent = (elapsed / duration) * 100;
                bar.css('width', Math.min(percent, 100) + '%');

                if (percent >= 100) {
                    clearInterval(storyInterval);
                    if (currentStoryIndex < activeStories.length - 1) {
                        playStory(currentStoryIndex + 1);
                    } else {
                        $('#storyViewerModal').modal('hide');
                    }
                }
            }, 50);
        }

        $('#nextStory').on('click', () => {
            if (currentStoryIndex < activeStories.length - 1) playStory(currentStoryIndex + 1);
            else $('#storyViewerModal').modal('hide');
        });

        $('#prevStory').on('click', () => {
            if (currentStoryIndex > 0) playStory(currentStoryIndex - 1);
        });

        $('#storyViewerModal').on('hidden.bs.modal', function () {
            clearInterval(storyInterval);
            $('#viewer_content').empty();
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#user_search_input, #user_search_results, #tag_user_search_input, #tag_user_search_results').length) {
                $('#user_search_results, #tag_user_search_results').addClass('d-none');
            }
        });
    </script>
@endpush
