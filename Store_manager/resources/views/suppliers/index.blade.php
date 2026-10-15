@extends('layouts.app')

@section('title', 'Suppliers - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <h3 class="fw-bold text-dark">Supplier Management</h3>
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
        <i class="fa-solid fa-plus me-1"></i> Add Supplier
    </a>
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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li><i class="fa-solid fa-exclamation me-1"></i> {{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-users fs-5"></i>
                    
                </div>
                <div>
                    <div class="text-muted small">Total Suppliers</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_suppliers']) }}</div>
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
                    <option value="10" {{ request('per_page') == '10' ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == '50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == '100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <!-- Automatic Search Form -->
            <div class="col-md-4">
                <form id="autoSearchForm" action="{{ route('suppliers.index') }}" method="GET" class="m-0">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="liveSearchInput" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Search name, code, category, unit..." 
                               value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                </form>
            </div>
        </div>
    </div>
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td class="ps-4 font-monospace">{{ $supplier->id }}</td>
                            <td class="fw-semibold">{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone ?? 'N/A' }}</td>
                            <td>{{ $supplier->email ?? 'N/A' }}</td>
                            <td>{{ $supplier->address ?? 'N/A' }}</td>
                            <td class="text-end pe-4">
                                <button type="button" 
                                        class="edit-supplier-btn btn btn-sm btn-outline-secondary me-1"
                                        data-id="{{ $supplier->id }}"
                                        data-name="{{ addslashes($supplier->name) }}"
                                        data-phone="{{ addslashes($supplier->phone ?? '') }}"
                                        data-email="{{ addslashes($supplier->email ?? '') }}"
                                        data-address="{{ addslashes($supplier->address ?? '') }}">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete({{ $supplier->id }}, '{{ addslashes($supplier->name) }}')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No suppliers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Supplier Modal -->
<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Edit Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editSupplierForm" action="" method="POST">
                @csrf
                @method('PUT')   
                <div class="modal-body p-4">
                    <!-- Current vs New Name Comparison -->
                    <div class="d-flex align-items-center mb-3">
                        <div style="width: 45%;">
                            <label class="form-label fw-semibold">Current Name</label>
                            <input type="text" id="modalOriginalName" class="form-control bg-light" readonly>
                        </div>
                        <div class="text-center mt-4" style="width: 10%;">
                            <i class="fa-solid fa-arrow-right-long fs-4 text-muted"></i>
                        </div>
                        <div style="width: 45%;">
                            <label for="modalNewName" class="form-label fw-semibold">New Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalNewName" name="name" placeholder="Enter new supplier name..." required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="modalPhone" class="form-label fw-semibold">Phone</label>
                            <input type="text" class="form-control" id="modalPhone" name="phone" placeholder="Enter phone number...">
                        </div>
                        <div class="col-md-6">
                            <label for="modalEmail" class="form-label fw-semibold">Email</label>
                            <input type="email" class="form-control" id="modalEmail" name="email" placeholder="Enter email address...">
                        </div>
                    </div>

                    <div>
                        <label for="modalAddress" class="form-label fw-semibold">Address</label>
                        <textarea class="form-control" id="modalAddress" name="address" rows="3" placeholder="Enter supplier address..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
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
                <p class="text-muted mb-4">You are about to delete supplier <strong id="deleteSupplierName" class="text-dark"></strong>. This action cannot be undone.</p>
                
                <form id="deleteSupplierForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 rounded-3 fw-bold">
                            <i class="fa-solid fa-trash me-1"></i> Delete
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', '1'); // Reset to page 1 on per_page change
    window.location.href = url.toString();
}

function confirmDelete(id, name) {
    document.getElementById('deleteSupplierName').textContent = '"' + name + '"';
    const deleteForm = document.getElementById('deleteSupplierForm');
    deleteForm.action = '/suppliers/' + id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('liveSearchInput');
    const searchForm = document.getElementById('autoSearchForm');
    let searchTimer = null;

    if (searchInput && searchForm) {
        // Keep focus at the end of the input text on reload
        if (searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Auto submit form 400ms after user stops typing
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                searchForm.submit();
            }, 500);
        });
    }
    const editModalElement = document.getElementById('editSupplierModal');
    if (!editModalElement) return;

    const editModal = new bootstrap.Modal(editModalElement);
    const editForm = document.getElementById('editSupplierForm');
    const originalNameInput = document.getElementById('modalOriginalName');
    const newNameInput = document.getElementById('modalNewName');
    const phoneInput = document.getElementById('modalPhone');
    const emailInput = document.getElementById('modalEmail');
    const addressInput = document.getElementById('modalAddress');

    document.querySelectorAll('.edit-supplier-btn').forEach(button => {
        button.addEventListener('click', function () {
            const supplierId = this.dataset.id;
            const supplierName = this.dataset.name;
            const supplierPhone = this.dataset.phone;
            const supplierEmail = this.dataset.email;
            const supplierAddress = this.dataset.address;

            editForm.action = '/suppliers/' + supplierId;
            originalNameInput.value = supplierName;    
            newNameInput.value = supplierName;   
            phoneInput.value = supplierPhone;
            emailInput.value = supplierEmail;
            addressInput.value = supplierAddress;
            
            editModal.show();
        });
    });
    
});

</script>
@endsection