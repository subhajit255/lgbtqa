@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <!-- Toolbar -->
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Student Status Directory</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Status Management</li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Directory</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    
                    <!-- Navigation Tabs -->
                    <div class="d-flex flex-stack flex-wrap mb-7">
                        <ul class="nav nav-stretch nav-line-tabs nav-line-tabs-2x border-transparent fs-5 fw-bold">
                            <li class="nav-item">
                                <a class="nav-link text-active-primary py-4 me-10 {{ Route::currentRouteName() == 'admin.status.list' ? 'active' : '' }}" href="{{ route('admin.status.list') }}">Live Feed</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-active-primary py-4 {{ Route::currentRouteName() == 'admin.status.students' ? 'active' : '' }}" href="{{ route('admin.status.students') }}">Student Directory</a>
                            </li>
                        </ul>
                    </div>

                    <!-- Filter Bar -->
                    <div class="card mb-7 shadow-sm border-0">
                        <div class="card-body py-4 d-flex flex-stack flex-wrap gap-4">
                            <form action="{{ route('admin.status.students') }}" method="GET" class="d-flex align-items-center position-relative my-1 w-100">
                                <i class="bi bi-search position-absolute ms-3 fs-3"></i>
                                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-solid w-100 ps-12" placeholder="Search by Student Name or Email...">
                                <button type="submit" class="btn btn-primary btn-sm ms-2 px-6">Filter</button>
                                @if(request('search'))
                                    <a href="{{ route('admin.status.students') }}" class="btn btn-light-danger btn-sm ms-2">Clear</a>
                                @endif
                            </form>
                        </div>
                    </div>

                    <!-- Students Grid -->
                    <div class="row g-6 g-xl-9">
                        @forelse ($students as $student)
                            <div class="col-md-6 col-xxl-4">
                                <div class="card h-100 border-0 shadow-sm hover-elevate-up">
                                    <div class="card-body d-flex flex-center flex-column py-9 px-5">
                                        <!-- Avatar -->
                                        <div class="symbol symbol-65px symbol-circle mb-5">
                                            <img src="{{ $student->image_path }}" alt="image">
                                            @if($student->active_statuses_count > 0)
                                                <div class="bg-success position-absolute border border-4 border-white h-15px w-15px rounded-circle translate-middle start-100 top-100 ms-n3 mt-n3"></div>
                                            @endif
                                        </div>

                                        <!-- Name -->
                                        <a href="{{ route('admin.status.list', ['user_id' => $student->id]) }}" class="fs-4 text-gray-800 text-hover-primary fw-bold mb-0">
                                            {{ $student->name }}
                                        </a>
                                        <div class="fw-semibold text-gray-400 mb-6">{{ $student->email }}</div>

                                        <!-- Info Stats -->
                                        <div class="d-flex flex-center flex-wrap mb-5">
                                            <div class="border border-gray-300 border-dashed rounded min-w-80px py-3 px-4 mx-2 mb-3">
                                                <div class="fs-6 fw-bold text-gray-700 text-center">{{ $student->active_statuses_count }}</div>
                                                <div class="fw-semibold text-gray-400 text-center fs-8">Active Stories</div>
                                            </div>
                                            <div class="border border-gray-300 border-dashed rounded min-w-80px py-3 px-4 mx-2 mb-3">
                                                <div class="fs-6 fw-bold text-gray-700 text-center">{{ $student->total_statuses_count }}</div>
                                                <div class="fw-semibold text-gray-400 text-center fs-8">Total Statuses</div>
                                            </div>
                                        </div>

                                        <!-- Latest Activity Section (Fixed Height) -->
                                        <div class="bg-light rounded p-3 w-100 mb-5" style="height: 65px;">
                                            <div class="fs-8 text-muted mb-1 text-uppercase fw-bold">Latest Activity:</div>
                                            @if($student->statuses->count() > 0)
                                                <div class="d-flex align-items-center">
                                                    @php $latest = $student->statuses->first(); @endphp
                                                    <span class="badge badge-sm badge-light-{{ $latest->type == 'text' ? 'primary' : ($latest->type == 'image' ? 'success' : 'warning') }} me-2">
                                                        {{ $latest->type }}
                                                    </span>
                                                    <span class="fs-8 text-gray-600">{{ $latest->created_at->diffForHumans() }}</span>
                                                </div>
                                            @else
                                                <div class="fs-8 text-gray-400 mt-1 italic">No recent activity</div>
                                            @endif
                                        </div>

                                        <!-- Actions -->
                                        <div class="w-100 mt-auto">
                                            <a href="{{ route('admin.status.list', ['user_id' => $student->id]) }}" class="btn btn-sm btn-light-primary fw-bold w-100">
                                                View Full History
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-20">
                                <div class="mb-5">
                                    <i class="bi bi-person-x fs-5x text-gray-200"></i>
                                </div>
                                <h3 class="fs-2 fw-bold text-gray-900">No Students Found</h3>
                                <p class="text-gray-400 fs-5 fw-semibold">We couldn't find any students with status history matching your search.</p>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex flex-stack flex-wrap pt-10">
                        <div class="fs-6 fw-semibold text-gray-700">
                            Showing {{ $students->firstItem() }} to {{ $students->lastItem() }} of {{ $students->total() }} students
                        </div>
                        {{ $students->appends(request()->all())->links('pagination::bootstrap-5') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('style')
<style>
    .hover-elevate-up { transition: all 0.3s ease; }
    .hover-elevate-up:hover { transform: translateY(-8px); box-shadow: 0 0.5rem 1.5rem 0.5rem rgba(0, 0, 0, 0.075) !important; }
</style>
@endpush
