@extends('layouts.app')

@section('title', 'Sales Management - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark mb-0">Sales Management</h3>
    <div>
        <a href="{{ route('sales.create') }}" class="btn btn-primary px-3">
            <i class="fa-solid fa-plus me-1"></i> Create Sale
        </a>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted fw-bold">Total Sales</small>
            <h4 class="fw-bold text-dark mb-0">{{ $stats['total_sales'] ?? count($sales) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted fw-bold">Grand Total</small>
            <h4 class="fw-bold text-primary mb-0">${{ number_format($stats['grand_total'] ?? 0, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted fw-bold">Total Paid</small>
            <h4 class="fw-bold text-success mb-0">${{ number_format($stats['total_paid'] ?? 0, 2) }}</h4>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 border-0 shadow-sm">
            <small class="text-muted fw-bold">Total Balance</small>
            <h4 class="fw-bold text-danger mb-0">${{ number_format($stats['total_balance'] ?? 0, 2) }}</h4>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-3">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reference</th>
                    <th>Date</th>
                    <th>Grand Total</th>
                    <th>Paid</th>
                    <th>Balance</th>
                    <th>Payment Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <!-- Clickable Reference link to open invoice -->
                        <td>
                            <a href="{{ route('sales.show', $sale->id) }}" class="fw-bold text-primary text-decoration-none">
                                <i class="fa-solid fa-receipt me-1"></i> {{ $sale->reference_no }}
                            </a>
                        </td>
                        <td>{{ $sale->date }}</td>
                        <td class="fw-bold">${{ number_format($sale->grand_total ?? 0, 2) }}</td>
                        <td class="text-success fw-bold">${{ number_format($sale->paid_amount ?? 0, 2) }}</td>
                        <td class="text-danger fw-bold">${{ number_format($sale->balance ?? 0, 2) }}</td>
                        <td>
                            <span class="badge {{ strtolower($sale->payment_status) == 'paid' ? 'bg-success' : (strtolower($sale->payment_status) == 'partial' ? 'bg-warning' : 'bg-danger') }}">
                                {{ $sale->payment_status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <!-- View Invoice Button -->
                            <a href="{{ route('sales.show', $sale->id) }}" class="btn btn-sm btn-outline-primary me-1" title="View Invoice">
                                <i class="fa-solid fa-file-invoice"></i>
                            </a>
                            <form action="{{ route('sales.destroy', $sale->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this sale?')" title="Delete Sale">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No sales records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        {{ $sales->links() }}
    </div>
</div>
@endsection