@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Add Manual KYC Verification</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.kyc.list') }}">KYC List</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Add New</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card card-flush">
                        <div class="card-body">
                            <form action="{{ route('admin.kyc.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                
                                <div class="row mb-6">
                                    <label class="col-lg-3 col-form-label required fw-semibold fs-6">Select User</label>
                                    <div class="col-lg-9 text-dark">
                                        <select name="user_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select a user" required>
                                            <option value=""></option>
                                            @foreach($users as $user)
                                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-3 col-form-label required fw-semibold fs-6">Govt ID Image</label>
                                    <div class="col-lg-9">
                                        <input type="file" name="govt_id_image" class="form-control form-control-solid" accept="image/*" required>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-3 col-form-label required fw-semibold fs-6">Identity Image (Selfie)</label>
                                    <div class="col-lg-9">
                                        <input type="file" name="identity_image" class="form-control form-control-solid" accept="image/*" required>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-3 col-form-label required fw-semibold fs-6">Select Badge Style</label>
                                    <div class="col-lg-9">
                                        <select name="badge_style_id" class="form-select form-select-solid" required>
                                            <option value="">-- Select Style --</option>
                                            @foreach($badgeStyles as $style)
                                                <option value="{{ $style->id }}" {{ old('badge_style_id') == $style->id ? 'selected' : '' }}>
                                                    {{ $style->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-6">
                                    <label class="col-lg-3 col-form-label required fw-semibold fs-6">Select Badge Color</label>
                                    <div class="col-lg-9">
                                        <select name="badge_color_id" class="form-select form-select-solid" required>
                                            <option value="">-- Select Color --</option>
                                            @foreach($badgeColors as $color)
                                                <option value="{{ $color->id }}" {{ old('badge_color_id') == $color->id ? 'selected' : '' }}>
                                                    {{ $color->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="row mt-10">
                                    <div class="col-12 d-flex justify-content-end">
                                        <a href="{{ route('admin.kyc.list') }}" class="btn btn-light me-3">Cancel</a>
                                        <button type="submit" class="btn btn-primary">Submit & Auto-Approve Verification</button>
                                    </div>
                                </div>

                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
