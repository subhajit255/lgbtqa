@extends('layout.app')
@section('content')
    <link href="{{ asset('assets/css/engagements.css') }}" rel="stylesheet" type="text/css" />
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Post Engagements
                        </h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                            <li class="breadcrumb-item text-muted"><a href="{{ route('admin.post.list') }}">Posts</a></li>
                            <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                            <li class="breadcrumb-item text-muted">Engagements</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                            data-bs-target="#kt_modal_add_engagement">
                            <i class="bi bi-plus fs-2"></i> Add Engagement
                        </button>
                    </div>
                </div>
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">

                    <div class="card mb-5 mb-xl-8 border-0 shadow-sm">
                        <div class="card-body pt-5">
                            <div class="d-flex align-items-center mb-5">
                                <div class="d-flex align-items-center flex-grow-1">
                                    <div class="symbol symbol-45px me-5">
                                        <img src="{{ $post->user->image_path ?? asset('assets/media/avatars/blank.png') }}"
                                            alt="" />
                                    </div>
                                    <div class="d-flex flex-column">
                                        <a href="#"
                                            class="text-gray-900 text-hover-primary fs-6 fw-bold">{{ $post->user->name ?? 'Admin' }}</a>
                                        <span class="text-gray-400 fw-bold">{{ $post->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-5">
                                <span class="fw-bold text-gray-800 d-block fs-4 mb-2">{{ $post->title }}</span>
                                <p class="text-gray-800 fw-normal mb-5">
                                    {{ strip_tags($post->description) }}
                                </p>

                                @if ($post->media && $post->media->count() > 0)
                                    <div class="row g-3 mb-5">
                                        @foreach ($post->media as $media)
                                            <div class="col-sm-4 col-md-3 col-xl-2">
                                                <a class="d-block overlay rounded" data-fslightbox="lightbox-base"
                                                    href="{{ $media->file_path }}">
                                                    <div class="overlay-wrapper bgi-no-repeat bgi-position-center bgi-size-cover card-rounded min-h-125px"
                                                        style="background-image:url('{{ $media->file_path }}')"></div>
                                                    <div class="overlay-layer card-rounded bg-dark bg-opacity-25 shadow">
                                                        <i class="bi bi-eye-fill text-white fs-3x"></i>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="d-flex align-items-center pt-4 border-top border-gray-200 mt-5">
                                <span class="btn btn-sm btn-light-danger btn-color-danger me-5 fw-bold">
                                    <i class="bi bi-heart-fill fs-4 me-1"></i> {{ $post->loves->count() }}
                                </span>
                                <span class="btn btn-sm btn-light-primary btn-color-primary me-5 fw-bold">
                                    <i class="bi bi-chat-fill fs-4 me-1"></i> {{ $post->comments->count() }}
                                </span>
                                <span class="btn btn-sm btn-light-warning btn-color-warning me-5 fw-bold">
                                    <i class="bi bi-star-fill fs-4 me-1"></i> {{ $post->stars->count() }}
                                </span>
                                <span class="btn btn-sm btn-light-info btn-color-info fw-bold">
                                    <i class="bi bi-emoji-smile-fill fs-4 me-1"></i> {{ $post->emojis->count() }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-pills nav-pills-custom nav-justified mb-5 fs-6 border-0 w-100 gap-4">
                        <li class="nav-item">
                            <a class="nav-link nav-link-comments d-flex flex-column flex-center h-100px active"
                                data-bs-toggle="tab" href="#kt_tab_pane_comments">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="bi bi-chat-quote fs-2 me-2"></i>
                                    <span class="fw-bold fs-2 nav-count">{{ $post->comments->count() }}</span>
                                </span>
                                <span class="fw-bold fs-8 nav-label">Comments</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-loves d-flex flex-column flex-center h-100px" data-bs-toggle="tab"
                                href="#kt_tab_pane_loves">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="bi bi-heart-fill fs-2 me-2"></i>
                                    <span class="fw-bold fs-2 nav-count">{{ $post->loves->count() }}</span>
                                </span>
                                <span class="fw-bold fs-8 nav-label">Loves</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-stars d-flex flex-column flex-center h-100px" data-bs-toggle="tab"
                                href="#kt_tab_pane_stars">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="bi bi-star-fill fs-2 me-2"></i>
                                    <span class="fw-bold fs-2 nav-count">{{ $post->stars->count() }}</span>
                                </span>
                                <span class="fw-bold fs-8 nav-label">Stars</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link nav-link-emojis d-flex flex-column flex-center h-100px" data-bs-toggle="tab"
                                href="#kt_tab_pane_emojis">
                                <span class="d-flex align-items-center mb-2">
                                    <i class="bi bi-emoji-smile fs-2 me-2"></i>
                                    <span class="fw-bold fs-2 nav-count">{{ $post->emojis->count() }}</span>
                                </span>
                                <span class="fw-bold fs-8 nav-label">Emojis</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content" id="myTabContent">
                        <!-- Comments Tab -->
                        <div class="tab-pane fade show active" id="kt_tab_pane_comments" role="tabpanel">
                            <div class="card border-0 shadow-sm">
                                <div class="card-body ps-10 pe-10 pb-10 pt-5">
                                    @forelse($post->comments as $comment)
                                        <div class="d-flex pt-6">
                                            <div class="symbol symbol-45px symbol-circle me-5">
                                                <img alt="Pic"
                                                    src="{{ $comment->user->image_path ?? asset('assets/media/avatars/blank.png') }}" />
                                            </div>
                                            <div class="d-flex flex-column flex-row-fluid">
                                                <div class="d-flex align-items-center flex-wrap mb-1">
                                                    <a href="#"
                                                        class="text-gray-800 text-hover-primary fw-bold me-2">{{ $comment->user->name ?? 'Unknown' }}</a>
                                                    <span
                                                        class="text-gray-400 fw-semibold fs-7">{{ $comment->created_at->diffForHumans() }}</span>
                                                    <button
                                                        class="btn btn-sm btn-icon btn-color-danger btn-active-light-danger ms-auto del-engagement"
                                                        data-url="{{ route('admin.post.engagements.delete-comment', $comment->id) }}">
                                                        <i class="bi bi-trash fs-5"></i>
                                                    </button>
                                                </div>
                                                <span
                                                    class="text-gray-800 fs-6 fw-normal pt-1">{{ $comment->comment }}</span>

                                                @if ($comment->replies->count() > 0)
                                                    <div class="mt-5 p-5 bg-light rounded shadow-sm">
                                                        <span class="fw-bold text-gray-800 fs-6 mb-3 d-block"><i
                                                                class="bi bi-reply-fill me-2"></i> Replies
                                                            ({{ $comment->replies->count() }})
                                                        </span>
                                                        @foreach ($comment->replies as $reply)
                                                            <div class="d-flex mb-4">
                                                                <div class="symbol symbol-35px symbol-circle me-4">
                                                                    <img alt="Pic"
                                                                        src="{{ $reply->user->image_path ?? asset('assets/media/avatars/blank.png') }}" />
                                                                </div>
                                                                <div class="d-flex flex-column flex-row-fluid">
                                                                    <div class="d-flex align-items-center flex-wrap mb-1">
                                                                        <a href="#"
                                                                            class="text-gray-800 text-hover-primary fw-bold me-2 fs-7">{{ $reply->user->name ?? 'Unknown' }}</a>
                                                                        <span
                                                                            class="text-gray-400 fw-semibold fs-8">{{ $reply->created_at->diffForHumans() }}</span>
                                                                        <button
                                                                            class="btn btn-sm btn-icon btn-color-danger btn-active-light-danger ms-auto del-engagement w-20px h-20px"
                                                                            data-url="{{ route('admin.post.engagements.delete-comment', $reply->id) }}">
                                                                            <i class="bi bi-trash fs-7"></i>
                                                                        </button>
                                                                    </div>
                                                                    <span
                                                                        class="text-gray-800 fs-7 fw-normal">{{ $reply->comment }}</span>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted fw-bold py-10">No comments on this post yet.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Loves Tab -->
                        <div class="tab-pane fade" id="kt_tab_pane_loves" role="tabpanel">
                            <div class="row g-6 g-xl-9">
                                @forelse($post->loves as $love)
                                    <div class="col-md-4 col-xxl-3">
                                        <div class="card border-0 shadow-sm hover-elevate-up">
                                            <div class="card-body d-flex flex-center flex-column py-9 px-5">
                                                <div class="symbol symbol-65px symbol-circle mb-5">
                                                    <img src="{{ $love->user->image_path ?? asset('assets/media/avatars/blank.png') }}"
                                                        alt="image" />
                                                    <div
                                                        class="bg-danger position-absolute border border-4 border-body h-20px w-20px rounded-circle translate-middle start-100 top-100 ms-n3 mt-n3 d-flex flex-center">
                                                        <i class="bi bi-heart-fill text-white fs-9"></i>
                                                    </div>
                                                </div>
                                                <a href="#"
                                                    class="fs-4 text-gray-800 text-hover-primary fw-bold mb-0">{{ $love->user->name ?? 'Unknown' }}</a>
                                                <div class="fw-semibold text-gray-400 mb-6">
                                                    {{ $love->created_at->diffForHumans() }}</div>
                                                <button class="btn btn-sm btn-light-danger del-engagement w-100"
                                                    data-url="{{ route('admin.post.engagements.delete-love', $love->id) }}">
                                                    <i class="bi bi-trash fs-4 me-2"></i> Remove Love
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body text-center text-muted fw-bold py-10">No loves found.
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Stars Tab -->
                        <div class="tab-pane fade" id="kt_tab_pane_stars" role="tabpanel">
                            <div class="row g-6 g-xl-9">
                                @forelse($post->stars as $star)
                                    <div class="col-md-4 col-xxl-3">
                                        <div class="card border-0 shadow-sm hover-elevate-up">
                                            <div class="card-body d-flex flex-center flex-column py-9 px-5">
                                                <div class="symbol symbol-65px symbol-circle mb-5">
                                                    <img src="{{ $star->user->image_path ?? asset('assets/media/avatars/blank.png') }}"
                                                        alt="image" />
                                                </div>
                                                <a href="#"
                                                    class="fs-4 text-gray-800 text-hover-primary fw-bold mb-2">{{ $star->user->name ?? 'Unknown' }}</a>
                                                <div class="fs-4 mb-4">
                                                    @for ($i = 0; $i < $star->star_count; $i++)
                                                        <i class="bi bi-star-fill text-warning fs-3"></i>
                                                    @endfor
                                                </div>
                                                <div class="fw-semibold text-gray-400 mb-6">
                                                    {{ $star->created_at->diffForHumans() }}</div>
                                                <button class="btn btn-sm btn-light-danger del-engagement w-100"
                                                    data-url="{{ route('admin.post.engagements.delete-star', $star->id) }}">
                                                    <i class="bi bi-trash fs-4 me-2"></i> Remove Star
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <div class="card border-0 shadow-sm">
                                            <div class="card-body text-center text-muted fw-bold py-10">No stars found.
                                            </div>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <!-- Emojis Tab -->
                        <div class="tab-pane fade" id="kt_tab_pane_emojis" role="tabpanel">
                            <div class="row g-6 g-xl-9">
                                @forelse($post->emojis as $emoji)
                                    <div class="col-md-4 col-xxl-3">
                                        <div class="card border-0 shadow-sm hover-elevate-up">
                                            <div class="card-body d-flex flex-center flex-column py-9 px-5">
                                                <div class="symbol symbol-65px symbol-circle mb-5">
                                                    <img src="{{ $emoji->user->image_path ?? asset('assets/media/avatars/blank.png') }}"
                                                        alt="image" />
                                                    <div
                                                        class="bg-light position-absolute border border-4 border-body h-30px w-30px rounded-circle translate-middle start-100 top-100 ms-n3 mt-n3 d-flex flex-center fs-3">
                                                        @switch($emoji->emoji)
                                                            @case('LIKE')
                                                                👍
                                                            @break

                                                            @case('LOVE')
                                                                ❤️
                                                            @break

                                                            @case('HAHA')
                                                                😂
                                                            @break

                                                            @case('WOW')
                                                                😮
                                                            @break

                                                            @case('SAD')
                                                                😢
                                                            @break

                                                            @case('ANGRY')
                                                                😡
                                                            @break

                                                            @default
                                                                {{ $emoji->emoji }}
                                                        @endswitch
                                                    </div>
                                                </div>
                                                <a href="#"
                                                    class="fs-4 text-gray-800 text-hover-primary fw-bold mb-0">{{ $emoji->user->name ?? 'Unknown' }}</a>
                                                <div class="fw-semibold text-gray-400 mb-6">
                                                    {{ $emoji->created_at->diffForHumans() }}</div>
                                                <button class="btn btn-sm btn-light-danger del-engagement w-100"
                                                    data-url="{{ route('admin.post.engagements.delete-emoji', $emoji->id) }}">
                                                    <i class="bi bi-trash fs-4 me-2"></i> Remove Emoji
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                        <div class="col-12">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-body text-center text-muted fw-bold py-10">No emojis found.
                                                </div>
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Engagement Modal -->
            <div class="modal fade" tabindex="-1" id="kt_modal_add_engagement">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content shadow-none">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Engagement as User</h5>
                            <!--begin::Close-->
                            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                                aria-label="Close">
                                <i class="bi bi-x fs-2x"></i>
                            </div>
                            <!--end::Close-->
                        </div>

                        <form id="addEngagementForm">
                            @csrf
                            <input type="hidden" name="post_id" value="{{ $post->id }}">
                            <div class="modal-body">

                                <div class="fv-row mb-8">
                                    <label class="required fs-6 fw-semibold mb-2">Select User</label>
                                    <select class="form-select form-select-solid" data-control="select2"
                                        data-placeholder="Search for a user..." name="user_id" id="userSelect">
                                        <option></option>
                                    </select>
                                </div>

                                <div class="fv-row mb-8">
                                    <label class="required fs-6 fw-semibold mb-2">Engagement Type</label>
                                    <select class="form-select form-select-solid" data-control="select2"
                                        data-hide-search="true" data-placeholder="Select type" name="engagement_type"
                                        id="engagementType">
                                        <option></option>
                                        <option value="love">Give Love ❤️</option>
                                        <option value="comment">Add Comment 💬</option>
                                        <option value="star">Rate with Star ⭐️</option>
                                        <option value="emoji">React with Emoji 😀</option>
                                    </select>
                                </div>

                                <!-- Dynamic Fields -->
                                <div id="commentSection" class="fv-row mb-8 d-none">
                                    <label class="required fs-6 fw-semibold mb-2">Comment Text</label>
                                    <textarea class="form-control form-control-solid" rows="3" name="comment_text"
                                        placeholder="Write a comment..."></textarea>
                                </div>

                                <div id="starSection" class="fv-row mb-8 d-none">
                                    <label class="required fs-6 fw-semibold mb-2">Star Rating (1-5)</label>
                                    <select class="form-select form-select-solid" data-control="select2"
                                        data-hide-search="true" name="star_count">
                                        <option value="1">1 Star</option>
                                        <option value="2">2 Stars</option>
                                        <option value="3">3 Stars</option>
                                        <option value="4">4 Stars</option>
                                        <option value="5">5 Stars</option>
                                    </select>
                                </div>

                                <div id="emojiSection" class="fv-row mb-8 d-none">
                                    <label class="required fs-6 fw-semibold mb-2">Select Emoji</label>
                                    <select class="form-select form-select-solid" data-control="select2"
                                        data-hide-search="true" name="emoji_code">
                                        <option value="LIKE">👍 Like</option>
                                        <option value="LOVE">❤️ Love</option>
                                        <option value="HAHA">😂 Haha</option>
                                        <option value="WOW">😮 Wow</option>
                                        <option value="SAD">😢 Sad</option>
                                        <option value="ANGRY">😡 Angry</option>
                                    </select>
                                </div>

                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="btnSubmitEngagement">
                                    <span class="indicator-label">Submit Engagement</span>
                                    <span class="indicator-progress">Please wait... <span
                                            class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endsection

    @push('script')
        <script>
            $(document).ready(function() {
                // Tab retention logic
                var activeTab = localStorage.getItem('activeEngagementTab');
                if (activeTab) {
                    $('.nav-pills a[href="' + activeTab + '"]').tab('show');
                }

                $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                    localStorage.setItem('activeEngagementTab', $(e.target).attr('href'));
                });

                // Initialize Select2 User Search
                $('#userSelect').select2({
                    dropdownParent: $('#kt_modal_add_engagement'),
                    ajax: {
                        url: "{{ route('admin.post.engagements.search-users') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                term: params.term
                            };
                        },
                        processResults: function(data) {
                            return {
                                results: data.results
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                });

                // Handle Type selection to show/hide fields
                $('#engagementType').on('change', function() {
                    var val = $(this).val();
                    $('#commentSection, #starSection, #emojiSection').addClass('d-none');

                    if (val === 'comment') $('#commentSection').removeClass('d-none');
                    else if (val === 'star') $('#starSection').removeClass('d-none');
                    else if (val === 'emoji') $('#emojiSection').removeClass('d-none');
                });

                // Handle Form Submission
                $('#addEngagementForm').on('submit', function(e) {
                    e.preventDefault();

                    var btn = $('#btnSubmitEngagement');
                    btn.attr("data-kt-indicator", "on").prop('disabled', true);

                    $.ajax({
                        url: "{{ route('admin.post.engagements.add') }}",
                        type: 'POST',
                        data: $(this).serialize(),
                        success: function(response) {
                            if (response.status) {
                                Swal.fire('Success!', response.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                                btn.removeAttr("data-kt-indicator").prop('disabled', false);
                            }
                        },
                        error: function(xhr) {
                            var msg = 'An error occurred';
                            if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON
                                .message;
                            Swal.fire('Error!', msg, 'error');
                            btn.removeAttr("data-kt-indicator").prop('disabled', false);
                        }
                    });
                });

                // Delete Engagement logic
                $(document).on('click', '.del-engagement', function(e) {
                    e.preventDefault();
                    var url = $(this).data('url');

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
                                url: url,
                                type: 'DELETE',
                                data: {
                                    _token: '{{ csrf_token() }}'
                                },
                                success: function(response) {
                                    if (response.status) {
                                        Swal.fire('Deleted!', response.message, 'success')
                                            .then(() => {
                                                location.reload();
                                            });
                                    } else {
                                        Swal.fire('Error!', response.message, 'error');
                                    }
                                },
                                error: function() {
                                    Swal.fire('Error!', 'Something went wrong', 'error');
                                }
                            });
                        }
                    })
                });
            });
        </script>
    @endpush
