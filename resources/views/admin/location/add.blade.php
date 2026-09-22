@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    {{ $detail ? 'Edit Location' : 'Add New Location' }}</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.location.list') }}" class="text-muted text-hover-primary">Locations</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">{{ $detail ? 'Edit' : 'Add' }}</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-body">
                    <form id="locationForm" method="POST" action="{{ route('admin.location.add', $detail->uuid ?? '') }}">
                        @csrf
                        @if($detail)
                            <input type="hidden" name="id" value="{{ $detail->id }}">
                        @endif

                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label required">Location Name</label>
                                <input type="text" name="name" class="form-control form-control-solid" placeholder="e.g. Central Warehouse - Zone A" value="{{ $detail->name ?? '' }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label d-block">Status</label>
                                <div class="form-check form-switch form-switch-sm form-check-custom form-check-solid">
                                    <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ isset($detail) ? ($detail->is_active == 1 ? 'checked' : '') : 'checked' }} />
                                    <label class="form-check-label">Active</label>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-12">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control form-control-solid" rows="3" placeholder="Optional description...">{{ $detail->description ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-12">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" id="location_address" class="form-control form-control-solid" placeholder="Search verified public address or map pin..." value="{{ $detail->address ?? '' }}">
                                <input type="hidden" step="any" name="lat" id="lat" class="form-control form-control-solid bg-secondary" placeholder="Auto-filled" value="{{ $detail->lat ?? '' }}" readonly>
                                <input type="hidden" step="any" name="lng" id="lng" class="form-control form-control-solid bg-secondary" placeholder="Auto-filled" value="{{ $detail->lng ?? '' }}" readonly>

                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-6">
                                <label class="form-label">Hours</label>
                                <input type="text" name="hours" class="form-control form-control-solid" placeholder="e.g. Mon-Fri 9AM-5PM" value="{{ $detail->hours ?? '' }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Official Link</label>
                                <input type="url" name="official_link" class="form-control form-control-solid" placeholder="https://..." value="{{ $detail->official_link ?? '' }}">
                            </div>
                        </div>

                        <div class="row mb-6">
                            <div class="col-md-12">
                                <label class="form-label required">Admin Record Lifecycle Status</label>
                                <select name="status" class="form-select form-select-solid" required>
                                    @php $currentStatus = $detail->status ?? 'DRAFT'; @endphp
                                    @foreach(['DRAFT', 'SUBMITTED', 'IN_REVIEW', 'CHANGES_REQUESTED', 'APPROVED', 'PUBLISHED', 'PAUSED', 'EXPIRED', 'REVOKED', 'ARCHIVED'] as $status)
                                        <option value="{{ $status }}" {{ $currentStatus == $status ? 'selected' : '' }}>{{ str_replace('_', ' ', $status) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('admin.location.list') }}" class="btn btn-light me-3">Cancel</a>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <span class="indicator-label">{{ $detail ? 'Update Location' : 'Add Location' }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('script')
        <script src="https://maps.googleapis.com/maps/api/js?key={{ config('constants.GEOLOCATION_API_KEY') }}&libraries=places&callback=initAutocomplete" async defer></script>
        <script>
            function initAutocomplete() {
                var input = document.getElementById('location_address');
                var autocomplete = new google.maps.places.Autocomplete(input);
                
                // Prevent form submission on enter in the address field
                input.addEventListener('keydown', function(e) {
                    if (e.keyCode === 13) {
                        e.preventDefault();
                    }
                });

                autocomplete.addListener('place_changed', function() {
                    var place = autocomplete.getPlace();
                    if (!place.geometry) {
                        return;
                    }
                    
                    document.getElementById('lat').value = place.geometry.location.lat();
                    document.getElementById('lng').value = place.geometry.location.lng();
                });
            }

            $('#locationForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                $('#submitBtn').attr('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

                $.ajax({
                    url: $(this).attr('action'),
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.status) {
                            toastr.success(response.message);
                            setTimeout(() => window.location.href = response.url, 1000);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON?.errors;
                        if (errors) {
                            Object.values(errors).forEach(err => toastr.error(err[0]));
                        } else {
                            toastr.error('Something went wrong!');
                        }
                    },
                    complete: function() {
                        $('#submitBtn').attr('disabled', false).html('{{ $detail ? "Update Location" : "Add Location" }}');
                    }
                });
            });
        </script>
    @endpush
@endsection