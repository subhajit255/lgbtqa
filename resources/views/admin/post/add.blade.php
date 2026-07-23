@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!--begin::Toolbar-->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            {{ !empty($details) ? 'Edit' : 'Add' }} Post</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.post.list') }}" class="text-muted text-hover-primary">Posts</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!--end::Toolbar-->
            <!--begin::Content-->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card shadow-sm border-0 glass-card">
                        <div class="card-body p-10">
                            <form id="postForm" class="formSubmit" action="{{ route('admin.post.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" value="{{ $details->id ?? null }}">

                                <div class="row g-9 mb-8">
                                    <!--begin::Col-->
                                    <div class="col-md-8">
                                        <div class="fv-row mb-8">
                                            <label class="required fw-bold fs-6 mb-2">Post Title</label>
                                            <input type="text" name="title"
                                                class="form-control form-control-solid border-gray-300"
                                                placeholder="Enter post title" value="{{ $details->title ?? '' }}"
                                                required />
                                        </div>

                                        <div class="fv-row mb-8">
                                            <label class="required fw-bold fs-6 mb-2">Editor</label>
                                            <div id="editor_container" class="rounded border">
                                                <textarea name="description" id="description_editor" class="form-control">{{ $details->description ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Col-->

                                    <!--begin::Col-->
                                    <div class="col-md-4">
                                        <div class="card bg-light-primary border-0 mb-8 shadow-none">
                                            <div class="card-body p-6">
                                                <div class="fv-row mb-7">
                                                    <label class="fw-bold fs-6 mb-2 text-primary">Tag User</label>
                                                    <select name="user_id" class="form-select form-select-solid"
                                                        data-control="select2" data-placeholder="Select a user">
                                                        <option value=""></option>
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->id }}"
                                                                {{ isset($details->user_id) && $details->user_id == $user->id ? 'selected' : '' }}>
                                                                {{ $user->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="fv-row">
                                                    <label class="fw-bold fs-6 mb-2 text-primary">Status</label>
                                                    <select name="status" class="form-select form-select-solid"
                                                        data-hide-search="true">
                                                        <option value="active"
                                                            {{ isset($details->status) && $details->status == 'active' ? 'selected' : '' }}>
                                                            Active</option>
                                                        <option value="inactive"
                                                            {{ isset($details->status) && $details->status == 'inactive' ? 'selected' : '' }}>
                                                            Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Col-->
                                </div>

                                <div class="fv-row mb-8">
                                    <label class="fw-bold fs-6 mb-4">Media Attachments <span
                                            class="text-muted fs-7 fw-normal">(Images, Videos)</span></label>

                                    <!--begin::Dropzone-->
                                    <div class="dropzone" id="kt_dropzone_post_media">
                                        <div class="dz-message needsclick">
                                            <i class="bi bi-cloud-arrow-up fs-3x text-primary mb-3"></i>
                                            <div class="ms-4">
                                                <h3 class="fs-5 fw-bold text-gray-900 mb-1">Click to upload or drag & drop.
                                                </h3>
                                                <span class="fs-7 fw-semibold text-gray-400">Upload multiple images or
                                                    videos.</span>
                                            </div>
                                        </div>
                                    </div>
                                    <!--end::Dropzone-->

                                    <div id="hidden_media_inputs"></div>

                                    @if (!empty($details) && $details->media->count() > 0)
                                        <div id="existing_media" class="row g-5 mt-5">
                                            @foreach ($details->media as $media)
                                                <div class="col-md-2" id="media_container_{{ $media->id }}">
                                                    <div class="card shadow-sm h-100 overflow-hidden border">
                                                        <div class="position-relative">
                                                            @if ($media->file_type == 'image')
                                                                <img src="{{ $media->file_path }}"
                                                                    class="w-100 h-100px object-fit-cover">
                                                            @else
                                                                <video src="{{ $media->file_path }}" controls preload="metadata" playsinline
                                                                    class="w-100 h-100px object-fit-cover bg-dark">
                                                                    <source src="{{ $media->file_path }}">
                                                                </video>
                                                            @endif
                                                            <button type="button"
                                                                class="btn btn-icon btn-circle btn-danger btn-sm position-absolute top-0 end-0 m-1 delete-existing-media"
                                                                data-id="{{ $media->id }}">
                                                                <i class="bi bi-x fs-6"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-end border-top pt-8">
                                    <button type="button" class="btn btn-light me-3"
                                        onclick="window.history.back()">Cancel</button>
                                    <button type="submit" id="submitBtn" class="btn btn-primary px-10">
                                        <span class="indicator-label">{{ !empty($details) ? 'Update' : 'Publish' }}
                                            Post</span>
                                        <span class="indicator-progress">Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!--end::Content-->
        </div>
    </div>
@endsection

@push('style')
    <style>
        .object-fit-cover {
            object-fit: cover;
        }

        #kt_dropzone_post_media {
            border-radius: 0.75rem;
            border: 2px dashed #009ef7;
            background: #f1faff;
            min-height: auto;
        }

        .ck-editor__editable {
            min-height: 300px;
        }
    </style>
@endpush

@push('script')
    <!-- CKEditor 5 SuperBuild for latest features and Source Editing -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
    <script>
        var globalEditor;
        CKEDITOR.ClassicEditor.create(document.querySelector('#description_editor'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'code', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'undo', 'redo', '|',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'alignment', '|',
                    'link', 'blockQuote', 'insertTable', 'mediaEmbed', 'htmlEmbed', '|',
                    'specialCharacters', 'horizontalLine', 'pageBreak', '|',
                    'sourceEditing'
                ],
                shouldNotGroupWhenFull: true
            },
            placeholder: 'Write something amazing...',
            htmlSupport: {
                allow: [{
                    name: /.*/,
                    attributes: true,
                    classes: true,
                    styles: true
                }]
            },
            // Comprehensive list of plugins to remove to avoid any Cloud Services/Collaboration errors
            removePlugins: [
                'ExportPdf', 'ExportWord', 'AIAssistant', 'CKBox', 'CKFinder', 'EasyImage',
                'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
                'RealTimeCollaborativeRevisionHistory', 'PresenceList', 'Comments',
                'TrackChanges', 'TrackChangesData', 'RevisionHistory', 'Pagination',
                'WProofreader', 'MathType', 'SlashCommand', 'Template', 'DocumentOutline',
                'FormatPainter', 'TableOfContents', 'PasteFromOfficeEnhanced', 'CaseChange'
            ]
        }).then(editor => {
            globalEditor = editor;
        }).catch(error => {
            console.error(error);
        });

        $(document).ready(function() {
            // Initialize Dropzone manually to ensure it works correctly
            if (typeof Dropzone !== 'undefined') {
                Dropzone.autoDiscover = false;

                var myDropzone = new Dropzone("#kt_dropzone_post_media", {
                    url: "{{ route('admin.post.upload.media') }}",
                    paramName: "file",
                    maxFiles: 10,
                    maxFilesize: 100, // MB
                    addRemoveLinks: true,
                    acceptedFiles: "image/*,video/*",
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    init: function() {
                        this.on("success", function(file, response) {
                            if (response.status) {
                                $(file.previewElement).attr('data-uuid', file.upload.uuid);
                                var input = $(
                                    '<input type="hidden" name="media_files[]" value="' +
                                    response.file_name + '" id="input_' + file.upload.uuid +
                                    '">');
                                $('#hidden_media_inputs').append(input);
                                toastr.success('File uploaded successfully');
                            }
                        });
                        this.on("removedfile", function(file) {
                            var uuid = $(file.previewElement).attr('data-uuid');
                            $('#input_' + uuid).remove();
                        });
                    }
                });
            }

            // Handle Form Submit
            $('#postForm').on('submit', function(e) {
                if (globalEditor) {
                    const data = globalEditor.getData();
                    $('#description_editor').val(data);
                }

                // Re-using common submit logic if exists, otherwise fallback to standard submit
                if ($(this).hasClass('formSubmit')) {
                    // assume formSubmit trait handles it
                } else {
                    const submitBtn = $('#submitBtn');
                    submitBtn.attr('data-kt-indicator', 'on').attr('disabled', true);
                }
            });

            // Delete Existing Media
            $(document).on('click', '.delete-existing-media', function() {
                var mediaId = $(this).data('id');
                var container = $('#media_container_' + mediaId);

                Swal.fire({
                    title: 'Delete Media?',
                    text: "This will permanently remove the file.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.post.delete.media') }}",
                            type: 'POST',
                            data: {
                                id: mediaId,
                                _token: "{{ csrf_token() }}"
                            },
                            success: function(response) {
                                if (response.status) {
                                    container.remove();
                                    toastr.success(response.message);
                                }
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
