@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                    Analytics Dashboard</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted">
                        <a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a>
                    </li>
                    <li class="breadcrumb-item">
                        <span class="bullet bg-gray-400 w-5px h-2px"></span>
                    </li>
                    <li class="breadcrumb-item text-muted">Control Center</li>
                </ul>
            </div>
            <div class="d-flex align-items-center gap-2 gap-lg-3">
                <span class="badge badge-light-primary fs-7 fw-semibold px-4 py-2">
                    <i class="fas fa-clock text-primary me-1"></i>
                    {{ \Carbon\Carbon::now()->format('l, M d Y — h:i A') }}
                </span>
                <a href="{{ route('admin.user.list') }}" class="btn btn-sm fw-bold btn-primary">
                    <i class="fas fa-users me-1"></i> Users</a>
                <a href="{{ route('admin.setting.update') }}" class="btn btn-sm fw-bold btn-light-dark">
                    <i class="fas fa-cog me-1"></i> Settings</a>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">

            {{-- ═══════════════ ROW 1: HERO KPI CARDS ═══════════════ --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                @php
                    $heroStats = [
                        [
                            'label' => 'Total Users',
                            'value' => $details['total_users'],
                            'today' => $details['today_users'],
                            'icon' => 'fa-users',
                            'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            'sparkData' => $details['registration_data'],
                            'growth' => $details['user_growth_pct'],
                            'route' => route('admin.user.list'),
                        ],
                        [
                            'label' => 'Total Posts',
                            'value' => $details['total_posts'],
                            'today' => $details['today_posts'],
                            'icon' => 'fa-signs-post',
                            'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                            'sparkData' => $details['post_data'],
                            'growth' => $details['post_growth_pct'],
                            'route' => route('admin.post.list'),
                        ],
                        [
                            'label' => 'Engagements',
                            'value' => $details['total_comments'] + $details['total_loves'] + $details['total_emojis'] + $details['total_stars'],
                            'today' => $details['today_comments'] + $details['today_loves'],
                            'icon' => 'fa-heart-pulse',
                            'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                            'sparkData' => $details['engagement_comments'],
                            'growth' => 0,
                            'route' => '#',
                        ],
                        [
                            'label' => 'Communities',
                            'value' => $details['total_communities'],
                            'today' => $details['total_events'],
                            'icon' => 'fa-people-group',
                            'gradient' => 'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                            'sparkData' => $details['login_activity'],
                            'growth' => 0,
                            'route' => '#',
                        ],
                    ];
                @endphp

                @foreach ($heroStats as $idx => $stat)
                    <div class="col-xl-3 col-md-6">
                        <a href="{{ $stat['route'] }}"
                            class="card border-0 shadow-sm kpi-card text-decoration-none animate-in"
                            style="animation-delay: {{ $idx * 0.1 }}s">
                            <div class="card-body p-6">
                                <div class="d-flex align-items-center justify-content-between mb-4">
                                    <div class="kpi-icon-wrap" style="background: {{ $stat['gradient'] }}">
                                        <i class="fas {{ $stat['icon'] }} text-white fs-3"></i>
                                    </div>
                                    @if ($stat['growth'] != 0)
                                        <span class="badge {{ $stat['growth'] >= 0 ? 'badge-light-success' : 'badge-light-danger' }} fs-8 fw-bold">
                                            <i class="fas {{ $stat['growth'] >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }} me-1" style="font-size: 9px"></i>
                                            {{ abs($stat['growth']) }}%
                                        </span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-end justify-content-between">
                                    <div>
                                        <h3 class="fs-2x fw-bold text-gray-900 mb-0 counter-value" data-target="{{ $stat['value'] }}">0</h3>
                                        <span class="text-gray-500 fw-semibold fs-7">{{ $stat['label'] }}</span>
                                    </div>
                                    <div class="sparkline-mini" id="spark_{{ $idx }}"></div>
                                </div>
                                <div class="mt-3 pt-3 border-top border-gray-200">
                                    <span class="fs-8 fw-semibold text-gray-500">
                                        <span class="text-primary fw-bold">+{{ $stat['today'] }}</span> today
                                        @if ($stat['label'] == 'Communities')
                                            · <span class="text-success fw-bold">{{ $details['upcoming_events'] }}</span> upcoming events
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- ═══════════════ ROW 2: QUICK METRICS BAR ═══════════════ --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                @php
                    $quickMetrics = [
                        ['label' => 'Chats', 'value' => $details['total_chats'], 'icon' => 'fa-comments', 'color' => '#009EF7', 'route' => route('admin.chat.index')],
                        ['label' => 'Messages', 'value' => $details['total_messages'], 'icon' => 'fa-envelope', 'color' => '#7239EA', 'route' => '#'],
                        ['label' => 'Stories', 'value' => $details['active_statuses'], 'icon' => 'fa-circle-play', 'color' => '#F1416C', 'route' => route('admin.status.list', ['filter' => 'active'])],
                        ['label' => 'Galleries', 'value' => $details['total_galleries'], 'icon' => 'fa-images', 'color' => '#FFC700', 'route' => route('admin.gallery.list')],
                        ['label' => 'Friends', 'value' => $details['accepted_friends'], 'icon' => 'fa-user-group', 'color' => '#50CD89', 'route' => '#'],
                        ['label' => 'Blogs', 'value' => $details['total_blogs'], 'icon' => 'fa-newspaper', 'color' => '#E4A951', 'route' => route('admin.blog.list')],
                        ['label' => 'Banners', 'value' => $details['total_banners'], 'icon' => 'fa-image', 'color' => '#3699FF', 'route' => route('admin.banner.list')],
                        ['label' => 'Bug Reports', 'value' => $details['total_bugs'], 'icon' => 'fa-bug', 'color' => '#F64E60', 'route' => '#'],
                    ];
                @endphp
                @foreach ($quickMetrics as $qidx => $qm)
                    <div class="col-xl-3 col-md-4 col-6">
                        <a href="{{ $qm['route'] }}"
                            class="card card-flush shadow-xs border-0 py-4 px-5 metric-pill text-decoration-none animate-in"
                            style="animation-delay: {{ 0.4 + $qidx * 0.05 }}s">
                            <div class="d-flex align-items-center">
                                <div class="symbol symbol-40px me-3">
                                    <div class="symbol-label rounded-circle" style="background-color: {{ $qm['color'] }}15">
                                        <i class="fas {{ $qm['icon'] }} fs-5" style="color: {{ $qm['color'] }}"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    <span class="fs-4 fw-bold text-gray-800 counter-value" data-target="{{ $qm['value'] }}">0</span>
                                    <span class="fs-8 fw-semibold text-gray-500">{{ $qm['label'] }}</span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endforeach
            </div>

            {{-- ═══════════════ ROW 3: MAIN CHARTS ═══════════════ --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                {{-- 30-Day Growth Chart --}}
                <div class="col-xl-8">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 0.5s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Platform Growth — 30 Days</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">User registrations vs content creation
                                    trend</span>
                            </h3>
                            <div class="card-toolbar">
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-light-primary btn-sm active chart-range-btn"
                                        data-range="30d">30D</button>
                                    <button type="button" class="btn btn-light btn-sm chart-range-btn"
                                        data-range="12m">12M</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body pt-2 pb-4">
                            <div id="growth_area_chart" style="height: 380px;"></div>
                        </div>
                    </div>
                </div>

                {{-- User Distribution Radial --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 0.6s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">User Status</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Active vs Inactive ratio</span>
                            </h3>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center pt-0">
                            <div id="user_radial_chart" style="height: 230px; width: 100%;"></div>
                            <div class="w-100 mt-3">
                                <div class="d-flex justify-content-between align-items-center p-3 mb-2 rounded" style="background-color: #f1faff">
                                    <div class="d-flex align-items-center">
                                        <span class="bullet bullet-vertical h-30px bg-success me-3 rounded"></span>
                                        <div>
                                            <div class="fs-7 fw-bold text-gray-800">Active Users</div>
                                            <div class="fs-9 text-gray-500">Verified & operating</div>
                                        </div>
                                    </div>
                                    <span class="fs-4 fw-bold text-gray-900">{{ number_format($details['user_distribution']['active']) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center p-3 rounded" style="background-color: #fff5f8">
                                    <div class="d-flex align-items-center">
                                        <span class="bullet bullet-vertical h-30px bg-danger me-3 rounded"></span>
                                        <div>
                                            <div class="fs-7 fw-bold text-gray-800">Inactive Users</div>
                                            <div class="fs-9 text-gray-500">Blocked or pending</div>
                                        </div>
                                    </div>
                                    <span class="fs-4 fw-bold text-gray-900">{{ number_format($details['user_distribution']['inactive']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ ROW 4: ENGAGEMENT & DEMOGRAPHICS ═══════════════ --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                {{-- Engagement Breakdown (Stacked Bar) --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 0.7s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Engagement Breakdown</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Last 7 days activity split</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div id="engagement_stacked_chart" style="height: 300px;"></div>
                            <div class="d-flex flex-wrap gap-4 mt-4 justify-content-center">
                                <div class="text-center">
                                    <div class="fs-2 fw-bold text-gray-900">{{ number_format($details['total_comments']) }}</div>
                                    <div class="fs-8 text-gray-500"><i class="fas fa-comment text-primary me-1"></i>Comments</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-2 fw-bold text-gray-900">{{ number_format($details['total_loves']) }}</div>
                                    <div class="fs-8 text-gray-500"><i class="fas fa-heart text-danger me-1"></i>Loves</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-2 fw-bold text-gray-900">{{ number_format($details['total_emojis']) }}</div>
                                    <div class="fs-8 text-gray-500"><i class="fas fa-face-smile text-warning me-1"></i>Emojis</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-2 fw-bold text-gray-900">{{ number_format($details['total_stars']) }}</div>
                                    <div class="fs-8 text-gray-500"><i class="fas fa-star text-info me-1"></i>Stars</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Gender Distribution --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 0.8s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Gender Identity</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">User profile demographics</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column align-items-center">
                            <div id="gender_pie_chart" style="height: 300px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- Age Distribution (Horizontal Bar) --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 0.9s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Age Distribution</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">User age group breakdown</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div id="age_bar_chart" style="height: 300px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ ROW 5: ORIENTATION + KYC + LOGIN ACTIVITY ═══════════════ --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                {{-- Orientation Breakdown --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 1.0s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Sexual Orientation</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Identity distribution</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0 d-flex flex-column align-items-center">
                            <div id="orientation_chart" style="height: 320px; width: 100%;"></div>
                        </div>
                    </div>
                </div>

                {{-- KYC Verification Status --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 1.1s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">KYC Verification</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Identity verification pipeline</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div id="kyc_radial_chart" style="height: 230px; width: 100%;"></div>
                            <div class="d-flex flex-column gap-3 mt-3">
                                <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded" style="background: #e8fff3">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check-circle text-success fs-4 me-3"></i>
                                        <span class="fs-7 fw-semibold text-gray-700">Approved</span>
                                    </div>
                                    <span class="fs-5 fw-bold text-success">{{ $details['kyc_approved'] }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded" style="background: #fff8dd">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-warning fs-4 me-3"></i>
                                        <span class="fs-7 fw-semibold text-gray-700">Pending Review</span>
                                    </div>
                                    <span class="fs-5 fw-bold text-warning">{{ $details['kyc_pending'] }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between px-3 py-2 rounded" style="background: #fff5f8">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-times-circle text-danger fs-4 me-3"></i>
                                        <span class="fs-7 fw-semibold text-gray-700">Rejected</span>
                                    </div>
                                    <span class="fs-5 fw-bold text-danger">{{ $details['kyc_rejected'] }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Login Activity --}}
                <div class="col-xl-4">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 1.2s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Login Activity</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">7-day login frequency</span>
                            </h3>
                        </div>
                        <div class="card-body pt-0">
                            <div id="login_activity_chart" style="height: 300px;"></div>
                            <div class="d-flex justify-content-between mt-3 px-2">
                                <div class="text-center">
                                    <div class="fs-3 fw-bold text-gray-900">{{ number_format($details['total_friend_requests']) }}</div>
                                    <div class="fs-8 text-gray-500">Friend Requests</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-3 fw-bold text-gray-900">{{ number_format($details['pending_friend_requests']) }}</div>
                                    <div class="fs-8 text-gray-500">Pending</div>
                                </div>
                                <div class="text-center">
                                    <div class="fs-3 fw-bold text-gray-900">{{ number_format($details['accepted_friends']) }}</div>
                                    <div class="fs-8 text-gray-500">Connected</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ═══════════════ ROW 6: TOP USERS + RECENT ACTIVITY ═══════════════ --}}
            <div class="row g-5 g-xl-8 mb-5 mb-xl-8">
                {{-- Top Active Users --}}
                <div class="col-xl-6">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 1.3s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Top Content Creators</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">Most active users by post count</span>
                            </h3>
                        </div>
                        <div class="card-body pt-3">
                            @foreach ($details['top_active_users'] as $rank => $user)
                                <div class="d-flex align-items-center mb-5 top-user-row" style="animation-delay: {{ 1.4 + $rank * 0.08 }}s">
                                    <div class="position-relative me-4">
                                        @if ($rank < 3)
                                            <div class="rank-badge rank-{{ $rank + 1 }}">
                                                @if ($rank == 0) 🥇 @elseif($rank == 1) 🥈 @else 🥉 @endif
                                            </div>
                                        @else
                                            <div class="rank-badge rank-other">{{ $rank + 1 }}</div>
                                        @endif
                                    </div>
                                    <div class="symbol symbol-45px symbol-circle me-4">
                                        @if ($user->profile_image && file_exists(storage_path('app/public/profile/' . $user->profile_image)))
                                            <img src="{{ asset('storage/profile/' . $user->profile_image) }}" alt="">
                                        @else
                                            <img src="{{ asset('assets/media/avatars/blank.png') }}" alt="">
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <a href="{{ route('admin.user.view', $user->uuid) }}"
                                            class="text-gray-900 fw-bold text-hover-primary fs-6 d-block">{{ $user->name }}</a>
                                        <span class="text-gray-500 fw-semibold fs-7">{{ $user->email }}</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="progress w-80px h-6px me-3 bg-gray-200 rounded-pill">
                                            @php
                                                $maxPosts = $details['top_active_users']->max('posts_count') ?: 1;
                                                $pct = ($user->posts_count / $maxPosts) * 100;
                                            @endphp
                                            <div class="progress-bar rounded-pill" role="progressbar"
                                                style="width: {{ $pct }}%; background: linear-gradient(90deg, #667eea, #764ba2)"></div>
                                        </div>
                                        <span class="badge badge-light-primary fw-bold fs-7">{{ $user->posts_count }} posts</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Recent Users — Card List --}}
                <div class="col-xl-6">
                    <div class="card card-flush h-xl-100 shadow-sm border-0 animate-in" style="animation-delay: 1.4s">
                        <div class="card-header pt-7 border-0">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Latest Registrations</span>
                                <span class="text-gray-400 mt-1 fw-semibold fs-6">New members who joined recently</span>
                            </h3>
                            <div class="card-toolbar">
                                <a href="{{ route('admin.user.list') }}" class="btn btn-sm btn-light-primary fw-bold">
                                    View All <i class="fas fa-arrow-right ms-1 fs-9"></i></a>
                            </div>
                        </div>
                        <div class="card-body pt-4">
                            @forelse($details['recent_users'] as $uidx => $user)
                                <div class="d-flex align-items-center px-4 py-4 mb-3 rounded-3 registration-row"
                                     style="background: {{ $uidx % 2 == 0 ? '#f9fafb' : '#ffffff' }}; animation-delay: {{ 1.5 + $uidx * 0.08 }}s">
                                    {{-- Avatar with status dot --}}
                                    <div class="position-relative me-4">
                                        <div class="symbol symbol-50px symbol-circle">
                                            <img src="{{ $user->image_path }}" alt="{{ $user->name }}" />
                                        </div>
                                        <div class="position-absolute rounded-circle border border-2 border-white"
                                             style="width: 14px; height: 14px; bottom: 0; right: 0;
                                                    background: {{ $user->is_active ? '#50CD89' : '#F1416C' }};">
                                        </div>
                                    </div>

                                    {{-- Info --}}
                                    <div class="flex-grow-1 me-3">
                                        <a href="{{ route('admin.user.view', $user->uuid) }}"
                                           class="text-gray-900 fw-bold text-hover-primary fs-6 d-block mb-1">{{ $user->name }}</a>
                                        <span class="text-gray-500 fw-semibold fs-7">{{ $user->email }}</span>
                                    </div>

                                    {{-- Meta --}}
                                    <div class="d-flex flex-column align-items-end gap-2">
                                        <span class="badge badge-light fs-9 fw-semibold text-gray-600 px-3 py-1">
                                            <i class="fas fa-clock me-1 text-gray-400" style="font-size: 9px"></i>
                                            {{ $user->created_at->diffForHumans() }}
                                        </span>
                                        <a href="{{ route('admin.user.view', $user->uuid) }}"
                                           class="btn btn-sm btn-icon btn-light-primary rounded-circle"
                                           style="width: 30px; height: 30px;" title="View Profile">
                                            <i class="fas fa-arrow-right" style="font-size: 11px"></i>
                                        </a>
                                    </div>
                                </div>
                            @empty
                                <div class="d-flex flex-column align-items-center justify-content-center py-15 text-center">
                                    <i class="fas fa-user-plus fs-2x text-gray-300 mb-4"></i>
                                    <span class="text-gray-500 fw-semibold fs-6">No recent registrations found.</span>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    @push('script')
        <script>
            $(document).ready(function() {
                function initDashboardCharts() {
                    if (typeof ApexCharts === 'undefined') {
                        setTimeout(initDashboardCharts, 200);
                        return;
                    }
                    renderAllCharts();
                }

                function renderAllCharts() {

                // ── Counter Animation ──
                document.querySelectorAll('.counter-value').forEach(el => {
                    const target = parseInt(el.dataset.target) || 0;
                    const duration = 1500;
                    const step = Math.ceil(target / (duration / 16));
                    let current = 0;
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            current = target;
                            clearInterval(timer);
                        }
                        el.textContent = current.toLocaleString();
                    }, 16);
                });

                // ── Helper: Gradient-fill config ──
                function gradientFill(id, color1, color2) {
                    return {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [0, 100]
                        }
                    };
                }

                // ── Sparkline Mini Charts ──
                @foreach ($heroStats as $idx => $stat)
                    new ApexCharts(document.querySelector("#spark_{{ $idx }}"), {
                        series: [{
                            data: @json($stat['sparkData'])
                        }],
                        chart: {
                            type: 'area',
                            height: 45,
                            width: 100,
                            sparkline: {
                                enabled: true
                            }
                        },
                        stroke: {
                            width: 2,
                            curve: 'smooth'
                        },
                        colors: ['{{ $idx == 0 ? "#667eea" : ($idx == 1 ? "#f5576c" : ($idx == 2 ? "#4facfe" : "#43e97b")) }}'],
                        fill: gradientFill(),
                        tooltip: {
                            enabled: false
                        }
                    }).render();
                @endforeach

                // ══ 30-Day Growth Area Chart ══
                var growthChart30d = @json($details['trend_30d_labels']);
                var growthUsers30d = @json($details['trend_30d_users']);
                var growthPosts30d = @json($details['trend_30d_posts']);
                var monthlyLabels = @json($details['monthly_labels']);
                var monthlyUsers = @json($details['monthly_users']);

                var growthChartEl = document.querySelector("#growth_area_chart");
                var growthOptions = {
                    series: [{
                        name: 'New Users',
                        type: 'area',
                        data: growthUsers30d
                    }, {
                        name: 'New Posts',
                        type: 'column',
                        data: growthPosts30d
                    }],
                    chart: {
                        height: 380,
                        type: 'line',
                        stacked: false,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1200,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 350
                            }
                        },
                        dropShadow: {
                            enabled: true,
                            color: '#667eea',
                            top: 12,
                            left: 1,
                            blur: 4,
                            opacity: 0.12
                        }
                    },
                    stroke: {
                        width: [3, 0],
                        curve: 'smooth'
                    },
                    plotOptions: {
                        bar: {
                            columnWidth: '35%',
                            borderRadius: 5,
                            borderRadiusApplication: 'end'
                        }
                    },
                    colors: ['#667eea', '#50CD89'],
                    fill: {
                        opacity: [0.3, 0.9],
                        gradient: {
                            inverseColors: false,
                            shade: 'light',
                            type: "vertical",
                            opacityFrom: 0.85,
                            opacityTo: 0.15,
                            stops: [0, 100]
                        }
                    },
                    labels: growthChart30d,
                    markers: {
                        size: [4, 0],
                        strokeWidth: 2,
                        hover: {
                            size: 6
                        }
                    },
                    xaxis: {
                        type: 'category',
                        labels: {
                            style: {
                                colors: '#A1A5B7',
                                fontSize: '11px'
                            },
                            rotate: -45,
                            rotateAlways: true,
                            show: true,
                            hideOverlappingLabels: true,
                            maxHeight: 60
                        },
                        tickAmount: 10
                    },
                    yaxis: [{
                        title: {
                            text: 'Users',
                            style: {
                                color: '#667eea',
                                fontWeight: 600
                            }
                        },
                        labels: {
                            style: {
                                colors: '#A1A5B7'
                            }
                        }
                    }, {
                        opposite: true,
                        title: {
                            text: 'Posts',
                            style: {
                                color: '#50CD89',
                                fontWeight: 600
                            }
                        },
                        labels: {
                            style: {
                                colors: '#A1A5B7'
                            }
                        }
                    }],
                    tooltip: {
                        shared: true,
                        intersect: false,
                        theme: 'dark',
                        y: {
                            formatter: function(y) {
                                return typeof y !== "undefined" ? y.toFixed(0) : y;
                            }
                        }
                    },
                    grid: {
                        borderColor: '#F1F1F1',
                        strokeDashArray: 4,
                        padding: {
                            left: 10,
                            right: 10
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '13px',
                        fontWeight: 600,
                        markers: {
                            width: 12,
                            height: 12,
                            radius: 4
                        }
                    }
                };
                var growthChart = new ApexCharts(growthChartEl, growthOptions);
                growthChart.render();

                // Chart range toggle
                document.querySelectorAll('.chart-range-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        document.querySelectorAll('.chart-range-btn').forEach(b => {
                            b.classList.remove('active', 'btn-light-primary');
                            b.classList.add('btn-light');
                        });
                        this.classList.add('active', 'btn-light-primary');
                        this.classList.remove('btn-light');

                        if (this.dataset.range === '12m') {
                            growthChart.updateOptions({
                                series: [{
                                    name: 'Monthly Registrations',
                                    type: 'area',
                                    data: monthlyUsers
                                }],
                                labels: monthlyLabels,
                                xaxis: {
                                    tickAmount: 12
                                }
                            });
                        } else {
                            growthChart.updateOptions({
                                series: [{
                                    name: 'New Users',
                                    type: 'area',
                                    data: growthUsers30d
                                }, {
                                    name: 'New Posts',
                                    type: 'column',
                                    data: growthPosts30d
                                }],
                                labels: growthChart30d,
                                xaxis: {
                                    tickAmount: 10
                                }
                            });
                        }
                    });
                });

                // ══ User Radial Chart ══
                var totalActive = {{ $details['user_distribution']['active'] }};
                var totalInactive = {{ $details['user_distribution']['inactive'] }};
                var totalAll = totalActive + totalInactive;
                new ApexCharts(document.querySelector("#user_radial_chart"), {
                    series: [totalAll > 0 ? Math.round((totalActive / totalAll) * 100) : 0],
                    chart: {
                        height: 230,
                        type: 'radialBar',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1500
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            startAngle: -135,
                            endAngle: 135,
                            hollow: {
                                size: '65%',
                            },
                            track: {
                                background: '#f1f1f4',
                                strokeWidth: '100%',
                                margin: 5,
                                dropShadow: {
                                    enabled: true,
                                    top: 2,
                                    left: 0,
                                    blur: 4,
                                    opacity: 0.06
                                }
                            },
                            dataLabels: {
                                name: {
                                    fontSize: '13px',
                                    color: '#A1A5B7',
                                    offsetY: -10
                                },
                                value: {
                                    offsetY: 5,
                                    fontSize: '28px',
                                    fontWeight: '700',
                                    color: '#181C32',
                                    formatter: function() {
                                        return totalAll.toLocaleString();
                                    }
                                }
                            }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            gradientToColors: ['#50CD89'],
                            stops: [0, 100]
                        }
                    },
                    stroke: {
                        lineCap: 'round'
                    },
                    labels: ['Active Rate'],
                    colors: ['#667eea']
                }).render();

                // ══ Engagement Stacked Bar ══
                new ApexCharts(document.querySelector("#engagement_stacked_chart"), {
                    series: [{
                        name: 'Comments',
                        data: @json($details['engagement_comments'])
                    }, {
                        name: 'Loves',
                        data: @json($details['engagement_loves'])
                    }, {
                        name: 'Emojis',
                        data: @json($details['engagement_emojis'])
                    }],
                    chart: {
                        type: 'bar',
                        height: 300,
                        stacked: true,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1000
                        }
                    },
                    plotOptions: {
                        bar: {
                            borderRadius: 5,
                            borderRadiusApplication: 'end',
                            borderRadiusWhenStacked: 'last',
                            columnWidth: '50%'
                        }
                    },
                    colors: ['#009EF7', '#F1416C', '#FFC700'],
                    xaxis: {
                        categories: @json($details['registration_labels']),
                        labels: {
                            style: {
                                colors: '#A1A5B7'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#A1A5B7'
                            }
                        }
                    },
                    grid: {
                        borderColor: '#F1F1F1',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'dark'
                    },
                    legend: {
                        position: 'top',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                }).render();

                // ══ Gender Pie Chart ══
                var genderLabels = @json($details['gender_labels']);
                var genderCounts = @json($details['gender_counts']);
                var genderColors = ['#667eea', '#f5576c', '#43e97b', '#FFC700', '#7239EA', '#009EF7', '#F1416C', '#50CD89', '#E4A951', '#3699FF'];
                if (genderLabels.length > 0) {
                    new ApexCharts(document.querySelector("#gender_pie_chart"), {
                        series: genderCounts,
                        chart: {
                            type: 'donut',
                            height: 300,
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 1200
                            }
                        },
                        labels: genderLabels,
                        colors: genderColors.slice(0, genderLabels.length),
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '60%',
                                    labels: {
                                        show: true,
                                        total: {
                                            show: true,
                                            label: 'Total',
                                            fontSize: '14px',
                                            fontWeight: 600,
                                            color: '#A1A5B7'
                                        }
                                    }
                                }
                            }
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: '12px',
                            fontWeight: 500,
                            markers: {
                                width: 10,
                                height: 10,
                                radius: 3
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            dropShadow: {
                                enabled: false
                            },
                            style: {
                                fontSize: '11px',
                                fontWeight: 600
                            }
                        },
                        tooltip: {
                            theme: 'dark'
                        }
                    }).render();
                } else {
                    document.querySelector("#gender_pie_chart").innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="fas fa-chart-pie fs-2x mb-3"></i>No gender data available</div>';
                }

                // ══ Age Bar Chart ══
                var ageLabels = @json($details['age_labels']);
                var ageCounts = @json($details['age_counts']);
                if (ageLabels.length > 0) {
                    new ApexCharts(document.querySelector("#age_bar_chart"), {
                        series: [{
                            name: 'Users',
                            data: ageCounts
                        }],
                        chart: {
                            type: 'bar',
                            height: 300,
                            toolbar: {
                                show: false
                            },
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 1000
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: true,
                                borderRadius: 6,
                                barHeight: '60%',
                                distributed: true
                            }
                        },
                        colors: ['#667eea', '#764ba2', '#f093fb', '#f5576c', '#4facfe', '#43e97b'],
                        xaxis: {
                            categories: ageLabels,
                            labels: {
                                style: {
                                    colors: '#A1A5B7'
                                }
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    colors: '#A1A5B7',
                                    fontSize: '12px',
                                    fontWeight: 600
                                }
                            }
                        },
                        grid: {
                            borderColor: '#F1F1F1',
                            strokeDashArray: 4
                        },
                        tooltip: {
                            theme: 'dark'
                        },
                        legend: {
                            show: false
                        },
                        dataLabels: {
                            enabled: true,
                            style: {
                                fontSize: '12px',
                                fontWeight: 700
                            }
                        }
                    }).render();
                } else {
                    document.querySelector("#age_bar_chart").innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="fas fa-chart-bar fs-2x mb-3"></i>No age data available</div>';
                }

                // ══ Orientation Chart ══
                var orientationLabels = @json($details['orientation_labels']);
                var orientationCounts = @json($details['orientation_counts']);
                var orientColors = ['#764ba2', '#667eea', '#f5576c', '#43e97b', '#FFC700', '#4facfe', '#F1416C', '#50CD89'];
                if (orientationLabels.length > 0) {
                    new ApexCharts(document.querySelector("#orientation_chart"), {
                        series: orientationCounts,
                        chart: {
                            type: 'polarArea',
                            height: 320,
                            animations: {
                                enabled: true,
                                easing: 'easeinout',
                                speed: 1200
                            }
                        },
                        labels: orientationLabels,
                        colors: orientColors.slice(0, orientationLabels.length),
                        stroke: {
                            colors: ['#fff'],
                            width: 2
                        },
                        fill: {
                            opacity: 0.85
                        },
                        legend: {
                            position: 'bottom',
                            fontSize: '12px',
                            fontWeight: 500
                        },
                        tooltip: {
                            theme: 'dark'
                        },
                        plotOptions: {
                            polarArea: {
                                rings: {
                                    strokeWidth: 1,
                                    strokeColor: '#f1f1f4'
                                },
                                spokes: {
                                    strokeWidth: 1,
                                    connectorColors: '#f1f1f4'
                                }
                            }
                        }
                    }).render();
                } else {
                    document.querySelector("#orientation_chart").innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100 text-muted"><i class="fas fa-chart-pie fs-2x mb-3"></i>No orientation data available</div>';
                }

                // ══ KYC Radial Chart ══
                var kycTotal = {{ $details['kyc_approved'] + $details['kyc_pending'] + $details['kyc_rejected'] }};
                new ApexCharts(document.querySelector("#kyc_radial_chart"), {
                    series: [
                        kycTotal > 0 ? Math.round(({{ $details['kyc_approved'] }} / kycTotal) * 100) : 0,
                        kycTotal > 0 ? Math.round(({{ $details['kyc_pending'] }} / kycTotal) * 100) : 0,
                        kycTotal > 0 ? Math.round(({{ $details['kyc_rejected'] }} / kycTotal) * 100) : 0,
                    ],
                    chart: {
                        height: 230,
                        type: 'radialBar',
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1200
                        }
                    },
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                size: '35%'
                            },
                            track: {
                                strokeWidth: '100%',
                                background: '#f1f1f4',
                                margin: 3
                            },
                            dataLabels: {
                                name: {
                                    fontSize: '11px',
                                    offsetY: -5
                                },
                                value: {
                                    fontSize: '14px',
                                    fontWeight: 700,
                                    offsetY: 2,
                                    formatter: function(val) {
                                        return val + '%';
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total KYC',
                                    fontSize: '11px',
                                    color: '#A1A5B7',
                                    formatter: function() {
                                        return kycTotal;
                                    }
                                }
                            }
                        }
                    },
                    labels: ['Approved', 'Pending', 'Rejected'],
                    colors: ['#50CD89', '#FFC700', '#F1416C'],
                    stroke: {
                        lineCap: 'round'
                    }
                }).render();

                // ══ Login Activity Chart ══
                new ApexCharts(document.querySelector("#login_activity_chart"), {
                    series: [{
                        name: 'Logins',
                        data: @json($details['login_activity'])
                    }],
                    chart: {
                        type: 'area',
                        height: 300,
                        toolbar: {
                            show: false
                        },
                        animations: {
                            enabled: true,
                            easing: 'easeinout',
                            speed: 1200
                        },
                        dropShadow: {
                            enabled: true,
                            color: '#4facfe',
                            top: 10,
                            left: 0,
                            blur: 6,
                            opacity: 0.15
                        }
                    },
                    stroke: {
                        width: 3,
                        curve: 'smooth'
                    },
                    colors: ['#4facfe'],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.05,
                            stops: [0, 100]
                        }
                    },
                    xaxis: {
                        categories: @json($details['registration_labels']),
                        labels: {
                            style: {
                                colors: '#A1A5B7'
                            }
                        }
                    },
                    yaxis: {
                        labels: {
                            style: {
                                colors: '#A1A5B7'
                            }
                        }
                    },
                    markers: {
                        size: 4,
                        strokeWidth: 2,
                        hover: {
                            size: 7
                        }
                    },
                    grid: {
                        borderColor: '#F1F1F1',
                        strokeDashArray: 4
                    },
                    tooltip: {
                        theme: 'dark'
                    }
                }).render();
                } // end renderAllCharts

                initDashboardCharts();
            });
        </script>

        <style>
            /* ── Entrance Animations ── */
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(25px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            @keyframes slideInLeft {
                from {
                    opacity: 0;
                    transform: translateX(-20px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.05); }
            }

            .animate-in {
                opacity: 0;
                animation: fadeInUp 0.6s ease-out forwards;
            }

            .top-user-row {
                opacity: 0;
                animation: slideInLeft 0.5s ease-out forwards;
            }

            .registration-row {
                opacity: 0;
                animation: slideInLeft 0.5s ease-out forwards;
                transition: all 0.25s ease;
                border: 1px solid transparent;
            }

            .registration-row:hover {
                background: #f1f5ff !important;
                border-color: #e0e7ff;
                transform: translateX(4px);
            }

            /* ── KPI Cards ── */
            .kpi-card {
                border-radius: 16px !important;
                transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
                overflow: hidden;
                position: relative;
            }

            .kpi-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 4px;
                opacity: 0;
                transition: opacity 0.35s ease;
            }

            .kpi-card:nth-child(1) .kpi-card::before { background: linear-gradient(90deg, #667eea, #764ba2); }
            .kpi-card:nth-child(2) .kpi-card::before { background: linear-gradient(90deg, #f093fb, #f5576c); }

            .kpi-card:hover {
                transform: translateY(-6px);
                box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12) !important;
            }

            .kpi-card:hover::before {
                opacity: 1;
            }

            .kpi-icon-wrap {
                width: 48px;
                height: 48px;
                border-radius: 14px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: transform 0.3s ease;
            }

            .kpi-card:hover .kpi-icon-wrap {
                transform: rotate(-5deg) scale(1.1);
            }

            /* ── Metric Pills ── */
            .metric-pill {
                border-radius: 14px !important;
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .metric-pill:hover {
                transform: translateY(-3px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08) !important;
            }

            /* ── Rank Badges ── */
            .rank-badge {
                width: 32px;
                height: 32px;
                border-radius: 10px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 16px;
                font-weight: 700;
            }

            .rank-badge.rank-1 { background: linear-gradient(135deg, #FFD700, #FFA500); }
            .rank-badge.rank-2 { background: linear-gradient(135deg, #C0C0C0, #A8A8A8); }
            .rank-badge.rank-3 { background: linear-gradient(135deg, #CD7F32, #B87333); }
            .rank-badge.rank-other {
                background: #f1f1f4;
                color: #A1A5B7;
                font-size: 13px;
            }

            /* ── Cards ── */
            .card {
                border-radius: 16px !important;
                transition: box-shadow 0.3s ease;
            }

            .card:hover {
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08) !important;
            }

            /* ── Progress bars ── */
            .progress {
                overflow: hidden;
            }

            .progress-bar {
                transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            }

            /* ── Scrollbar ── */
            ::-webkit-scrollbar {
                width: 6px;
            }
            ::-webkit-scrollbar-track {
                background: transparent;
            }
            ::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 3px;
            }

            /* ── Responsive ── */
            @media (max-width: 768px) {
                .kpi-icon-wrap {
                    width: 40px;
                    height: 40px;
                }
                .sparkline-mini {
                    display: none;
                }
            }
        </style>
    @endpush
@endsection
