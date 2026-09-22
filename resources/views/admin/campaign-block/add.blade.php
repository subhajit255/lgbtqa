@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Schedule Campaign Block</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.campaign-block.list') }}" class="text-muted text-hover-primary">Campaign Blocks</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Schedule</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <a href="{{ route('admin.campaign-block.list') }}" class="btn btn-sm fw-bold btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
            </div>
        </div>
    </div>
    
    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-body py-10">
                    <form action="{{ route('admin.campaign-block.add') }}" method="POST" class="form formSubmit" id="campaignBlockForm">
                        @csrf
                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="form-label required">Target Type</label>
                                <select name="target_type" id="target_type" class="form-select form-select-solid" required>
                                    <option value="App\Models\Event">Event</option>
                                    <option value="App\Models\Location">Location</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Search Inventory Item</label>
                                <select name="target_id" id="target_id" class="form-select form-select-solid" required data-control="select2" data-placeholder="Search for item...">
                                    <option></option>
                                </select>
                                <div class="text-muted fs-7 mt-2">Search for the approved event or location you want to feature.</div>
                            </div>
                        </div>

                        <div class="row mb-8">
                            <div class="col-md-6">
                                <label class="form-label required">Campaign Type</label>
                                <select name="type" class="form-select form-select-solid" required>
                                    <option value="FEATURED">Featured Event/Location</option>
                                    <option value="WEEKEND_PICK">Weekend Pick</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label required">Start Date</label>
                                <input type="date" name="start_date" class="form-control form-control-solid" required min="{{ date('Y-m-d') }}" />
                                <div class="text-muted fs-7 mt-2">Campaigns are scheduled in 5-day blocks. The system will calculate the end date automatically. Overbooking will be prevented.</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-10">
                            <button type="submit" class="btn btn-primary" id="kt_submit_button">
                                <span class="indicator-label">Schedule Block</span>
                                <span class="indicator-progress">Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
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
            function initSelect2() {
                var type = $('#target_type').val();
                $('#target_id').select2({
                    ajax: {
                        url: "{{ route('admin.campaign-block.search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: function (params) {
                            return {
                                q: params.term, // search term
                                type: type
                            };
                        },
                        processResults: function (data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 1
                });
            }

            initSelect2();

            $('#target_type').change(function() {
                $('#target_id').val(null).trigger('change');
                $('#target_id').select2('destroy');
                initSelect2();
            });
        });
    </script>
@endpush
