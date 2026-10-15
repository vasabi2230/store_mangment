@extends('layouts.app')

@section('title', 'Invoice - ' . $sale->reference_no)

@section('content')
<div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3 d-print-none">
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Back to Sales
        </a>
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fa-solid fa-print me-1"></i> Print Invoice
        </button>
    </div>

    <div class="card p-4 shadow-sm border-0 bg-white">
        <!-- Invoice Header -->
        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
            <div>
                <h2 class="fw-bold text-primary mb-0">INVOICE</h2>
                <span class="fs-5 text-muted fw-semibold">{{ $sale->reference_no }}</span>
            </div>
            <div class="text-end">
                <h5 class="fw-bold mb-0 text-dark">{{ $sale->branch->name ?? 'Store Branch' }}</h5>
                <small class="text-muted">Date: {{ $sale->date }}</small>
            </div>
        </div>

        <!-- Customer & Payment Details -->
        <div class="row mb-4">
            <div class="col-6">
                <h6 class="text-muted text-uppercase mb-1 fs-7">Customer Info:</h6>
                <p class="mb-0 fw-bold fs-5 text-dark">{{ $sale->customer->name ?? 'N/A' }}</p>
            </div>
            <div class="col-6 text-end">
                <h6 class="text-muted text-uppercase mb-1 fs-7">Payment Status:</h6>
                <span class="badge {{ strtolower($sale->payment_status) == 'paid' ? 'bg-success' : 'bg-warning' }} fs-6 px-3 py-2">
                    {{ $sale->payment_status }}
                </span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;" class="text-center">#</th>
                        <th>Product Details</th>
                        <th class="text-end" style="width: 15%;">Unit Price</th>
                        <th class="text-center" style="width: 15%;">Quantity</th>
                        <th class="text-end" style="width: 20%;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale->items as $index => $item)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <strong class="text-primary">[{{ $item->product->code ?? 'N/A' }}]</strong> 
                                <span class="fw-semibold">{{ $item->product->name ?? 'Product' }}</span>
                            </td>
                            <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-end fw-bold">${{ number_format($item->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="fw-bold">
                    <tr>
                        <td colspan="4" class="text-end text-uppercase">Grand Total:</td>
                        <td class="text-end text-primary fs-5">${{ number_format($sale->grand_total, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end text-uppercase">Paid Amount:</td>
                        <td class="text-end text-success">${{ number_format($sale->paid_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td colspan="4" class="text-end text-uppercase">Balance Due:</td>
                        <td class="text-end text-danger">${{ number_format($sale->balance, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="text-center text-muted mt-4 border-top pt-3">
            <small>Thank you for your business!</small>
        </div>
    </div>
</div>
@endsection