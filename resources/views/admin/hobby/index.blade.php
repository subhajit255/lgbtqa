@extends('layout.app')
@section('content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div class="d-flex flex-column flex-column-fluid">
            <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
                <div id="kt_app_toolbar_container" class="app-container container-xxl d-flex flex-stack">
                    <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                        <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">
                            Hobbies, Vibes & Lifestyle List</h1>
                        <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                            <li class="breadcrumb-item text-muted">
                                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <span class="bullet bg-gray-400 w-5px h-2px"></span>
                            </li>
                            <li class="breadcrumb-item text-muted">Hobbies, Vibes & Lifestyle</li>
                        </ul>
                    </div>
                    <div class="d-flex align-items-center gap-2 gap-lg-3">
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-dark goTo"
                                data-action="{{ route('admin.hobby.add') }}">Add New</button>
                        </div>
                    </div>
                </div>
            </div>
            <div id="kt_app_content" class="app-content flex-column-fluid">
                <div id="kt_app_content_container" class="app-container container-xxl">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
                        @forelse ($details as $detail)
                            <div class="col">
                                <div class="card card-flush h-md-100 glass-card">
                                    <div class="card-header">
                                        <div class="card-title d-flex flex-column align-items-start">
                                            <h2 class="mb-1">{{ $detail->title ?? 'N/A' }}</h2>
                                            @if($detail->type == 2)
                                                <span class="badge badge-light-success fs-8 fw-bold">Lifestyle</span>
                                            @elseif($detail->type == 3)
                                                <span class="badge badge-light-warning fs-8 fw-bold">Home & Future</span>
                                            @elseif($detail->type == 4)
                                                <span class="badge badge-light-info fs-8 fw-bold">Your Vibe</span>
                                            @else
                                                <span class="badge badge-light-primary fs-8 fw-bold">Hobby / Interest</span>
                                            @endif
                                        </div>
                                        <div class="card-toolbar">
                                            <div class="form-check form-switch">
                                                <input type="checkbox" data-uuid="{{ $detail->uuid }}" data-table="hobbies"
                                                    class="form-check-input isVerified" id="status_{{ $detail->id }}"
                                                    value="{{ $detail->is_active ?? 0 }}"
                                                    {{ $detail->is_active == 1 ? 'checked' : '' }}>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body pt-1">
                                        <div class="fw-bold text-gray-600 mb-5">Total Items:
                                            {{ $detail->items->count() }}
                                        </div>
                                        <div class="d-flex flex-column text-gray-600">
                                            @forelse ($detail->items->take(5) as $item)
                                                <div class="d-flex align-items-center py-2">
                                                    <span class="bullet bg-primary me-3"></span>
                                                    <span
                                                        class="{{ $item->is_active == 0 ? 'text-decoration-line-through text-muted' : '' }}">
                                                        {{ $item->name }}
                                                    </span>
                                                </div>
                                            @empty
                                                <div class="text-muted italic">No items added</div>
                                            @endforelse
                                            @if ($detail->items->count() > 5)
                                                <div class='d-flex align-items-center py-2 text-primary fw-bold'>
                                                    <span class='bullet bg-primary me-3'></span>
                                                    <em>+{{ $detail->items->count() - 5 }} more items</em>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="card-footer d-flex flex-stack pt-0">
                                        <div class="text-muted fs-7">
                                            {{ $detail->is_active == 1 ? 'Active' : 'Inactive' }}
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('admin.hobby.add', $detail->uuid) }}"
                                                class="btn btn-sm btn-light-primary">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                            <a href="javascript:void(0)" data-table="hobbies"
                                                data-uuid="{{ $detail->uuid }}"
                                                class="btn btn-sm btn-light-danger deleteData">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-md-12">
                                <div class="card glass-card p-10 text-center">
                                    <h2 class="text-gray-600">No Hobby Found</h2>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
