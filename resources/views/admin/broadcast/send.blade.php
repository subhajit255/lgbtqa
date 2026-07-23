@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Broadcast</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.broadcast.list') }}"
                                    class="text-muted text-hover-primary">Broadcast</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                Send
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body pt-6">
                            <div class="container">
                                <form id="broadcastForm" action="{{ route('admin.broadcast.send') }}" method="POST"
                                    class="formSubmit fileUpload" enctype="multipart/form-data">
                                    <input type="hidden" name="id" name="id" value="{{ $details->id ?? null }}">
                                    @if ($details->file_path)
                                        <div class="row pt-2">
                                            <div class="col-md-12">
                                                <label for="title" class="label-style">Image : </label>
                                                <img class="img-fluid" width="100" height="100"
                                                    src="{{ $details->file_path ?? null }}" alt="">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="row pt-2">
                                        <div class="col-md-12">
                                            <label for="title" class="label-style">Title</label>
                                            <input type="text" class="form-control" placeholder="Enter Title"
                                                name="title" id="title" value="{{ $details->title ?? null }}"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="row pt-2">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="body" class="label-style">Body</label>
                                                <textarea readonly class="form-control" name="body" id="body" cols="30" rows="4">{{ $details->body ?? null }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-2">
                                        <div class="col-md-6">
                                            <label for="title" class="label-style">Send To</label>
                                            <select class="form-control" name="send_to" id="send_to">
                                                <option value="">Select User</option>
                                                <option value="1">All</option>
                                                <option value="2">Priority User</option>
                                                <option value="3">Normal User</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="button add-btn-div-save-style">
                                        <button type="submit" id="submitBtn" class="btn btn-dark">
                                            <span class="indicator-label">Send -></span>
                                            <span class="indicator-progress">Please wait...
                                                <span
                                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).ready(function() {
                $("#submitBtn").click(function() {
                    if($('#send_to').val().trim() != ''){
                        setTimeout(function() {
                            toastr.info('Please wait... It will take some time to send notification to all users.');
                        },5000);
                    }
                });
            });
        </script>
    @endpush
@endsection
