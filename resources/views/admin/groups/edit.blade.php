@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Edit Group</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.groups.index') }}">Group Chat</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="card">
                        <div class="card-body">
                            <form id="groupFormEdit" class="formSubmit fileUpload"
                                action="{{ route('admin.groups.update', $group->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="row g-9 mb-8">
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Group Name</label>
                                        <input type="text" class="form-control" name="name"
                                            value="{{ $group->name }}" required />
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="required fs-6 fw-semibold mb-2">Admin</label>
                                        <select class="form-select" name="admin_id" data-control="select2" required>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->id }}"
                                                    {{ $group->admin_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="fv-row mb-8">
                                    <label class="fs-6 fw-semibold mb-2">Description</label>
                                    <textarea class="form-control" rows="3" name="description">{{ $group->description }}</textarea>
                                </div>
                                <div class="row g-9 mb-8">
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Group Image</label>
                                        <div class="mt-1">
                                            <style>
                                                .image-input-placeholder {
                                                    background-image: url('{{ $group->image_path }}');
                                                }

                                                [data-bs-theme="dark"] .image-input-placeholder {
                                                    background-image: url('{{ $group->image_path }}');
                                                }
                                            </style>
                                            <div class="image-input image-input-outline image-input-placeholder {{ $group->image ? '' : 'image-input-empty' }}"
                                                data-kt-image-input="true">
                                                <div class="image-input-wrapper w-125px h-125px"
                                                    style="background-image: url('{{ $group->image_path }}')"></div>
                                                <label
                                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                    data-kt-image-input-action="change" data-bs-toggle="tooltip"
                                                    title="Change Image">
                                                    <i class="fa-solid fa-pen fs-7"></i>
                                                    <input type="file" name="image" accept=".png, .jpg, .jpeg" />
                                                    <input type="hidden" name="image_remove" />
                                                </label>
                                                <span
                                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                    data-kt-image-input-action="cancel" data-bs-toggle="tooltip"
                                                    title="Cancel Image">
                                                    <i class="fa-solid fa-xmark fs-2"></i>
                                                </span>
                                                <span
                                                    class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow"
                                                    data-kt-image-input-action="remove" data-bs-toggle="tooltip"
                                                    title="Remove Image">
                                                    <i class="fa-solid fa-xmark fs-2"></i>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="form-text">Allowed file types: png, jpg, jpeg.</div>
                                    </div>
                                    <div class="col-md-6 fv-row">
                                        <label class="fs-6 fw-semibold mb-2">Tags</label>
                                        <input type="text" class="form-control" name="tags"
                                            value="{{ $group->tags }}" />
                                    </div>
                                </div>
                                <div class="row g-9 mb-8">
                                    <div class="col-md-6 fv-row">
                                        <div class="form-check form-switch form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" value="1" id="is_public"
                                                name="is_public" {{ $group->is_public ? 'checked' : '' }} />
                                            <label class="form-check-label fw-semibold fs-6" for="is_public">Public
                                                Group</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center">
                                    <button type="submit" id="submitBtn" class="btn btn-primary">
                                        <span class="indicator-label">Update Group</span>
                                        <span class="indicator-progress">Please wait...
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
