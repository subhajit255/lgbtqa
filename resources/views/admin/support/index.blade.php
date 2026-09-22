@extends('layout.app')
@section('content')
    <div id="kt_app_toolbar" class="app-toolbar py-3 py-lg-6">
        <div id="kt_app_toolbar_container" class="app-container container-fluid d-flex flex-stack">
            <div class="page-title d-flex flex-column justify-content-center flex-wrap me-3">
                <h1 class="page-heading d-flex text-dark fw-bold fs-3 flex-column justify-content-center my-0">Voluntary Support Transactions</h1>
                <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
                    <li class="breadcrumb-item text-muted"><a href="{{ route('admin.dashboard') }}" class="text-muted text-hover-primary">Home</a></li>
                    <li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li>
                    <li class="breadcrumb-item text-muted">Support Transactions</li>
                </ul>
            </div>
        </div>
    </div>

    <div id="kt_app_content" class="app-content flex-column-fluid">
        <div id="kt_app_content_container" class="app-container container-fluid">
            <div class="card card-flush shadow-sm">
                <div class="card-body pt-6">
                    <div class="table-responsive">
                        <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted">
                                    <th class="min-w-50px">#</th>
                                    <th class="min-w-150px">User</th>
                                    <th class="min-w-100px">Type</th>
                                    <th class="min-w-100px">Amount</th>
                                    <th class="min-w-150px">Stripe ID</th>
                                    <th class="min-w-100px">Status</th>
                                    <th class="min-w-120px text-end">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($details as $key => $item)
                                    <tr>
                                        <td>{{ $details->firstItem() + $key }}</td>
                                        <td>
                                            <a href="{{ route('admin.user.view', $item->user->uuid ?? '') }}" class="text-dark fw-bold text-hover-primary fs-6">{{ $item->user->name ?? 'Unknown User' }}</a>
                                        </td>
                                        <td><span class="badge badge-light-primary fs-8">{{ $item->type }}</span></td>
                                        <td><span class="text-gray-600 fw-bold">{{ $item->currency }} {{ $item->amount }}</span></td>
                                        <td>
                                            <span class="text-gray-500 fs-8">{{ $item->stripe_payment_intent_id ?? $item->stripe_subscription_id ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($item->status == 'ACTIVE')
                                                <span class="badge badge-light-success fs-8">ACTIVE</span>
                                            @elseif($item->status == 'PENDING')
                                                <span class="badge badge-light-warning fs-8">PENDING</span>
                                            @elseif($item->status == 'CANCELLED' || $item->status == 'EXPIRED')
                                                <span class="badge badge-light-danger fs-8">{{ $item->status }}</span>
                                            @else
                                                <span class="badge badge-light-secondary fs-8">{{ $item->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <span class="text-muted fs-8">{{ $item->created_at->format('Y-m-d H:i') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">No transactions found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($details->hasPages())
                        <div class="d-flex justify-content-center mt-5">
                            {{ $details->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
