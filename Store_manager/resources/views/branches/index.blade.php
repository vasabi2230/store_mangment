@extends('layouts.app')

@section('title', 'Branches - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <h3 class="fw-bold text-dark">Branches</h3>
      <div class="d-flex gap-2"> 
        <div class="dropdown">
         <button class="btn btn-primary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bolt me-1"></i> Actions
        </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="{{ route('branches.create') }}"class="dropdown-item"><i class="fa-solid fa-plus me-1"></i> Add Product</a></li>
                <li><button type="button" class="dropdown-item" onclick="exportSelectedBranches()"><i class="fa-solid fa-file-arrow-down me-2"></i> Export Selected</button></li>
                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="fa-solid fa-file-arrow-down me-2"></i>Import From Excel File</button></li>
            </ul>
                
        </div>
     </div>
</div>
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-code-branch fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Branches</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_branches']) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>   

<!-- Container for JS Alerts -->
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
<div class="">
   <p>You can download the table as excel & You can import data using excel file.</p>
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
                <form id="autoSearchForm" action="{{ route('branches.index') }}" method="GET" class="m-0">
                    <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" 
                               name="search" 
                               id="liveSearchInput" 
                               class="form-control border-start-0 ps-0" 
                               placeholder="Search branches..." 
                               value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- BULK DELETE FORM -->
    <form id="bulkForm" action="{{ route('branches.bulkDelete') }}" method="POST">
        @csrf
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-start fw-bold text-secondary fs-6">List Branches</h5>
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
                            <th>Logo</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Email</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                            <tr>
                                <td class="ps-3">
                                    <input class="form-check-input branch-checkbox" type="checkbox" name="selected_branches[]" value="{{ $branch->id }}">
                                </td>
                                <td>
                                    @if($branch->logo)
                                        <img src="{{ asset($branch->logo) }}" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 45px; height: 45px;">
                                            <i class="fa-solid fa-store"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a href="javascript:void(0)" 
                                       class="fw-semibold text-decoration-none view-branch-btn"
                                       style="cursor:pointer"
                                       data-id="{{ $branch->id }}"
                                       data-name="{{ $branch->name }}"
                                       data-phone="{{ $branch->phone ?? 'N/A' }}"
                                       data-address="{{ $branch->address ?? 'N/A' }}"
                                       data-email="{{ $branch->email ?? 'N/A' }}"
                                       data-logo="{{ $branch->logo ? asset($branch->logo) : '' }}">
                                        {{ $branch->name }}
                                    </a>
                                </td>
                                <td>{{ $branch->phone ?? 'N/A' }}</td>
                                <td>{{ $branch->address ?? 'N/A' }}</td>
                                <td>{{ $branch->email ?? 'N/A' }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                            Action
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>  
                                                <a href="javascript:void(0)" 
                                                   class="dropdown-item view-branch-btn"
                                                   data-id="{{ $branch->id }}"
                                                   data-name="{{ $branch->name }}"
                                                   data-phone="{{ $branch->phone ?? 'N/A' }}"
                                                   data-address="{{ $branch->address ?? 'N/A' }}"
                                                   data-email="{{ $branch->email ?? 'N/A' }}"
                                                   data-logo="{{ $branch->logo ? asset($branch->logo) : '' }}">
                                                    <i class="fa-solid fa-eye me-2"></i>View Branch
                                                </a>
                                            </li>
                                            <li><a class="dropdown-item" href="{{ route('branches.edit', $branch->id) }}"><i class="fa-solid fa-pen me-2"></i> Edit</a></li>
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item text-danger" 
                                                        onclick="confirmDelete({{ $branch->id }}, '{{ addslashes($branch->name) }}')">
                                                    <i class="fa-solid fa-trash me-2"></i> Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No branches found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($branches, 'hasPages') && $branches->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $branches->firstItem() }} to {{ $branches->lastItem() }} of {{ $branches->total() }} results
                    </div>
                    <div>{{ $branches->links('pagination::bootstrap-4') }}</div>
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Branch Detail Modal -->
<div class="modal fade" id="branchDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalBranchName">Branch Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5 text-center">
                        <div id="modalLogoContainer" class="bg-light rounded-3 p-3 d-flex align-items-center justify-content-center" style="min-height: 200px; max-height: 240px;">
                            <img id="modalBranchLogo" src="" class="img-fluid rounded" style="max-height: 200px; object-fit: contain;">
                            <div id="modalLogoPlaceholder" class="text-muted d-none">
                                <i class="fa-solid fa-store fs-1 opacity-25"></i>
                                <p class="small mb-0 mt-2">No Logo Available</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="row g-3">
                            <div class="col-12">
                                <span class="text-muted small d-block">Phone</span>
                                <strong id="modalBranchPhone" class="text-dark fs-6"></strong>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small d-block">Email</span>
                                <strong id="modalBranchEmail" class="text-dark fs-6"></strong>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small d-block">Address</span>
                                <p id="modalBranchAddress" class="text-secondary small bg-light p-3 rounded-3 mb-0" style="min-height: 60px;"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Single Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                <h4 class="fw-bold text-dark">Are you sure?</h4>
                <p class="text-muted mb-4">You are about to delete branch <strong id="deleteBranchName" class="text-dark"></strong></p>
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
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Import Branch from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('branches.import') }}" method="POST" enctype="multipart/form-data">
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
document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.branch-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const detailModal = new bootstrap.Modal(document.getElementById('branchDetailModal'));

    // Checkbox State Handler
    function toggleBulkState() {
        const checkedCount = document.querySelectorAll('.branch-checkbox:checked').length;
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
    document.querySelectorAll('.view-branch-btn').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('modalBranchName').textContent = this.dataset.name;
            document.getElementById('modalBranchPhone').textContent = this.dataset.phone;
            document.getElementById('modalBranchEmail').textContent = this.dataset.email;
            document.getElementById('modalBranchAddress').textContent = this.dataset.address;

            const imgElement = document.getElementById('modalBranchLogo');
            const placeholder = document.getElementById('modalLogoPlaceholder');

            if (this.dataset.logo) {
                imgElement.src = this.dataset.logo;
                imgElement.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                imgElement.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }

            detailModal.show();
        });
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

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1);
    window.location.href = url.toString();
}

function confirmDelete(id, name) {
    document.getElementById('deleteBranchName').textContent = '"' + name + '"';
    const form = document.getElementById('singleDeleteForm');
    form.action = '/branches/' + id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}
// Place this at the VERY BOTTOM of your script tag, outside DOMContentLoaded
function exportSelectedBranches() {
    const checkedboxes = document.querySelectorAll('.branch-checkbox:checked');
    const selectedIds = Array.from(checkedboxes).map(cb => cb.value);

    if (selectedIds.length === 0) {
        alert('Please select at least one branch to export.');
        return;
    }

    window.location.href = "{{ route('branches.export') }}?ids=" + selectedIds.join(',');
}
</script>
@endsection