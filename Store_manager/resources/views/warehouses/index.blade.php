@extends('layouts.app')

@section('title', 'Warehouses - Store Manager')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <div>
        <h3 class="fw-bold text-dark mb-0">Warehouses</h3>
        <span class="text-muted small">Manage warehouse locations per branch</span>
    </div>
    <div class="dropdown">
        <button class="btn btn-primary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bolt me-1"></i> Actions
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li>
                <a href="{{ route('warehouses.create') }}" class="dropdown-item">
                    <i class="fa-solid fa-plus me-2 text-primary"></i> Add Warehouse
                </a>
            </li>
            <li>
                <button type="button" class="dropdown-item" onclick="exportSelectedWarehouses()">
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
                    <i class="fa-solid fa-warehouse fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Warehouses</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_warehouses']) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-code-branch fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Branches Covered</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['branches_covered']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body py-3 border-bottom">
        <div class="row align-items-center justify-content-between g-2">
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

            <div class="col-md-4">
                <form id="autoSearchForm" action="{{ route('warehouses.index') }}" method="GET" class="m-0">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text"
                               name="search"
                               id="liveSearchInput"
                               class="form-control border-start-0 ps-0"
                               placeholder="Search by name or branch..."
                               value="{{ request('search') }}"
                               autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('warehouses.index', ['per_page' => request('per_page', 10)]) }}"
                               class="input-group-text bg-white border-start-0" title="Clear search">
                                <i class="fa-solid fa-xmark text-muted"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form id="bulkForm" action="{{ route('warehouses.bulkDelete') }}" method="POST">
        @csrf
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-start fw-bold text-secondary fs-6">
                List Warehouses
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
                            <th>#</th>
                            <th>Warehouse Name</th>
                            <th>Branch</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warehouses as $warehouse)
                            <tr>
                                <td class="ps-3">
                                    <input class="form-check-input warehouse-checkbox" type="checkbox" name="selected_warehouses[]" value="{{ $warehouse->id }}">
                                </td>
                                <td><span class="fw-semibold text-dark">#{{ $warehouse->id }}</span></td>
                                <td class="fw-semibold text-dark">{{ $warehouse->name }}</td>
                                <td>{{ $warehouse->branch->name ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a href="{{ route('warehouses.edit', $warehouse->id) }}" class="dropdown-item">
                                                    <i class="fa-solid fa-pen-to-square me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <button type="button"
                                                        class="dropdown-item text-danger"
                                                        onclick="confirmDelete({{ $warehouse->id }}, '{{ $warehouse->name }}')">
                                                    <i class="fa-solid fa-trash me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-warehouse fs-2 d-block mb-2 text-secondary"></i>
                                    No warehouses found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($warehouses, 'hasPages') && $warehouses->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $warehouses->firstItem() }} to {{ $warehouses->lastItem() }} of {{ $warehouses->total() }} results
                    </div>
                    <div>{{ $warehouses->appends(request()->query())->links('pagination::bootstrap-4') }}</div>
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                <h4 class="fw-bold text-dark">Are you sure?</h4>
                <p class="text-muted mb-4">You are about to delete <strong id="deleteWarehouseName" class="text-dark"></strong></p>
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
                <h5 class="modal-title fw-bold">Import Warehouses from CSV/Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('warehouses.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose File (.csv, .xlsx)</label>
                        <input type="file" name="excel_file" class="form-control" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 small text-muted">
                        <strong class="d-block text-dark mb-1">Expected Columns (in order):</strong>
                        <code>branch_id</code>, <code>name</code>
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
    const checkboxes = document.querySelectorAll('.warehouse-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

    function toggleBulkState() {
        const checkedCount = document.querySelectorAll('.warehouse-checkbox:checked').length;
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
    document.getElementById('deleteWarehouseName').textContent = '"' + name + '"';
    const form = document.getElementById('singleDeleteForm');
    form.action = '/warehouses/' + id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

function exportSelectedWarehouses() {
    const checkedboxes = document.querySelectorAll('.warehouse-checkbox:checked');
    const selectedIds = Array.from(checkedboxes).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one warehouse to export.');
        return;
    }

    window.location.href = "{{ route('warehouses.export') }}?ids=" + selectedIds.join(',');
}
</script>
@endsection