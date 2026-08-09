<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar"
    data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="225px"
    data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
    <div class="app-sidebar-logo px-6 sideHead" id="kt_app_sidebar_logo">
        <a href="javascript:void(0)">
            <h3 class="app-sidebar-logo-default" style="font-size: 37px; font-weight: bold; color: #fff;">
                <img src="{{ asset('assets/media/logos/favicon.png') }}" alt="Logo" width="40px;"
                    style="margin-top: -4px;">
                LGBTQIA
            </h3>
            <p class="app-sidebar-logo-minimize" style="font-size: 50px; font-weight: bold; color: #fff;">
                <img src="{{ asset('assets/media/logos/favicon.png') }}" alt="Logo" width="40px;">
            </p>
        </a>
        <div id="kt_app_sidebar_toggle"
            class="app-sidebar-toggle btn btn-icon btn-shadow btn-sm btn-color-muted btn-active-color-primary body-bg h-30px w-30px position-absolute top-50 start-100 translate-middle rotate"
            data-kt-toggle="true" data-kt-toggle-state="active" data-kt-toggle-target="body"
            data-kt-toggle-name="app-sidebar-minimize">
            <span class="svg-icon svg-icon-2 rotate-180">
                <i class="fa-sharp fa-solid fa-arrow-right-arrow-left"></i>
            </span>
        </div>
    </div>
    <div class="app-sidebar-menu overflow-hidden flex-column-fluid">
        <div id="kt_app_sidebar_menu_wrapper" class="app-sidebar-wrapper hover-scroll-overlay-y mb-5"
            data-kt-scroll="true" data-kt-scroll-activate="true" data-kt-scroll-height="auto"
            data-kt-scroll-dependencies="#kt_app_sidebar_logo, #kt_app_sidebar_footer"
            data-kt-scroll-wrappers="#kt_app_sidebar_menu" data-kt-scroll-offset="5px" data-kt-scroll-save-state="true">
            <div class="menu menu-column menu-rounded menu-sub-indention px-3" id="#kt_app_sidebar_menu"
                data-kt-menu="true" data-kt-menu-expand="false">
                {{-- <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-7">Dashboard</span>
                    </div>
                </div> --}}

                <div class="leftbar-user">
                    <div class="menu-item p-0">
                        <div class="menu-content d-flex align-items-center p-0">
                            <div class="symbol symbol-50px me-3">
                                <img src="{{ auth()->user()->image_path }}" alt="User Image" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover;">
                            </div>
                            <div class="d-flex flex-column" style="min-width: 0; flex: 1;">
                                <div class="fw-bold d-flex align-items-center fs-6 text-white text-truncate">
                                    @if (auth()->user()->name)
                                        {{ auth()->user()->name }}
                                    @else
                                        Super Admin
                                    @endif
                                </div>
                                @if (auth()->user()->email != null)
                                    <a href="javascript:void(0)" title="{{ auth()->user()->email }}"
                                        class="fw-semibold text-muted text-hover-primary fs-7 text-truncate" style="max-width: 120px; display: inline-block;">{{ Str::limit(auth()->user()->email, 18, '...') }}</a>
                                @else
                                    <a href="javascript:void(0)"
                                        class="fw-semibold text-muted text-hover-primary fs-7 text-truncate">No Email</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- OVERVIEW HEADER -->
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Overview</span>
                    </div>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.dashboard') }}">
                        <span class="menu-link {{ sidebarActive(['admin.dashboard']) }}">
                            <span class="menu-icon">
                                <span class="menu-icon">
                                    <span class="svg-icon svg-icon-2">
                                        <i class="fa fa-id-card-o" style="color: #38bbee;"></i>
                                    </span>
                                </span>
                            </span>
                            <span class="menu-title">Dashboard</span>
                        </span>
                    </a>
                </div>

                <!-- CORE MANAGEMENT HEADER -->
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Core Management</span>
                    </div>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.user.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.user.*']) }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa fa-user-circle-o" style="color: #b66dff;"></i>
                                </span>
                            </span>
                            <span class="menu-title">Users</span>
                        </span>
                    </a>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.kyc.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.kyc.*']) }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa fa-id-badge" style="color: #ffc107;"></i>
                                </span>
                            </span>
                            <span class="menu-title">KYC Verifications</span>
                        </span>
                    </a>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.bug.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.bug.*']) }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa fa-bug" style="color: #f44336;"></i>
                                </span>
                            </span>
                            <span class="menu-title">Bug Reports</span>
                        </span>
                    </a>
                </div>

                <!-- ENGAGEMENT & SOCIAL HEADER -->
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Engagement & Social</span>
                    </div>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.post.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.post.*']) }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa-solid fa-images" style="color: #4caf50;"></i>
                                </span>
                            </span>
                            <span class="menu-title">Post</span>
                        </span>
                    </a>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.status.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.status.*']) }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa-solid fa-circle-play" style="color: #e91e63;"></i>
                                </span>
                            </span>
                            <span class="menu-title">Statuses (Stories)</span>
                        </span>
                    </a>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.event.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.event.*']) }}">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa-solid fa-calendar-days" style="color: #ff5722;"></i>
                                </span>
                            </span>
                            <span class="menu-title">Events</span>
                        </span>
                    </a>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ sidebarOpen(['admin.community.*', 'admin.community-category.*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <span class="svg-icon svg-icon-2">
                                    <i class="fa-solid fa-users" style="color: #00bcd4;"></i>
                                </span>
                            </span>
                            <span class="menu-title">Communities</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion" style="">
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                                <div class="menu-item">
                                    <a href="{{ route('admin.community.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.community.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-list" style="color: #00bcd4;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">All Communities</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.community-category.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.community-category.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-tags" style="color: #4caf50;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Category Management</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SYSTEM CONFIG & MASTER HEADER -->
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">System & Master Data</span>
                    </div>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ sidebarOpen(['admin.category.*', 'admin.post-category.*', 'admin.banner.*', 'admin.blog.*', 'admin.cms.*', 'admin.hobby.*', 'admin.gallery.*', 'admin.groups.*', 'admin.badge-style.*', 'admin.badge-color.*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <span class="menu-icon">
                                    <span class="svg-icon svg-icon-2">
                                        <i class="fa-solid fa-recycle" style="color: #9c27b0;"></i>
                                    </span>
                                </span>
                            </span>
                            <span class="menu-title">Master</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion" style="">
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                                <div class="menu-item">
                                    <a href="{{ route('admin.post-category.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.post-category.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-layer-group" style="color: #f44336;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Post Categories</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.badge-style.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.badge-style.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-certificate" style="color: #ffeb3b;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Badge Style</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.badge-color.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.badge-color.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-palette" style="color: #009688;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Badge Color</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.cms.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.cms.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fas fa-tasks" style="color: #3f51b5;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">CMS</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.hobby.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.hobby.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa fa-heart" style="color: #ff5252;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Hobbies, Vibes & Lifestyle</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.gallery.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.gallery.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa fa-picture-o" style="color: #8bc34a;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Gallery Management</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.groups.index') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.groups.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-users-rectangle" style="color: #795548;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Group Management</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <div data-kt-menu-trigger="click"
                        class="menu-item menu-accordion {{ sidebarOpen(['admin.role.*']) }}">
                        <span class="menu-link">
                            <span class="menu-icon">
                                <span class="menu-icon">
                                    <span class="svg-icon svg-icon-2">
                                        <i class="fa-solid fa-paperclip" style="color: #607d8b;"></i>
                                    </span>
                                </span>
                            </span>
                            <span class="menu-title">Role & Permission</span>
                            <span class="menu-arrow"></span>
                        </span>
                        <div class="menu-sub menu-sub-accordion" style="">
                            <div data-kt-menu-trigger="click" class="menu-item menu-accordion mb-1">
                                <div class="menu-item">
                                    <a href="{{ route('admin.role.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.role.list']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-solid fa-key" style="color: #ff9800;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Role</span>
                                        </span>
                                    </a>
                                    <a href="{{ route('admin.role.user.list') }}">
                                        <span class="menu-link {{ sidebarActive(['admin.role.user.*']) }}">
                                            <span class="menu-icon">
                                                <span class="svg-icon svg-icon-2">
                                                    <i class="fa-regular fa-user" style="color: #00e676;"></i>
                                                </span>
                                            </span>
                                            <span class="menu-title">Admin Users</span>
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SETTINGS & MONITORING HEADER -->
                <div class="menu-item pt-5">
                    <div class="menu-content">
                        <span class="menu-heading fw-bold text-uppercase fs-8 text-muted">Settings & Monitoring</span>
                    </div>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.setting.update') }}">
                        <span class="menu-link {{ sidebarActive(['admin.setting.*']) }}">
                            <span class="menu-icon">
                                <span class="menu-icon">
                                    <span class="svg-icon svg-icon-2">
                                        <i class="fa-solid fa-gear" style="color: #03a9f4;"></i>
                                    </span>
                                </span>
                            </span>
                            <span class="menu-title">Setting</span>
                        </span>
                    </a>
                </div>

                <div data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="right-start"
                    class="menu-item menu-lg-down-accordion menu-sub-lg-down-indention">
                    <a href="{{ route('admin.log-error.list') }}">
                        <span class="menu-link {{ sidebarActive(['admin.log-error.*']) }}">
                            <span class="menu-icon">
                                <span class="menu-icon">
                                    <span class="svg-icon svg-icon-2">
                                        <i class="fa-solid fa-bug" style="color: #ff3d00;"></i>
                                    </span>
                                </span>
                            </span>
                            <span class="menu-title">Error Logs</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
