@extends('layouts.app')

@section('title', 'Purchases - Store Manager')

@section('content')

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Purchases</h3>
        <span class="text-muted small">Track and manage all incoming stock purchases</span>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bolt me-1"></i> Actions
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a href="{{ route('purchases.create') }}" class="dropdown-item">
                    <i class="fa-solid fa-plus me-2 text-primary"></i> Add Purchase
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item" onclick="exportSelectedPurchases()">
                    <i class="fa-solid fa-file-arrow-down me-2 text-success"></i> Export Selected
                </button>
            </li>
            <li>
                <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importExcelModal">
                    <i class="fa-solid fa-file-arrow-up me-2 text-info"></i> Import From Excel/CSV
                </button>
            </li>
        </ul>
    </div>
</div>

<!-- Flash Messages -->
<div id="jsAlertContainer"></div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Summary Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-boxes-stacked fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Purchases</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_purchases']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-sack-dollar fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Spend</div>
                    <div class="fs-4 fw-bold text-dark">${{ number_format($stats['total_spend'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-calendar-days fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">This Month</div>
                    <div class="fs-4 fw-bold text-dark">${{ number_format($stats['this_month'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-warning bg-opacity-10 text-warning d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-clock fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Pending Payment</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['pending_payment']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body py-3 border-bottom">
        <div class="row align-items-center justify-content-between g-2">
            <!-- Show Per Page Dropdown -->
            <div class="col-auto d-flex align-items-center gap-2">
                <label for="perPageSelect" class="fw-semibold text-secondary small mb-0">Show</label>
                <select id="perPageSelect" class="form-select form-select-sm" style="width: 80px;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page', '10') == '10' ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <!-- Live Search -->
            <div class="col-md-4">
                <form id="autoSearchForm" action="{{ route('purchases.index') }}" method="GET" class="m-0">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text"
                               name="search"
                               id="liveSearchInput"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search by reference no, supplier or branch..."
                               value="{{ request('search') }}"
                               autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('purchases.index', ['per_page' => request('per_page', 10)]) }}"
                               class="input-group-text bg-white border-start-0" title="Clear search">
                                <i class="fa-solid fa-xmark text-muted"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK DELETE FORM -->
    <form id="bulkForm" action="{{ route('purchases.bulkDelete') }}" method="POST">
        @csrf
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-start fw-bold text-secondary fs-6">
                List Purchases
                @if(request('search'))
                    <span class="text-muted fw-normal small">— results for "{{ request('search') }}"</span>
                @endif
            </h5>
            <button type="submit" class="btn btn-danger btn-sm rounded-2 d-none" id="bulkDeleteBtn" onclick="return confirm('Are you sure you want to delete selected items?')">
                <i class="fa-solid fa-trash me-1"></i> Delete Selected
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><input class="form-check-input" type="checkbox" id="selectAll"></th>
                            <th>Reference</th>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Branch / Warehouse</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Payment</th>
                            <th>Created By</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td class="ps-3">
                                    <input class="form-check-input purchase-checkbox" type="checkbox" name="selected_purchases[]" value="{{ $purchase->id }}">
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $purchase->reference_no ?? '#' . $purchase->id }}</span>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y') }}</td>
                                <td>
                                    <a href="javascript:void(0)"
                                       class="fw-semibold text-decoration-none view-purchase-btn"
                                       style="cursor:pointer"
                                       data-id="{{ $purchase->id }}"
                                       data-reference="{{ $purchase->reference_no ?? '#' . $purchase->id }}"
                                       data-date="{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y H:i') }}"
                                       data-supplier="{{ $purchase->supplier->name ?? 'N/A' }}"
                                       data-branch="{{ $purchase->branch->name ?? 'N/A' }}"
                                       data-warehouse="{{ $purchase->warehouse->name ?? 'N/A' }}"
                                       data-total="${{ number_format($purchase->total_amount, 2) }}"
                                       data-status="{{ ucfirst($purchase->status ?? 'completed') }}"
                                       data-payment="{{ ucfirst($purchase->payment_status ?? 'pending') }}"
                                       data-user="{{ $purchase->user->name ?? 'N/A' }}">
                                        {{ $purchase->supplier->name ?? 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    {{ $purchase->branch->name ?? '-' }}
                                    @if($purchase->warehouse)
                                        <small class="text-muted d-block">{{ $purchase->warehouse->name }}</small>
                                    @endif
                                </td>
                                <td class="text-end"><strong class="text-success">${{ number_format($purchase->total_amount, 2) }}</strong></td>
                                <td class="text-center">
                                    @php $status = $purchase->status ?? 'completed'; @endphp
                                    <span class="badge rounded-pill {{ $status === 'completed' ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php $pay = $purchase->payment_status ?? 'pending'; @endphp
                                    <span class="badge rounded-pill
                                        {{ $pay === 'paid' ? 'bg-success-subtle text-success' : ($pay === 'partial' ? 'bg-warning-subtle text-warning' : 'bg-danger-subtle text-danger') }}">
                                        {{ ucfirst($pay) }}
                                    </span>
                                </td>
                                <td>{{ $purchase->user->name ?? 'System' }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a href="javascript:void(0)"
                                                   class="dropdown-item view-purchase-btn"
                                                   data-id="{{ $purchase->id }}"
                                                   data-reference="{{ $purchase->reference_no ?? '#' . $purchase->id }}"
                                                   data-date="{{ \Carbon\Carbon::parse($purchase->date)->format('M d, Y H:i') }}"
                                                   data-supplier="{{ $purchase->supplier->name ?? 'N/A' }}"
                                                   data-branch="{{ $purchase->branch->name ?? 'N/A' }}"
                                                   data-warehouse="{{ $purchase->warehouse->name ?? 'N/A' }}"
                                                   data-total="${{ number_format($purchase->total_amount, 2) }}"
                                                   data-status="{{ ucfirst($purchase->status ?? 'completed') }}"
                                                   data-payment="{{ ucfirst($purchase->payment_status ?? 'pending') }}"
                                                   data-user="{{ $purchase->user->name ?? 'N/A' }}">
                                                    <i class="fa-solid fa-eye me-2"></i>View Purchase
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('purchases.edit', $purchase->id) }}" class="dropdown-item">
                                                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button"
                                                        class="dropdown-item text-danger"
                                                        onclick="confirmDelete({{ $purchase->id }}, '{{ $purchase->reference_no ?? ('Purchase #' . $purchase->id) }}')">
                                                    <i class="fa-solid fa-trash me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-box-open fs-2 d-block mb-2 text-secondary"></i>
                                    No purchases found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($purchases, 'hasPages') && $purchases->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $purchases->firstItem() }} to {{ $purchases->lastItem() }} of {{ $purchases->total() }} results
                    </div>
                    <div>{{ $purchases->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Purchase Detail Modal -->
<div class="modal fade" id="purchaseDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalPurchaseTitle">Purchase Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Supplier</span>
                        <strong id="modalSupplier" class="text-dark fs-6"></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Purchase Date</span>
                        <strong id="modalDate" class="text-dark fs-6"></strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Branch</span>
                        <span id="modalBranch" class="text-dark"></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Warehouse</span>
                        <span id="modalWarehouse" class="text-dark"></span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Total Amount</span>
                        <h4 id="modalTotal" class="text-success fw-bold mb-0"></h4>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Status</span>
                        <span id="modalStatus" class="text-dark fw-semibold"></span>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Payment</span>
                        <span id="modalPayment" class="text-dark fw-semibold"></span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Created By</span>
                        <span id="modalUser" class="text-dark"></span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                <h4 class="fw-bold text-dark">Are you sure?</h4>
                <p class="text-muted mb-4">You are about to delete <strong id="deletePurchaseName" class="text-dark"></strong></p>
                <form id="singleDeleteForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 rounded-3 fw-bold">Yes, Delete It</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Excel/CSV Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Import Purchases from CSV/Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('purchases.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose File (.csv, .xlsx)</label>
                        <input type="file" name="excel_file" class="form-control" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 small text-muted">
                        <strong class="d-block text-dark mb-1">Expected Columns (in order):</strong>
                        <code>date</code>, <code>supplier_id</code>, <code>branch_id</code>, <code>warehouse_id</code>, <code>total_amount</code>
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3">Upload & Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.purchase-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const detailModal = new bootstrap.Modal(document.getElementById('purchaseDetailModal'));

    function toggleBulkState() {
        const checkedCount = document.querySelectorAll('.purchase-checkbox:checked').length;
        if (bulkDeleteBtn) {
            checkedCount > 0 ? bulkDeleteBtn.classList.remove('d-none') : bulkDeleteBtn.classList.add('d-none');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkState();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', toggleBulkState));

    // Modal View Details
    document.querySelectorAll('.view-purchase-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('modalPurchaseTitle').textContent = 'Purchase ' + this.dataset.reference;
            document.getElementById('modalSupplier').textContent = this.dataset.supplier;
            document.getElementById('modalDate').textContent = this.dataset.date;
            document.getElementById('modalBranch').textContent = this.dataset.branch;
            document.getElementById('modalWarehouse').textContent = this.dataset.warehouse;
            document.getElementById('modalTotal').textContent = this.dataset.total;
            document.getElementById('modalStatus').textContent = this.dataset.status;
            document.getElementById('modalPayment').textContent = this.dataset.payment;
            document.getElementById('modalUser').textContent = this.dataset.user;

            detailModal.show();
        });
    });

    // Live Search
    const searchInput = document.getElementById('liveSearchInput');
    const searchForm = document.getElementById('autoSearchForm');
    let timer = null;

    if (searchInput) {
        if (searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                searchForm.submit();
            }, 400);
        });
    }
});

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

function confirmDelete(id, name) {
    document.getElementById('deletePurchaseName').textContent = '"' + name + '"';
    const form = document.getElementById('singleDeleteForm');
    form.action = '/purchases/' + id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

function exportSelectedPurchases() {
    const checkedboxes = document.querySelectorAll('.purchase-checkbox:checked');
    const selectedIds = Array.from(checkedboxes).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one purchase to export.');
        return;
    }

    window.location.href = "{{ route('purchases.export') }}?ids=" + selectedIds.join(',');
}
</script>
@endsection