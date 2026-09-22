@extends('layout.app')
@section('content')
@php
    $times = [];
    $start = strtotime('12:00 AM');
    $end = strtotime('11:30 PM');
    for ($i = $start; $i <= $end; $i += 1800) {
        $times[] = date('g:i A', $i);
    }
@endphp
<div class="app-main flex-column flex-row-fluid" id="kt_app_main">
    <div class="d-flex flex-column flex-column-fluid">
        <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
            <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                    <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                        {{ !empty($details) ? 'Edit' : 'Add' }} Event</h1>
                    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item">
                            <span class="bullet bg-gray-400 w-5px h-2px"></span>
                        </li>
                        <li class="breadcrumb-item text-muted">
                            <a href="{{ route('admin.event.list') }}" class="text-muted text-hover-primary">Events</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <form id="eventForm" action="{{ route('admin.event.add') }}" method="POST" class="formSubmit fileUpload" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{ $details->id ?? null }}">

                    <div class="row g-9">
                        <!-- Main Content (Left Column) -->
                        <div class="col-lg-8">
                            
                            <!-- Basic Details Card -->
                            <div class="card shadow-sm border-0 mb-6">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Basic Details</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="fv-row mb-8">
                                        <label class="required fw-bold fs-6 mb-2">Event Title</label>
                                        <input type="text" name="title" class="form-control form-control-solid" placeholder="Downtown Rooftop Social" value="{{ $details->title ?? '' }}" required />
                                    </div>
                                    <div class="fv-row mb-8">
                                        <label class="fw-bold fs-6 mb-2">Short Description</label>
                                        <textarea name="description" class="form-control form-control-solid" rows="2" placeholder="An open, safe space for sharing words, stories, and voices...">{{ $details->description ?? '' }}</textarea>
                                    </div>

                                    <label class="d-block fw-bold fs-6 mb-2">
                                        <span class="required">Event Banner Image</span>
                                    </label>
                                    <div class="fv-row mb-5">
                                        @if (!empty($details->image))
                                        <style>
                                            .event-image-placeholder {
                                                background-image: url('{{ $details->image_path }}');
                                            }
                                        </style>
                                        @else
                                        <style>
                                            .event-image-placeholder {
                                                background-image: url('/assets/media/svg/files/blank-image.png');
                                            }
                                        </style>
                                        @endif
                                        <div class="image-input image-input-empty image-input-outline event-image-placeholder" data-kt-image-input="true" style="width: 100%; max-width: 400px;">
                                            <div class="image-input-wrapper w-100 h-200px" style="background-size: cover; background-position: center; border-radius: 8px;"></div>
                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Upload Event Banner">
                                                <div>
                                                    <i class="fa-solid fa-pen"></i>
                                                </div>
                                                <input type="file" name="file" accept=".png, .jpg, .jpeg" id="file" />
                                                <input type="hidden" name="avatar_remove" />
                                            </label>
                                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel image">
                                                <i class="fa-solid fa-xmark"></i>
                                            </span>
                                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove image">
                                                <i class="bi bi-x fs-2"></i>
                                            </span>
                                        </div>
                                        <div class="form-text mt-2 text-muted fs-7">Allowed types: png, jpg, jpeg. Recommended ratio 16:9</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Date & Location Card -->
                            <div class="card shadow-sm border-0 mb-6">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Date & Location</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="row g-9 mb-6">
                                        <div class="col-md-5">
                                            <label class="required fw-bold fs-6 mb-2">Event Date</label>
                                            <input type="text" name="event_date" id="event_date" class="form-control form-control-solid" placeholder="Select Event Date" value="{{ $details->event_date ?? '' }}" required />
                                        </div>
                                        <div class="col-md-7">
                                            <label class="fw-bold fs-6 mb-2">Time Zone</label>
                                            <input type="text" name="time_zone" class="form-control form-control-solid" placeholder="e.g. Europe/Zurich" value="{{ $details->time_zone ?? '' }}" />
                                        </div>
                                    </div>
                                    <div class="row g-9 mb-6 align-items-center">
                                        <div class="col-md-3">
                                            <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                                <input class="form-check-input" type="checkbox" name="is_all_day" id="is_all_day_check" value="1" {{ !empty($details->is_all_day) ? 'checked' : '' }} />
                                                <label class="form-check-label fw-bold fs-6 text-dark" for="is_all_day_check">All Day Event</label>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="required fw-bold fs-6 mb-2">Start Time</label>
                                            <select name="start_time" id="start_time" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Start Time" required>
                                                <option value=""></option>
                                                @foreach($times as $time)
                                                    <option value="{{ $time }}" {{ (isset($details->start_time) && $details->start_time == $time) ? 'selected' : '' }}>{{ $time }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="fw-bold fs-6 mb-2">End Time</label>
                                            <select name="end_time" id="end_time" class="form-select form-select-solid" data-control="select2" data-placeholder="Select End Time">
                                                <option value=""></option>
                                                @foreach($times as $time)
                                                    <option value="{{ $time }}" {{ (isset($details->end_time) && $details->end_time == $time) ? 'selected' : '' }}>{{ $time }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="row g-9 mb-2">
                                        <div class="col-md-6">
                                            <label class="fw-bold fs-6 mb-2">Location (Linked)</label>
                                            <select name="location_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Select Location">
                                                <option value="">None</option>
                                                @foreach($locations ?? [] as $loc)
                                                    <option value="{{ $loc->id }}" {{ (isset($details->location_id) && $details->location_id == $loc->id) ? 'selected' : '' }}>{{ $loc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="fw-bold fs-6 mb-2">Location String (Fallback)</label>
                                            <input type="text" name="location_string" class="form-control form-control-solid" placeholder="Switzerland, Zurich" value="{{ $details->location_string ?? ($details->location ?? '') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Content Card -->
                            <div class="card shadow-sm border-0 mb-6">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">About Event (Detailed Content)</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <textarea name="about" id="about_editor" class="form-control form-control-solid" rows="5" placeholder="Join us for an evening above the city, where good conversations, open energy, and meaningful connections come together...">{{ $details->about ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Side Content (Right Column) -->
                        <div class="col-lg-4">
                            
                            <!-- Status & Publishing Card -->
                            <div class="card shadow-sm border-0 mb-6">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Status & Publishing</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2 required">Admin Lifecycle Status</label>
                                        <select name="admin_status" class="form-select form-select-solid" required>
                                            @php $currentStatus = $details->admin_status ?? 'DRAFT'; @endphp
                                            @foreach(['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'CHANGES_REQUESTED', 'APPROVED', 'PUBLISHED', 'PAUSED', 'EXPIRED', 'REVOKED', 'ARCHIVED'] as $status)
                                                <option value="{{ $status }}" {{ $currentStatus == $status ? 'selected' : '' }}>{{ str_replace('_', ' ', $status) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Activity Status</label>
                                        <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                            <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ !isset($details) || (isset($details->is_active) && $details->is_active == 1) ? 'checked' : '' }} />
                                            <label class="form-check-label fw-bold fs-6 text-dark">Active on Platform</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Host Details Card -->
                            <div class="card shadow-sm border-0 mb-6">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Host Details</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="d-flex align-items-center mb-6">
                                        @if (!empty($details->host_image))
                                        <style>
                                            .host-image-placeholder {
                                                background-image: url('{{ $details->host_image_path }}');
                                            }
                                        </style>
                                        @else
                                        <style>
                                            .host-image-placeholder {
                                                background-image: url('/assets/media/avatars/blank.png');
                                            }
                                        </style>
                                        @endif
                                        <div class="image-input image-input-empty image-input-outline image-input-circle host-image-placeholder me-5" data-kt-image-input="true">
                                            <div class="image-input-wrapper w-75px h-75px" style="background-size: cover; background-position: center;"></div>
                                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Upload Host Image">
                                                <i class="fa-solid fa-pen fs-7"></i>
                                                <input type="file" name="host_file" accept=".png, .jpg, .jpeg" id="host_file" />
                                                <input type="hidden" name="host_avatar_remove" />
                                            </label>
                                        </div>
                                        <div class="flex-grow-1">
                                            <label class="required fw-bold fs-6 mb-2">Host Name</label>
                                            <input type="text" name="host_name" class="form-control form-control-solid" placeholder="Queer Mixer" value="{{ $details->host_name ?? '' }}" required />
                                        </div>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Host Pronouns</label>
                                        <input type="text" name="host_pronouns" class="form-control form-control-solid" placeholder="They/Them" value="{{ $details->host_pronouns ?? '' }}" />
                                    </div>
                                    <div class="fv-row mb-2">
                                        <label class="fw-bold fs-6 mb-2">Host Type</label>
                                        <select name="host_type" class="form-select form-select-solid">
                                            <option value="PARTNER" {{ (isset($details->host_type) && $details->host_type == 'PARTNER') ? 'selected' : '' }}>PARTNER</option>
                                            <option value="USER" {{ (isset($details->host_type) && $details->host_type == 'USER') ? 'selected' : '' }}>USER</option>
                                            <option value="EXTERNAL" {{ (isset($details->host_type) && $details->host_type == 'EXTERNAL') ? 'selected' : '' }}>EXTERNAL</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Ticketing & Targeting Card -->
                            <div class="card shadow-sm border-0 mb-6">
                                <div class="card-header border-0 pt-6">
                                    <h3 class="card-title align-items-start flex-column">
                                        <span class="card-label fw-bold fs-3 mb-1">Targeting & Ticketing</span>
                                    </h3>
                                </div>
                                <div class="card-body pt-5">
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Age Restriction</label>
                                        <select name="age_restriction" class="form-select form-select-solid">
                                            <option value="ALL" {{ (isset($details->age_restriction) && $details->age_restriction == 'ALL') ? 'selected' : '' }}>All Ages</option>
                                            <option value="16-17" {{ (isset($details->age_restriction) && $details->age_restriction == '16-17') ? 'selected' : '' }}>16-17</option>
                                            <option value="18+" {{ (isset($details->age_restriction) && $details->age_restriction == '18+') ? 'selected' : '' }}>18+</option>
                                        </select>
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Audience Visibility</label>
                                        <input type="text" name="audience" class="form-control form-control-solid" placeholder="Friends" value="{{ $details->audience ?? 'Friends' }}" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Tags / Categories (Comma separated)</label>
                                        <input type="text" name="tags" class="form-control form-control-solid" placeholder="Party, Nightlife, Event" value="{{ $details->tags ?? '' }}" />
                                    </div>
                                    <div class="fv-row mb-6">
                                        <label class="fw-bold fs-6 mb-2">Official Ticket URL</label>
                                        <input type="url" name="official_ticket_url" class="form-control form-control-solid" placeholder="https://..." value="{{ $details->official_ticket_url ?? '' }}" />
                                    </div>
                                    <div class="fv-row mb-2">
                                        <label class="fw-bold fs-6 mb-2">Source Attribution</label>
                                        <input type="text" name="source_attribution" class="form-control form-control-solid" placeholder="Eventfrog / gay.ch" value="{{ $details->source_attribution ?? '' }}" />
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mb-8 mt-4">
                        <button type="button" class="btn btn-light me-4" onclick="window.history.back()">Cancel</button>
                        <button type="submit" id="submitBtn" class="btn btn-primary px-8">
                            <span class="indicator-label">{{ !empty($details) ? 'Update Event' : 'Save Event' }}</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/super-build/ckeditor.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Initialize Flatpickr for Date Picker (limiting past dates)
        if (typeof flatpickr !== 'undefined') {
            flatpickr("#event_date", {
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                minDate: "today"
            });
        } else if (typeof jQuery !== 'undefined' && jQuery.fn.flatpickr) {
            $("#event_date").flatpickr({
                altInput: true,
                altFormat: "F j, Y",
                dateFormat: "Y-m-d",
                minDate: "today"
            });
        }

        // Toggle time fields when "All Day" is checked
        $('#is_all_day_check').on('change', function() {
            if($(this).is(':checked')) {
                $('#start_time').val(null).trigger('change').prop('disabled', true);
                $('#end_time').val(null).trigger('change').prop('disabled', true);
            } else {
                $('#start_time').prop('disabled', false);
                $('#end_time').prop('disabled', false);
            }
        });
        // Run on load
        if($('#is_all_day_check').is(':checked')) {
            $('#start_time').prop('disabled', true);
            $('#end_time').prop('disabled', true);
        }

        if(typeof CKEDITOR !== 'undefined' || typeof CKEditor5 !== 'undefined') {
            CKEDITOR.ClassicEditor.create(document.querySelector('#about_editor'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'strikethrough', 'underline', '|',
                        'bulletedList', 'numberedList', 'todoList', '|',
                        'outdent', 'indent', '|',
                        'undo', 'redo', '|',
                        'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                        'alignment', '|',
                        'link', 'blockQuote', 'insertTable', '|',
                        'specialCharacters', 'horizontalLine', '|',
                        'sourceEditing'
                    ],
                    shouldNotGroupWhenFull: true
                },
                placeholder: 'Detailed event description...',
                htmlSupport: {
                    allow: [
                        {
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }
                    ]
                }
            }).catch(error => {
                console.error(error);
            });
        }
    });
</script>
@endpush
