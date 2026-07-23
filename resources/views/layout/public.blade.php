<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} | LGBTQIA</title>
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.85);
            --glass-border: rgba(255, 255, 255, 0.2);
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--primary-gradient);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #2d3748;
        }

        .main-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .policy-card {
            background: var(--glass-bg);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            width: 100%;
            max-width: 900px;
            padding: 3rem;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .policy-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .policy-title {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1rem;
        }

        .policy-content {
            line-height: 1.8;
            font-size: 1.1rem;
            color: #4a5568;
        }

        .policy-content h2 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 700;
            color: #2d3748;
        }

        .footer {
            padding: 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .policy-card {
                padding: 1.5rem;
            }
            .policy-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <div class="policy-card">
            <div class="policy-header">
                <h1 class="policy-title">{{ $title }}</h1>
                <div class="w-100px h-4px bg-primary mx-auto rounded" style="background: var(--primary-gradient) !important;"></div>
            </div>
            <div class="policy-content">
                @yield('content')
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; {{ date('Y') }} LGBTQIA. All rights reserved.</p>
    </footer>

    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
</body>
</html>
