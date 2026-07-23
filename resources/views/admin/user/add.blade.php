@extends('layout.app')
@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/css/intlTelInput.css"
        integrity="sha512-gxWow8Mo6q6pLa1XH/CcH8JyiSDEtiwJV78E+D+QP0EVasFs8wKXq16G8CLD4CJ2SnonHr4Lm/yY2fSI2+cbmw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .iti {
            position: relative;
            display: block !important;
        }
    </style>
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Customer {{ !empty($details) ? 'Edit' : 'Add' }}</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.user.list') }}" class="text-muted text-hover-primary">Customer</a>
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
                                <form id="userForm" action="{{ route('admin.user.add') }}" method="POST"
                                    class="formSubmit fileUpload" enctype="multipart/form-data">
                                    <input type="hidden" name="id" name="id" value="{{ $details->id ?? null }}">
                                    <div class="row pt-2">
                                        <div class="col-md-6">
                                            <label>
                                                <span class="label_title">User Image</span>
                                                <span class="astrict_sign">*</span>
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
                                                            background-image: url('/assets/media/svg/files/blank-image.png');
                                                        }

                                                        [data-bs-theme="dark"] .image-input-placeholder {
                                                            background-image: url('/assets/media/svg/files/blank-image.png');
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
                                                        <label for="name" class="label-style">Name</label>
                                                        <input type="text" class="form-control fromAlias"
                                                            placeholder="Enter Name" name="name" id="name"
                                                            value="{{ $details->name ?? null }}">
                                                    </div>
                                                </div>
                                                <div class="col-md-12 pt-4">
                                                    <div class="form-group">
                                                        <label for="username" class="label-style">Username</label>
                                                        <input type="text" class="form-control toAlias"
                                                            placeholder="Enter Slug" name="username" id="username"
                                                            value="{{ $details->username ?? null }}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-4">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="mobile_number" class="label-style">Mobile Number</label>
                                                <span class="astrict_sign">*</span>
                                                <input type="text" class="form-control number-only"
                                                    placeholder="Enter mobile number" maxlength="10" name="mobile_number"
                                                    id="mobile_number" value="{{ $details->mobile_number ?? null }}">
                                                <input type="hidden" name="phone_code" id="phone_code"
                                                    value="{{ $details->phone_code ?? null }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email" class="label-style">Email</label>
                                                <span class="astrict_sign">*</span>
                                                <input type="text" class="form-control" placeholder="Enter Email"
                                                    name="email" id="email" value="{{ $details->email ?? null }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row pt-4">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="country_id" class="label-style">Country</label>
                                                <span class="astrict_sign">*</span>
                                                <select class="form-control" name="country_id" id="country_id">
                                                    <option value="">Select Country</option>
                                                    @foreach (getCountries() as $country)
                                                        <option value="{{ $country['id'] }}"
                                                            {{ !empty($details->country_id) && $details->country_id == $country['id'] ? 'selected' : '' }}>
                                                            {{ $country['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="state_id" class="label-style">State</label>
                                                <span class="astrict_sign">*</span>
                                                <select class="form-control" name="state_id" id="state_id">
                                                    <option value="">Select State</option>
                                                    @if (!empty($details->country_id) && $details->country_id)
                                                        @foreach (getStates($details->country_id) as $state)
                                                            <option value="{{ $state['id'] }}"
                                                                {{ !empty($details->state_id) && $details->state_id == $state['id'] ? 'selected' : '' }}>
                                                                {{ $state['name'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="city_id" class="label-style">City</label>
                                                <span class="astrict_sign">*</span>
                                                <select class="form-control" name="city_id" id="city_id">
                                                    <option value="">Select City</option>
                                                    @if (!empty($details->state_id) && $details->state_id)
                                                        @foreach (getCities($details->state_id) as $city)
                                                            <option value="{{ $city['id'] }}"
                                                                {{ !empty($details) && $details->city_id == $city['id'] ? 'selected' : '' }}>
                                                                {{ $city['name'] }}
                                                            </option>
                                                        @endforeach
                                                    @endif
                                                </select>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.12/js/intlTelInput-jquery.min.js"
            integrity="sha512-QK4ymL3xaaWUlgFpAuxY+6xax7QuxPB3Ii/99nykNP/PlK3NTQa/f/UbQQnWsM4h5yjQoMjWUhCJbYgWamtL6g=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script>
            $(document).ready(function() {
                const codes = {
                    '1': 'us',
                    '44': 'gb',
                    '93': 'af',
                    '355': 'al',
                    '213': 'dz',
                    '376': 'ad',
                    '244': 'ao',
                    '54': 'ar',
                    '374': 'am',
                    '61': 'au',
                    '43': 'at',
                    '994': 'az',
                    '973': 'bh',
                    '880': 'bd',
                    '32': 'be',
                    '229': 'bj',
                    '975': 'bt',
                    '591': 'bo',
                    '55': 'br',
                    '359': 'bg',
                    '226': 'bf',
                    '855': 'kh',
                    '237': 'cm',
                    '1': 'ca',
                    '56': 'cl',
                    '86': 'cn',
                    '57': 'co',
                    '506': 'cr',
                    '385': 'hr',
                    '53': 'cu',
                    '357': 'cy',
                    '420': 'cz',
                    '45': 'dk',
                    '20': 'eg',
                    '503': 'sv',
                    '251': 'et',
                    '358': 'fi',
                    '33': 'fr',
                    '995': 'ge',
                    '49': 'de',
                    '233': 'gh',
                    '30': 'gr',
                    '502': 'gt',
                    '224': 'gn',
                    '509': 'ht',
                    '504': 'hn',
                    '852': 'hk',
                    '36': 'hu',
                    '91': 'in',
                    '62': 'id',
                    '98': 'ir',
                    '353': 'ie',
                    '972': 'il',
                    '39': 'it',
                    '81': 'jp',
                    '962': 'jo',
                    '254': 'ke',
                    '996': 'kg',
                    '856': 'la',
                    '371': 'lv',
                    '961': 'lb',
                    '218': 'ly',
                    '370': 'lt',
                    '352': 'lu',
                    '60': 'my',
                    '223': 'ml',
                    '356': 'mt',
                    '52': 'mx',
                    '373': 'md',
                    '212': 'ma',
                    '31': 'nl',
                    '64': 'nz',
                    '234': 'ng',
                    '47': 'no',
                    '92': 'pk',
                    '51': 'pe',
                    '63': 'ph',
                    '48': 'pl',
                    '351': 'pt',
                    '974': 'qa',
                    '40': 'ro',
                    '7': 'ru',
                    '966': 'sa',
                    '65': 'sg',
                    '421': 'sk',
                    '386': 'si',
                    '27': 'za',
                    '82': 'kr',
                    '34': 'es',
                    '94': 'lk',
                    '46': 'se',
                    '41': 'ch',
                    '66': 'th',
                    '90': 'tr',
                    '380': 'ua',
                    '971': 'ae',
                    '598': 'uy',
                    '998': 'uz',
                    '58': 've',
                    '84': 'vn',
                    '260': 'zm',
                    '263': 'zw'
                };
                var phoneCode = "{{ $details->phone_code ?? '91' }}";
                var instance = $("[name=mobile_number]");
                instance.intlTelInput({
                    initialCountry: codes[phoneCode] ?? 'IN' // Set default country to India
                });

                console.log(phoneCode, codes[phoneCode]);


                $("[name=mobile_number]").on("blur", function() {
                    const phoneCode = instance.intlTelInput('getSelectedCountryData').dialCode;
                    $("#phone_code").val(phoneCode);
                });

                $('#country_id').on('change', function() {
                    var countryID = $(this).val();
                    $('#state_id').html('<option value="">Select State</option>');
                    $('#city_id').html('<option value="">Select City</option>');
                    if (countryID) {
                        $.ajax({
                            url: "{{ route('admin.user.get.states') }}/" + countryID,
                            type: "GET",
                            dataType: "json",
                            success: function(data) {
                                $.each(data, function(key, value) {
                                    $('#state_id').append('<option value="' + value.id +
                                        '">' + value.name + '</option>');
                                });
                            }
                        });
                    }
                });

                $('#state_id').on('change', function() {
                    var stateID = $(this).val();
                    $('#city_id').html('<option value="">Select City</option>');
                    if (stateID) {
                        $.ajax({
                            url: "{{ route('admin.user.get.cities') }}/" + stateID,
                            type: "GET",
                            dataType: "json",
                            success: function(data) {
                                $.each(data, function(key, value) {
                                    $('#city_id').append('<option value="' + value.id +
                                        '">' + value.name + '</option>');
                                });
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
@endsection
