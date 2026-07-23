@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Hobbies, Vibes & Lifestyle {{ !empty($details) ? 'Edit' : 'Add' }}</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.hobby.list') }}" class="text-muted text-hover-primary">Hobbies, Vibes & Lifestyle</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card glass-card">
                        <div class="card-body pt-6">
                            <form id="hobbyForm" action="{{ route('admin.hobby.add') }}" method="POST" class="formSubmit">
                                <input type="hidden" name="id" value="{{ $details->id ?? null }}">
                                <div class="row">
                                    <div class="col-md-5">
                                        <div class="form-group mb-5">
                                            <label for="title" class="label-style fw-bold mb-2">Title</label>
                                            <span class="text-danger">*</span>
                                            <input type="text" class="form-control" placeholder="Enter Title"
                                                name="title" id="title" value="{{ $details->title ?? null }}"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-5">
                                            <label for="type" class="label-style fw-bold mb-2">Section Type</label>
                                            <span class="text-danger">*</span>
                                            <select name="type" id="type" class="form-control" required>
                                                <option value="1" {{ isset($details->type) && $details->type == 1 ? 'selected' : '' }}>Hobby / Interest</option>
                                                <option value="2" {{ isset($details->type) && $details->type == 2 ? 'selected' : '' }}>Lifestyle</option>
                                                <option value="3" {{ isset($details->type) && $details->type == 3 ? 'selected' : '' }}>Home & Future</option>
                                                <option value="4" {{ isset($details->type) && $details->type == 4 ? 'selected' : '' }}>Your Vibe</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group mb-5">
                                            <label for="is_active" class="label-style fw-bold mb-2">Status</label>
                                            <select name="is_active" id="is_active" class="form-control">
                                                <option value="1"
                                                    {{ isset($details->is_active) && $details->is_active == 1 ? 'selected' : '' }}>
                                                    Active</option>
                                                <option value="0"
                                                    {{ isset($details->is_active) && $details->is_active == 0 ? 'selected' : '' }}>
                                                    Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="separator separator-dashed my-8"></div>

                                <div class="mb-5">
                                    <div class="d-flex flex-stack mb-5">
                                        <h3 class="fw-bold m-0">Items</h3>
                                        <button type="button" class="btn btn-sm btn-primary add-item-btn">
                                            <i class="fa fa-plus"></i> Add Item
                                        </button>
                                    </div>

                                    <div id="hobby-items-container">
                                        @if (isset($details) && $details->items->count() > 0)
                                            @foreach ($details->items as $item)
                                                <div class="hobby-item-row row mb-2 align-items-end">
                                                    <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                                    <div class="col-md-7">
                                                        <label class="form-label fs-7">Item Name</label>
                                                        <input type="text" name="item_name[]" class="form-control"
                                                            value="{{ $item->name }}" placeholder="Item Name">
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label fs-7">Status</label>
                                                        <select name="item_status[]" class="form-control">
                                                            <option value="1"
                                                                {{ $item->is_active == 1 ? 'selected' : '' }}>Active
                                                            </option>
                                                            <option value="0"
                                                                {{ $item->is_active == 0 ? 'selected' : '' }}>Inactive
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <button type="button"
                                                            class="btn btn-icon btn-light-danger remove-item-btn w-100">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="hobby-item-row row mb-2 align-items-end">
                                                <input type="hidden" name="item_id[]" value="">
                                                <div class="col-md-7">
                                                    <label class="form-label fs-7">Item Name</label>
                                                    <input type="text" name="item_name[]" class="form-control"
                                                        placeholder="Item Name">
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fs-7">Status</label>
                                                    <select name="item_status[]" class="form-control">
                                                        <option value="1">Active</option>
                                                        <option value="0">Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button"
                                                        class="btn btn-icon btn-light-danger remove-item-btn w-100">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-center mt-10">
                                    <button type="submit" id="submitBtn" class="btn btn-dark w-25">
                                        <span class="indicator-label">{{ !empty($details) ? 'Update' : 'Save' }}</span>
                                        <span class="indicator-progress">Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script>
            $(document).on('click', '.add-item-btn', function() {
                var html = `
                    <div class="hobby-item-row row mb-2 align-items-end">
                        <input type="hidden" name="item_id[]" value="">
                        <div class="col-md-7">
                            <label class="form-label fs-7">Item Name</label>
                            <input type="text" name="item_name[]" class="form-control" placeholder="Item Name">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fs-7">Status</label>
                            <select name="item_status[]" class="form-control">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-icon btn-light-danger remove-item-btn w-100">
                                <i class="fa fa-trash"></i>
                            </button>
                        </div>
                    </div>`;
                $('#hobby-items-container').append(html);
            });

            $(document).on('click', '.remove-item-btn', function() {
                if ($('.hobby-item-row').length > 1) {
                    $(this).closest('.hobby-item-row').remove();
                } else {
                    $(this).closest('.hobby-item-row').find('input').val('');
                }
            });
        </script>
    @endpush
@endsection
