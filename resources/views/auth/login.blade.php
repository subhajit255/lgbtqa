<!DOCTYPE html>
<html lang="en">

<head>
    <title>LGBTQIA | Login</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="canonical" href="{{ asset('assets/logo/custom-2.svg') }}" />
    <link rel="shortcut icon" href="{{ asset('assets/media/logos/favicon.png') }}" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/glass-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/cdn/toastr.css') }}" />
    <script src="{{ asset('assets/js/custom_js/cdn/jquery.min.js') }}"></script>

    <style>
        /* Custom Modern Login Redesign Styles */
        :root {
            --purple-glow: #a855f7;
            --indigo-glow: #6366f1;
            --pink-glow: #ec4899;
            --dark-bg: #09090b;
        }

        body {
            background: var(--dark-bg) !important;
            font-family: 'Outfit', sans-serif !important;
            overflow-x: hidden;
            position: relative;
            color: #ffffff;
            min-height: 100vh;
        }

        /* Animated Backdrop Gradients */
        .bg-animated {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
            background: radial-gradient(circle at 50% 50%, #180828 0%, #09090b 100%);
        }

        .bg-animated .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.45;
            mix-blend-mode: screen;
            animation: drift 25s infinite alternate ease-in-out;
        }

        .bg-animated .circle-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, var(--purple-glow) 0%, rgba(168, 85, 247, 0) 70%);
            top: -10%;
            left: 10%;
            animation-duration: 20s;
        }

        .bg-animated .circle-2 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, var(--indigo-glow) 0%, rgba(99, 102, 241, 0) 70%);
            bottom: -15%;
            right: 5%;
            animation-duration: 28s;
            animation-delay: -5s;
        }

        .bg-animated .circle-3 {
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, var(--pink-glow) 0%, rgba(236, 72, 153, 0) 70%);
            top: 40%;
            right: 40%;
            animation-duration: 24s;
            animation-delay: -10s;
        }

        @keyframes drift {
            0% {
                transform: translate(0, 0) scale(1) rotate(0deg);
            }
            50% {
                transform: translate(80px, 50px) scale(1.2) rotate(180deg);
            }
            100% {
                transform: translate(-50px, -80px) scale(0.9) rotate(360deg);
            }
        }

        /* Layout Structure */
        .login-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
        }

        .login-sidebar {
            background-image: linear-gradient(to bottom, rgba(9, 9, 11, 0.45), rgba(9, 9, 11, 0.85)), url('{{ asset('assets/media/bg/login_background.png') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-right: 1px solid rgba(255, 255, 255, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem;
        }

        .brand-logo-text {
            font-family: 'Outfit', sans-serif;
            font-weight: 900;
            font-size: 2.2rem;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 0%, rgba(255, 255, 255, 0.7) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .brand-logo-badge {
            background: linear-gradient(135deg, var(--purple-glow) 0%, var(--indigo-glow) 100%);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 20px;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.4);
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .tagline-heading {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 3rem;
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 1.5rem;
            background: linear-gradient(135deg, #ffffff 30%, #e879f9 70%, #818cf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .tagline-desc {
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.6) !important;
            max-width: 480px;
        }

        /* Glass Login Card */
        .glass-login-card {
            background: rgba(255, 255, 255, 0.03) !important;
            backdrop-filter: blur(25px) saturate(190%) !important;
            -webkit-backdrop-filter: blur(25px) saturate(190%) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 24px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 
                        inset 0 1px 0 rgba(255, 255, 255, 0.05) !important;
            padding: 3.5rem 3rem !important;
            width: 100%;
            max-width: 460px;
            transition: border-color 0.4s ease, box-shadow 0.4s ease;
        }

        .glass-login-card:hover {
            border-color: rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 30px 60px -10px rgba(0, 0, 0, 0.6), 
                        inset 0 1px 0 rgba(255, 255, 255, 0.08),
                        0 0 40px rgba(168, 85, 247, 0.05) !important;
        }

        .card-title-main {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff !important;
        }

        .glass-login-card p,
        .glass-login-card .text-muted {
            color: rgba(255, 255, 255, 0.65) !important;
        }

        /* Custom Input Groups */
        .custom-input-group {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            align-items: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .custom-input-group:focus-within {
            border-color: var(--purple-glow);
            box-shadow: 0 0 20px rgba(168, 85, 247, 0.25);
            background: rgba(255, 255, 255, 0.06);
            transform: translateY(-1px);
        }

        .custom-input-group .input-group-text {
            background: transparent !important;
            border: none !important;
            color: rgba(255, 255, 255, 0.4) !important;
            padding-left: 18px !important;
            padding-right: 8px !important;
            font-size: 16px;
        }

        .custom-input-group .input-group-text .fa-envelope {
            background: linear-gradient(135deg, var(--pink-glow) 0%, #f472b6 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            filter: drop-shadow(0 0 5px rgba(236, 72, 153, 0.6)) !important;
        }

        .custom-input-group .input-group-text .fa-lock {
            background: linear-gradient(135deg, var(--purple-glow) 0%, #c084fc 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            filter: drop-shadow(0 0 5px rgba(168, 85, 247, 0.6)) !important;
        }

        .custom-input-group .form-control {
            background: transparent !important;
            background-color: transparent !important;
            border: none !important;
            border-color: transparent !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            color: #ffffff !important;
            font-family: 'Inter', sans-serif;
            font-size: 15px !important;
            padding: 15px 16px 15px 8px !important;
        }

        .custom-input-group .form-control::placeholder {
            color: rgba(255, 255, 255, 0.35) !important;
        }

        .custom-input-group .btn-toggle {
            background: transparent !important;
            border: none !important;
            color: rgba(255, 255, 255, 0.4) !important;
            padding-right: 18px !important;
            padding-left: 10px !important;
            font-size: 16px;
            transition: color 0.2s ease, transform 0.2s ease;
        }

        .custom-input-group .btn-toggle i {
            background: linear-gradient(135deg, var(--indigo-glow) 0%, #818cf8 100%) !important;
            -webkit-background-clip: text !important;
            -webkit-text-fill-color: transparent !important;
            filter: drop-shadow(0 0 5px rgba(99, 102, 241, 0.6)) !important;
        }

        .custom-input-group .btn-toggle:hover {
            color: #ffffff !important;
        }

        .custom-input-group .btn-toggle:active {
            transform: scale(0.92);
        }

        /* Links */
        .link-forgot {
            color: rgba(255, 255, 255, 0.5) !important;
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .link-forgot:hover {
            color: var(--purple-glow) !important;
            text-shadow: 0 0 10px rgba(168, 85, 247, 0.2);
            text-decoration: none;
        }

        /* Buttons */
        .btn-glass-submit {
            background: linear-gradient(135deg, var(--purple-glow) 0%, var(--indigo-glow) 100%) !important;
            border: none !important;
            border-radius: 14px !important;
            padding: 15px !important;
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 16px !important;
            color: #ffffff !important;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: 0 10px 25px -5px rgba(168, 85, 247, 0.35) !important;
            position: relative;
            overflow: hidden;
        }

        .btn-glass-submit::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            transition: left 0.6s ease;
        }

        .btn-glass-submit:hover::before {
            left: 100%;
        }

        .btn-glass-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(168, 85, 247, 0.55),
                        0 0 15px rgba(99, 102, 241, 0.3) !important;
        }

        .btn-glass-submit:active {
            transform: translateY(0);
        }

        /* Glass Modals Styling */
        .modal-content.glass-modal {
            background: rgba(15, 15, 20, 0.8) !important;
            backdrop-filter: blur(30px) saturate(200%) !important;
            -webkit-backdrop-filter: blur(30px) saturate(200%) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 24px !important;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.8),
                        inset 0 1px 0 rgba(255, 255, 255, 0.05);
            color: #ffffff;
        }

        .modal-header {
            border: none !important;
        }

        .modal-body label {
            color: rgba(255, 255, 255, 0.7) !important;
            font-size: 14px;
            font-weight: 500;
        }

        .modal-body .form-control-solid {
            background: rgba(255, 255, 255, 0.03) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            border-radius: 12px !important;
            color: #ffffff !important;
            padding: 13px 16px !important;
            transition: all 0.3s ease;
        }

        .modal-body .form-control-solid:focus {
            border-color: var(--purple-glow) !important;
            background: rgba(255, 255, 255, 0.06) !important;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.25) !important;
        }

        .btn-modal-cancel {
            background: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: rgba(255, 255, 255, 0.7) !important;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 12px !important;
            transition: all 0.3s ease;
        }

        .btn-modal-cancel:hover {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }

        .btn-modal-submit {
            background: linear-gradient(135deg, var(--purple-glow) 0%, var(--indigo-glow) 100%) !important;
            border: none !important;
            color: #ffffff !important;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 12px !important;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(168, 85, 247, 0.25);
        }

        .btn-modal-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px rgba(168, 85, 247, 0.45);
        }

        /* SVG Close button hover */
        .btn-close-custom {
            color: rgba(255, 255, 255, 0.5);
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .btn-close-custom:hover {
            color: #ffffff;
            transform: scale(1.1);
        }

        /* Fix visibility issues within glass modals (override Metronic styling) */
        .glass-modal h1,
        .glass-modal h2,
        .glass-modal h3,
        .glass-modal h4,
        .glass-modal h5,
        .glass-modal h6,
        .glass-modal label,
        .glass-modal span,
        .glass-modal .form-label,
        .glass-modal .required {
            color: #ffffff !important;
        }

        .glass-modal p,
        .glass-modal .text-muted {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .glass-modal .form-control,
        .glass-modal .form-control-solid {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
        }

        .glass-modal .form-control:focus,
        .glass-modal .form-control-solid:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--purple-glow) !important;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.25) !important;
        }

        .glass-modal .form-control::placeholder,
        .glass-modal .form-control-solid::placeholder {
            color: rgba(255, 255, 255, 0.4) !important;
        }

        .btn-modal-cancel {
            color: #ffffff !important;
        }

        /* Responsive Settings */
        @media (max-width: 991.98px) {
            .login-sidebar {
                padding: 3rem 2rem;
                text-align: center;
                border-right: none;
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            }
            .tagline-desc {
                margin: 0 auto;
            }
            .glass-login-card {
                padding: 2.5rem 2rem !important;
            }
        }
    </style>
</head>

@include('layout.partials.loader')

<body>
    <!-- Animated Blurred Ambient Circles -->
    <div class="bg-animated">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        <div class="circle circle-3"></div>
    </div>

    <!-- Main Container -->
    <div class="d-flex flex-column flex-lg-row min-vh-100 login-container" id="kt_app_root">
        
        <!-- Left Side: Branding / Intro -->
        <div class="col-lg-6 login-sidebar flex-column align-items-center align-items-lg-start justify-content-center">
            <div class="mb-5 text-center text-lg-start">
                <span class="brand-logo-badge">LGBTQIA PLATFORM 💜</span>
            </div>
            <h1 class="tagline-heading text-center text-lg-start">
                Find Your Perfect<br>Match with Love.
            </h1>
            <p class="tagline-desc text-center text-lg-start">
                Where hearts connect and love knows no boundaries. Join our safe, supportive, and inclusive community to build meaningful relationships.
            </p>
        </div>

        <!-- Right Side: Login Card -->
        <div class="col-lg-6 d-flex flex-column align-items-center justify-content-center p-6 p-md-12">
            <div class="glass-login-card">
                <div class="text-center mb-10">
                    <h2 class="card-title-main mb-2">Sign In</h2>
                    <p class="text-muted fs-7">Enter your credentials to access the console</p>
                </div>

                <form class="form w-100 formSubmit" novalidate="novalidate" id="kt_sign_in_form"
                    action="{{ route('admin.login') }}" method="POST">
                    @csrf

                    <!-- Email Input -->
                    <div class="custom-input-group mb-6">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="text" placeholder="Email Address" name="email" id="email"
                            autocomplete="off" class="form-control" />
                    </div>

                    <!-- Password Input -->
                    <div class="custom-input-group mb-4">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input id="password" type="password" placeholder="Password" name="password"
                            autocomplete="off" class="form-control" />
                        <button type="button" class="btn btn-toggle" id="togglePassword">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>

                    <!-- Forgot Password Link -->
                    <div class="d-flex justify-content-end mb-8">
                        <a href="javascript:void(0)" class="link-forgot" data-bs-toggle="modal"
                            data-bs-target="#forgot_password_form">Forgot Password?</a>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" id="kt_sign_in_submit" class="btn btn-glass-submit">
                            <span class="indicator-label">Sign In</span>
                            <span class="indicator-progress">
                                Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgot_password_form" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content glass-modal">
                <div class="modal-header pb-0 justify-content-end">
                    <button type="button" class="btn btn-sm btn-icon btn-close-custom" data-bs-dismiss="modal" aria-label="Close" style="background:none; border:none;">
                        <i class="fas fa-times fs-4"></i>
                    </button>
                </div>
                <div class="modal-body px-10 px-lg-15 pt-0 pb-12">
                    <form id="admin_forgot_password_form" class="form formSubmit"
                        action="{{ route('admin.forgot.password') }}" enctype='multipart/form-data'>
                        @csrf
                        <div class="mb-10 text-center">
                            <h2 class="fw-bold mb-3" style="color:#ffffff;">Forgot Password</h2>
                            <p class="text-muted fs-6">Enter your email address to receive a recovery link.</p>
                        </div>
                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center mb-2">
                                <span class="required">Email Address</span>
                            </label>
                            <input type="text" class="form-control form-control-solid"
                                placeholder="name@domain.com" name="forgot_email" id="forgot_email" autocomplete="off" />
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="reset" id="admin_password_form_cancel" class="btn btn-modal-cancel"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="admin_password_form_submit" class="btn btn-modal-submit">
                                <span class="indicator-label">Send Link</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="reset_password_form" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-500px">
            <div class="modal-content glass-modal">
                <div class="modal-header pb-0 justify-content-end">
                    <button type="button" class="btn btn-sm btn-icon btn-close-custom" data-bs-dismiss="modal" aria-label="Close" style="background:none; border:none;">
                        <i class="fas fa-times fs-4"></i>
                    </button>
                </div>
                <div class="modal-body px-10 px-lg-15 pt-0 pb-12">
                    <form id="admin_reset_password_form" class="form formSubmit"
                        action="{{ route('admin.reset.password') }}" enctype='multipart/form-data'>
                        @csrf
                        <input type="hidden" name="reset_token" id="reset_token">
                        <div class="mb-10 text-center">
                            <h2 class="fw-bold mb-3" style="color:#ffffff;">Reset Your Password</h2>
                            <p class="text-muted fs-6">Choose a secure, strong password for your account.</p>
                        </div>
                        <div class="d-flex flex-column mb-6 fv-row">
                            <label class="d-flex align-items-center mb-2">
                                <span class="required">New Password</span>
                            </label>
                            <input type="password" class="form-control form-control-solid"
                                placeholder="Enter New Password" name="new_password" id="new_password" />
                        </div>

                        <div class="d-flex flex-column mb-8 fv-row">
                            <label class="d-flex align-items-center mb-2">
                                <span class="required">Confirm Password</span>
                            </label>
                            <input type="password" class="form-control form-control-solid"
                                placeholder="Confirm New Password" name="password_confirmation"
                                id="password_confirmation" />
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="reset" id="admin_reset_password_form_cancel" class="btn btn-modal-cancel"
                                data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="admin_reset_password_form_submit" class="btn btn-modal-submit">
                                <span class="indicator-label">Reset Password</span>
                                <span class="indicator-progress">Please wait...
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        var baseUrl = "{{ url('/') }}";
        var APP_URL = "{{ json_encode(url('/')) }}";
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets/js/toastr.js') }}"></script>
    <script src="{{ asset('assets/js/operations.js') }}"></script>
    <script>
        $(window).on('load', function() {
            $("#preloader").fadeOut(0);
        });
        $(document).ready(function() {
            const linkExpire = "{{ $linkExpire ?? '' }}";
            const token = "{{ $token ?? '' }}";
            $('#reset_token').val(token);
            if (linkExpire == true) {
                swal.fire({
                    text: "Awww !!! Link Expired, Please try again",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, Got it!",
                    customClass: {
                        confirmButton: "btn btn-dark"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $(location).attr('href', "{{ route('admin.login') }}");
                    }
                })
            } else {
                if (token != '') {
                    $('#reset_password_form').modal('show');
                }
            }
        });

        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fas', 'fa-eye');
                eyeIcon.classList.add('fas', 'fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fas', 'fa-eye-slash');
                eyeIcon.classList.add('fas', 'fa-eye');
            }
        });
    </script>
</body>

</html>
