@extends('layouts.app')

@section('title', 'Customers - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <h3 class="fw-bold text-dark">Customer Management</h3>
     <div class="d-flex gap-2"> 
        <div class="dropdown">
         <button class="btn btn-primary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bolt me-1"></i> Actions
        </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="{{ route('customers.create') }}"class="dropdown-item"><i class="fa-solid fa-plus me-1"></i> Add Product</a></li>
                <li><button type="button" class="dropdown-item" onclick="exportSelectedCustomers()"><i class="fa-solid fa-file-arrow-down me-2"></i> Export Selected</button></li>
                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="fa-solid fa-file-arrow-down me-2"></i>Import From Excel File</button></li>
            </ul>
                
        </div>
     </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
<div class="">
   <p>You can download the table as excel & You can import data using excel file.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-users fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Customers</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_customers']) }}</div>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="card shadow-sm border-0">
  <div class="card-body border-bottom py-3">
        <div class="row align-items-center justify-content-between g-2">
            <!-- Show Entries Dropdown -->
            <div class="col-auto d-flex align-items-center gap-2">
                <span class="fw-semibold text-muted small">Show</span>
                <select name="per_page" id="perPageSelect" class="form-select form-select-sm" style="width: 80px;" onchange="changePerPage(this.value)">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
                </select>
            </div>

            <!-- Search Bar -->
            <div class="col-md-4">
                <form id="autoSearchForm" action="{{ route('customers.index') }}" method="GET" class="m-0">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="liveSearchInput" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Search name, code, description..." 
                               value="{{ request('search') }}" 
                               autocomplete="off">
                    </div>
                </form>
            </div>
        </div>
    </div>
<form action="{{route('customers.bulk-delete')}}" id="bulkForm" method="POST">
    @csrf
    @method('DELETE')
    <div class="card-header bg-white d-flex align-items-center justify-content-between py-2">
        <span class="fw-bold text-secondary fs-6">Customer List</span>
        <button type="submit" class="btn btn-danger btn-sm rounded-2 d-none" id="bulkDeleteBtn" onclick="return confirm('Are you sure you want to delete selected customers?')">
            <i class="fa-solid fa-trash me-1"></i> Delete Selected
        </button>
    </div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3"><input class="form-check-input" type="checkbox" id="selectAll"></th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Address</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>

                            <td class="ps-3">
                                <input class="form-check-input customer-checkbox" type="checkbox" name="selected_customer[]" value="{{ $customer->id }}">
                            </td>
                            <td class="fw-semibold">{{ $customer->name }}</td>
                            <td>{{ $customer->phone ?? 'N/A' }}</td>
                            <td>{{ $customer->email ?? 'N/A' }}</td>
                            <td>{{ $customer->address ?? 'N/A' }}</td>
                            <td class="text-end pe-4">
                                <button 
                                type="button"
                                class="btn edit-customer-btn btn-sm btn-outline-secondary me-1"
                                data-id="{{ $customer->id }}"
                                data-name="{{ $customer->name }}"
                                data-phone="{{ addslashes($customer->phone ?? '') }}"
                                data-email="{{ $customer->email }}"
                                data-address="{{ $customer->address }}"
                            >
                                <i class="fa-solid fa-pen"></i>
                           <button type="button" 
                                class="btn btn-danger btn-sm" 
                                onclick="confirmDelete({{ $customer->id }}, '{{ addslashes($customer->name) }}')">
                            <i class="fa-solid fa-trash"></i>
                            </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
  @if(method_exists($customers, 'hasPages') && $customers->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} results
                    </div>
                    <div>{{ $customers->links('pagination::bootstrap-4') }}</div>
                </div>
            </div>
        @endif
</form>
</div>

{{-- Delete modal --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-body p-4 text-center">
        <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
        <h5 class="modal-title fw-bold text-dark">Delete Customer</h5>
        <p class="text-secondary mb-0">Are you sure you want to delete <strong id="deleteCustomerName"></strong></p>
      </div>
      <form id="deleteCustomerForm" action="" method="POST">
        @csrf
        @method('DELETE')
        <div class="d-flex justify-content-center gap-2 mb-3">
            <button type="button" class="btn btn-light px-4 border rounded-3" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger px-4 rounded-3"><i class="fa-solid fa-trash me-1"></i> Delete</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Edit modal --}}
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content rounded-4 border-0 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold text-dark">Edit Customer</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
     <form id="editCustomerForm" action="" method="POST">
                @csrf
                @method('PUT') 
                <div class="p-4">
                   <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modalName" name="name"  placeholder="e.g. General Customer / John Doe">
                    </div>
                   <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Customer Phone <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="modalphone" name="phone"  placeholder="e.g. +855">
                    </div>
                   <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Customer Email<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="modalemail" name="email"  placeholder="e.g. your@gmail.com">
                    </div>
                   <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">Customer address <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modaladdress" name="address"  placeholder="e.g. 271....">
                    </div>
                </div>
                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-light px-4 border rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-3">
                        <i class="fa-solid fa-save me-1"></i> Save Changes
                    </button>
                </div>
     </form>
    </div>
  </div>
</div>
<!-- Import Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Import Customer from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('customers.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose Excel File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="excel_file" class="form-control" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 small text-muted">
                        <strong class="d-block text-dark mb-1">Expected Excel Columns:</strong>
                        <code>name</code>, <code>phone</code>, <code>email</code>,<code>address</code>
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
     document.addEventListener('DOMContentLoaded', function(){
        const editModalElement= document.getElementById('editCustomerModal');
        if(!editModalElement) return;

        const editModal = new bootstrap.Modal(editModalElement);
        const editForm = document.getElementById('editCustomerForm');
        const nameInput = document.getElementById('modalName');
        const phoneInput = document.getElementById('modalphone');
        const emailInput = document.getElementById('modalemail');
        const addressInput = document.getElementById('modaladdress');
      
        const selectAll = document.getElementById('selectAll');
        const customerCheckboxes = document.querySelectorAll('.customer-checkbox');
        const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');

        function toggleBulkState() {
                const checkedCount = document.querySelectorAll('.customer-checkbox:checked').length;
                if (bulkDeleteBtn) {
                    checkedCount > 0 ? bulkDeleteBtn.classList.remove('d-none') : bulkDeleteBtn.classList.add('d-none');
                }
            }

            if (selectAll) {
                selectAll.addEventListener('change', function() {
                    customerCheckboxes.forEach(cb => cb.checked = this.checked);
                    toggleBulkState();
                });
            }

            customerCheckboxes.forEach(cb => {
                cb.addEventListener('change', function() {
                    toggleBulkState();
                    if (!this.checked && selectAll) selectAll.checked = false;
                });
            });

    document.querySelectorAll('.edit-customer-btn').forEach(button => {
        button.addEventListener('click', function(){

            const customerID = this.dataset.id;
            const customerName = this.dataset.name;
            const customerPhone =this.dataset.phone;
            const customerEmail =this.dataset.email;
            const customerAddress =this.dataset.address;

            editForm.action = '/customers/' + customerID;

            nameInput.value = customerName;
            phoneInput.value = customerPhone;
            emailInput.value = customerEmail;
            addressInput.value = customerAddress;

            editModal.show();
        })
    });
     // Live Search Handler
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

      function confirmDelete(id, name){
        const deleteForm = document.getElementById('deleteCustomerForm');
        deleteForm.action = '/customers/' + id;
        document.getElementById('deleteCustomerName').textContent = '"' + name + '"';
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        deleteModal.show();
    }
        function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1);
        window.location.href = url.toString();
}
// Place this at the VERY BOTTOM of your script tag, outside DOMContentLoaded
function exportSelectedCustomers() {
    const checkedBoxes = document.querySelectorAll('.customer-checkbox:checked');
    const selectedIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one customer to export.');
        return;
    }

    window.location.href = "{{ route('customers.export') }}?ids=" + selectedIds.join(',');
}
</script>

@endsection