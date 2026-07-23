<div class="d-flex flex-column flex-center w-100 py-15 px-10">
    <div class="mb-5 text-center">
        <div class="empty-state-icon mb-6">
            <svg width="200" height="200" viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="100" cy="100" r="80" fill="url(#paint0_linear_empty)" fill-opacity="0.1" />
                <path
                    d="M100 60C77.9086 60 60 77.9086 60 100C60 122.091 77.9086 140 100 140C122.091 140 140 122.091 140 100C140 77.9086 122.091 60 100 60ZM100 125C86.1929 125 75 113.807 75 100C75 86.1929 86.1929 75 100 75C113.807 75 125 86.1929 125 100C125 113.807 113.807 125 100 125Z"
                    fill="url(#paint0_linear_empty)" />
                <path d="M100 85V105" stroke="url(#paint0_linear_empty)" stroke-width="5" stroke-linecap="round" />
                <path d="M100 115H100.01" stroke="url(#paint0_linear_empty)" stroke-width="5" stroke-linecap="round" />
                <defs>
                    <linearGradient id="paint0_linear_empty" x1="60" y1="60" x2="140" y2="140"
                        gradientUnits="userSpaceOnUse">
                        <stop stop-color="#7239ea" />
                        <stop offset="1" stop-color="#009ef7" />
                    </linearGradient>
                </defs>
            </svg>
        </div>
        <h3 class="fs-2x fw-bold text-gray-900 mb-2">{{ $title ?? 'No Data Found' }}</h3>
        <p class="fs-5 text-gray-500 fw-semibold mb-8">
            {{ $description ?? 'It seems there is no information to display at the moment.' }}
        </p>
        @if (isset($action_url) && isset($action_text))
            <a href="{{ $action_url }}" class="btn btn-primary px-8 py-4 fw-bold">
                <i class="{{ $action_icon ?? 'bi bi-plus-lg' }} fs-3 me-2"></i>
                {{ $action_text }}
            </a>
        @endif
    </div>
</div>

<style>
    .empty-state-icon {
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-15px);
        }
    }
</style>
