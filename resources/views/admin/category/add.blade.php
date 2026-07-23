@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Category {{ !empty($details) ? 'Edit' : 'Add' }}</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.category.list') }}"
                                    class="text-muted text-hover-primary">Category</a>
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
                                <form id="categoryForm" action="{{ route('admin.category.add') }}" method="POST"
                                    class="formSubmit fileUpload" enctype="multipart/form-data">
                                    <input type="hidden" name="id" name="id" value="{{ $details->id ?? null }}">
                                    <div class="row pt-2">
                                        <div class="col-md-6">
                                            <label>
                                                <span class="label_title">Category Image</span>
                                                <!-- <span class="asterisk_sign">*</span> -->
                                            </label>
                                            <div class="fv-row">
                                                @if (!empty($details->file))
                                                    <style>
                                                        .image-input-placeholder {
                                                            background-image: url('{{ $details->image_path }}');
                                                        }

                                                        [data-bs-theme="dark"] .image-input-placeholder {
                                                            background-image: url('{{ $details->image_path }}');
                                                        }
                                                    </style>
                                                @else
                                                    <style>
                                                        .image-input-placeholder {
                                                            background-image: url("{{ asset('/assets/media/svg/files/blank-image.png') }}");
                                                        }

                                                        [data-bs-theme="dark"] .image-input-placeholder {
                                                            background-image: url("{{ asset('/assets/media/svg/files/blank-image-dark.png') }}");
                                                        }
                                                    </style>
                                                @endif
                                                <div class="image-input image-input-empty image-input-outline image-input-placeholder"
                                                    data-kt-image-input="true">
                                                    <div class="image-input-wrapper w-125px h-125px"></div>
                                                    <label
                                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                        data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                        title="Add image">
                                                        <div class="img_edit_btn_icon">
                                                            <i class="fa-solid fa-pen"></i>
                                                        </div>
                                                        <input type="file" name="file" accept=".png, .jpg, .jpeg"
                                                            id="file" />
                                                        <input type="hidden" name="avatar_remove" />
                                                    </label>
                                                    <span
                                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                        data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                        title="Cancel image">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </span>
                                                    <span
                                                        class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                        data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                        title="Remove logo">
                                                        <i class="bi bi-x fs-2"></i>
                                                    </span>
                                                </div>
                                                <div class="form-text" style="font-size: 10px; color: #000 !important;">
                                                    Allowed file
                                                    types: png, jpg, jpeg.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="parent_id" class="label-style">Category</label>
                                                        <select class="form-control" name="parent_id" id="parent_id">
                                                            <option value="">--- Select Category ---</option>
                                                            {!! $categoriesOption !!}
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-12 pt-4">
                                                    <div class="form-group">
                                                        <label for="title" class="label-style">Title</label>
                                                        <span class="asterisk_sign">*</span>
                                                        <input type="text" class="form-control fromAlias"
                                                            placeholder="Enter Name" name="title" id="title"
                                                            value="{{ $details->title ?? null }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-2">
                                        {{-- <div class="col-md-3 pt-4">
                                            <div class="form-group">
                                                <label for="alias" class="label-style">Color</label>
                                                <span class="asterisk_sign">*</span>
                                                <input type="text" value="#00AABB" name="color_picker"
                                                    class="form-control" />
                                            </div>
                                        </div>
                                        <div class="col-md-2 pt-4">
                                            <div class="form-group">
                                                <label class="label-style">&nbsp;</label>
                                                <i class="fa-solid fa-rotate" id="regenerateColorBtn"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-1 pt-4">
                                            <div class="form-group">
                                                <label for="color_picker" class="label-style">Color</label>
                                                <span class="asterisk_sign">*</span>
                                                <input type="color" id="color_picker" name="color_picker"
                                                    class="form-control" value="{{ $details->color ?? '#00AABB' }}">
                                            </div>
                                        </div> --}}
                                        <div class="col-md-4 pt-4">
                                            <label for="alias" class="label-style">Color Code</label>
                                            <span class="asterisk_sign">*</span>
                                            <div class="input-group">
                                                <input type="text" value="{{ $details->color_code ?? '#00AABB' }}"
                                                    name="color_picker" class="form-control" id="color_code_input"
                                                    style="background-color: {{ $details->color_code ?? '#00AABB' }};" readonly/>

                                                <div class="input-group-append">
                                                    <span class="input-group-text goTo"
                                                        id="regenerateColorBtn">Re-Generate</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2 pt-4">
                                            <div class="form-group">
                                                <label for="color_picker" class="label-style">Change Color</label>
                                                <span class="asterisk_sign">*</span>
                                                <input style="height: 40px;" type="color" id="color_picker" name="color_picker"
                                                    class="form-control" value="{{ $details->color_code ?? '#00AABB' }}">
                                            </div>
                                        </div>

                                        <div class="col-md-6 pt-4">
                                            <div class="form-group">
                                                <label for="alias" class="label-style">Alias</label>
                                                <span class="asterisk_sign">*</span>
                                                <input type="text" class="form-control toAlias"
                                                    placeholder="Enter Alias" name="alias" id="alias"
                                                    value="{{ $details->alias ?? null }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row pt-2">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="description" class="label-style">Short Description</label>
                                                <span class="asterisk_sign">*</span>
                                                <textarea class="form-control" name="description" id="description" cols="30" rows="4">{{ $details->description ?? null }}</textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="button add-btn-div-save-style">
                                        <button type="submit" id="submitBtn" class="btn btn-dark">
                                            <span
                                                class="indicator-label">{{ !empty($details) ? 'Update' : 'Save' }}</span>
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
            document.addEventListener('DOMContentLoaded', function() {
                const colorPicker = document.getElementById('color_picker');
                if (!colorPicker.value) {
                    colorPicker.value = '#' + Math.floor(Math.random() * 16777215).toString(16);
                }
            });
            document.addEventListener('DOMContentLoaded', function() {
                const regenerateColorBtn = document.getElementById('regenerateColorBtn');
                const colorPicker = document.getElementById('color_picker');

                regenerateColorBtn.addEventListener('click', function() {
                    const randomColor = '#' + Math.floor(Math.random() * 16777215).toString(16);
                    colorPicker.value = randomColor;
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const colorCodeInput = document.getElementById('color_code_input');
                const colorPicker = document.getElementById('color_picker');

                colorCodeInput.addEventListener('input', function() {
                    colorPicker.value = colorCodeInput.value;
                    colorCodeInput.style.backgroundColor = colorCodeInput.value;
                });

                colorPicker.addEventListener('input', function() {
                    colorCodeInput.value = colorPicker.value;
                    colorCodeInput.style.backgroundColor = colorPicker.value;
                });

                const regenerateColorBtn = document.getElementById('regenerateColorBtn');
                regenerateColorBtn.addEventListener('click', function() {
                    const randomColor = '#' + Math.floor(Math.random() * 16777215).toString(16);
                    colorPicker.value = randomColor;
                    colorCodeInput.value = randomColor;
                    colorCodeInput.style.backgroundColor = randomColor;
                });
            });
        </script>
    @endpush
@endsection
