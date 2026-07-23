<!DOCTYPE html>
<html lang="en">

<head>
    <title>LGBTQIA | Delete Account</title>
    <meta charset="utf-8" />
    <link rel="canonical" href="{{ asset('assets/logo/custom-2.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glass-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/cdn/toastr.css') }}" />
    <script src="{{ asset('assets/js/custom_js/cdn/jquery.min.js') }}"></script>
</head>

@include('layout.partials.loader')

<body id="kt_body" class="app-blank bgi-size-cover bgi-position-center">
    <script>
        document.documentElement.setAttribute("data-bs-theme", "light");
    </script>
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <div class="d-flex flex-column flex-lg-row flex-column-fluid">
            <div class="d-flex flex-lg-row-fluid login_left">
                <div class="d-flex flex-column flex-center pb-0 pb-lg-10 p-10 w-100">
                    <div class="login-leftcon">
                        <h1 class="fs-2qx fw-bold text-center mb-7" style="background-color: #fff;border-radius: 8px;">
                            We're sorry to see you go 💜</h1>
                        <div class="fs-base text-center fw-semibold">Deleting your account is permanent and cannot be undone.
                            If you're sure, please enter your details below.
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column-fluid flex-lg-row-auto justify-content-center justify-content-lg-end p-12">
                <div class="bg-body d-flex flex-column flex-center rounded-4 w-md-600px p-10">
                    <div class="d-flex flex-center flex-column align-items-stretch h-lg-100 w-md-400px">
                        <div class="d-flex flex-center flex-column-fluid pb-15 pb-lg-20">
                            <form class="form w-100" id="kt_delete_account_form"
                                action="{{ route('delete.account.post') }}" method="POST">
                                @csrf
                                <div class="login-right">
                                    <div class="text-center mb-11">
                                        <h1
                                            style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%) !important; border-radius: 6px; padding: 10px; color: white; display: inline-block;">
                                            LGBTQIA
                                        </h1>
                                    </div>
                                    <div class="text-center mb-11">
                                        <h1 class="text-dark fw-bolder mb-3">Delete Account</h1>
                                        <div class="text-gray-500 fw-semibold fs-6">Enter your email or phone number</div>
                                    </div>

                                    @if(session('success'))
                                        <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                                            <i class="fas fa-check-circle fs-2hx text-success me-4"></i>
                                            <div class="d-flex flex-column">
                                                <h4 class="mb-1 text-success">Success</h4>
                                                <span>{{ session('success') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if(session('error'))
                                        <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                                            <i class="fas fa-exclamation-triangle fs-2hx text-danger me-4"></i>
                                            <div class="d-flex flex-column">
                                                <h4 class="mb-1 text-danger">Error</h4>
                                                <span>{{ session('error') }}</span>
                                            </div>
                                        </div>
                                    @endif

                                    @if($errors->any())
                                        <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                                            <div class="d-flex flex-column">
                                                @foreach ($errors->all() as $error)
                                                    <span>{{ $error }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <div class="fv-row mb-8">
                                        <input type="text" placeholder="Email or Phone Number" name="identity" id="identity"
                                            autocomplete="off" class="form-control bg-transparent" required />
                                    </div>
                                    
                                    <div class="d-grid mb-10">
                                        <button type="submit" id="kt_delete_submit" class="btn btn-danger">
                                            <span class="indicator-label">Delete Account</span>
                                            <span class="indicator-progress">Please wait...
                                                <span
                                                    class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <a href="{{ route('admin.login') }}" class="link-primary fw-bold">Back to Login</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets/js/toastr.js') }}"></script>
    <script>
        $(window).on('load', function() {
            $("#preloader").fadeOut(0);
        });

        $('#kt_delete_account_form').on('submit', function() {
            var btn = $('#kt_delete_submit');
            btn.attr('data-kt-indicator', 'on').attr('disabled', true);
        });
    </script>
</body>

</html>
