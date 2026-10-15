@extends('layouts.app')

@section('title', 'Categories - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <h3 class="fw-bold text-dark">Category Management</h3>
    <div class="d-flex gap-2">
        <div class="dropdown">
             <button class="btn btn-primary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bolt me-1"></i> Actions
        </button>
            <ul class="dropdown-menu dropdown-menu-end col-auto shadow-sm">
                <li><a href="{{ route('categories.create') }}" class="dropdown-item"><i class="fa-solid fa-plus me-2"></i>Add Category</a></li>
                <li><a href="javascript:void(0)" id="exportExcelBtn" class="dropdown-item"><i class="fa-solid fa-file-lines me-2"></i>Export To Excel File</a></li>
                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="fa-solid fa-file-arrow-down me-2"></i>Import From Excel File</button></li>
            </ul>
        </div>
    </div>
</div>

<!-- Alert Containers -->
<div id="jsAlertContainer"></div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
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

<div class="card shadow-sm border-0">
    <!-- Top Filter Bar -->
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
                <form id="autoSearchForm" action="{{ route('categories.index') }}" method="GET" class="m-0">
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

    <!-- Section Title Header & Bulk Delete Form -->
    <form id="bulkForm" action="{{ route('categories.bulkDelete') }}" method="POST">
        @csrf
        <div class="card-header bg-white d-flex align-items-center justify-content-between py-2">
            <span class="fw-bold text-secondary fs-6">Category List</span>
            <button type="submit" class="btn btn-danger btn-sm rounded-2 d-none" id="bulkDeleteBtn" onclick="return confirm('Are you sure you want to delete selected categories?')">
                <i class="fa-solid fa-trash me-1"></i> Delete Selected
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><input class="form-check-input" type="checkbox" id="selectAll"></th>
                            <th>Code</th>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td class="ps-3">
                                    <input class="form-check-input category-checkbox" type="checkbox" name="selected_category[]" value="{{ $category->id }}">
                                </td>
                                <td class="fw-semibold"><span class="badge bg-light text-dark border">{{ $category->code }}</span></td>
                                <td class="fw-semibold">{{ $category->name }}</td>
                                <td>{{ $category->description ?? 'None' }}</td>
                                <td class="text-center">
                                    <div class="dropdown">
                                        <!-- Trigger Button -->
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            Action
                                        </button>
                                        <!-- Dropdown Menu -->
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">  
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item edit-category-btn"
                                                        data-id="{{ $category->id }}"
                                                        data-code="{{ addslashes($category->code ?? '') }}"
                                                        data-name="{{ addslashes($category->name) }}"
                                                        data-description="{{ addslashes($category->description ?? '') }}">
                                                    <i class="fa-solid fa-pen me-2 text-primary"></i>Edit
                                                </button>
                                            </li> 
                                            <li>
                                                <button type="button" 
                                                        class="dropdown-item text-danger" 
                                                        onclick="confirmDelete({{ $category->id }}, '{{ addslashes($category->name) }}')">
                                                    <i class="fa-solid fa-trash me-2"></i>Delete
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No categories found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
            </div>
        </div>

        <!-- Corrected Pagination Check -->
        @if(method_exists($categories, 'hasPages') && $categories->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} results
                    </div>
                    <div>{{ $categories->links('pagination::bootstrap-4') }}</div>
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Dynamic Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Edit Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="editCategoryForm" action="" method="POST">
                @csrf
                @method('PUT')
                
                <div class="p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div style="width: 45%;">
                            <label class="form-label fw-semibold">Current Code</label>
                            <input type="text" id="modalOriginalCode" class="form-control bg-light" readonly>
                        </div>
                        <div class="text-center mt-4" style="width: 10%;">
                            <i class="fa-solid fa-arrow-right-long fs-4 text-muted"></i>
                        </div>
                        <div style="width: 45%;">
                            <label for="modalNewCode" class="form-label fw-semibold">New Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modalNewCode" name="code" placeholder="Enter new code...">
                        </div>
                    </div>

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
                            <input type="text" class="form-control" id="modalNewName" name="name" placeholder="Enter new category name...">
                        </div>
                    </div>

                    <div>
                        <label for="modalDescription" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="modalDescription" name="description" rows="3" placeholder="Enter category description..."></textarea>
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

<!-- Single Delete Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">Are you sure you want to delete category <strong id="deleteCategoryName"></strong>?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <form id="deleteCategoryForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="btn btn-light px-4 border rounded-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger px-4 rounded-3">
                        <i class="fa-solid fa-trash me-1"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importExcelModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold">Import Category from Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('categories.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Choose Excel File (.xlsx, .xls, .csv)</label>
                        <input type="file" name="excel_file" class="form-control" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 small text-muted">
                        <strong class="d-block text-dark mb-1">Expected Excel Columns:</strong>
                        <code>code</code>, <code>name</code>, <code>description</code>
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
    const checkboxes = document.querySelectorAll('.category-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const exportBtn = document.getElementById('exportExcelBtn');

    const editModal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    const editForm = document.getElementById('editCategoryForm');
    const originalNameInput = document.getElementById('modalOriginalName');
    const originalCodeInput = document.getElementById('modalOriginalCode');
    const newNameInput = document.getElementById('modalNewName');
    const newCodeInput = document.getElementById('modalNewCode');
    const descriptionInput = document.getElementById('modalDescription');

    // Toggle Bulk Delete Button
    function toggleBulkState() {
        const checkedCount = document.querySelectorAll('.category-checkbox:checked').length;
        if (bulkDeleteBtn) {
            checkedCount > 0 ? bulkDeleteBtn.classList.remove('d-none') : bulkDeleteBtn.classList.add('d-none');
        }
    }

    // Select All Checkboxes
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            toggleBulkState();
        });
    }

    checkboxes.forEach(cb => cb.addEventListener('change', toggleBulkState));

    // Export Excel Logic
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            const selected = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selected.length === 0) {
                const container = document.getElementById('jsAlertContainer');
                container.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Please select at least one category to export.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            let url = "{{ route('categories.export') }}";
            url += '?ids=' + selected.join(',');
            window.location.href = url;
        });
    }

    // Edit Modal Handler
  document.querySelectorAll('.edit-category-btn').forEach(button => {
    button.addEventListener('click', function () {
        const categoryId = this.dataset.id;
        const categoryName = this.dataset.name;
        const categoryCode = this.dataset.code;
        const categoryDescription = this.dataset.description;

        editForm.action = '/categories/' + categoryId;
        originalNameInput.value = categoryName;
        originalCodeInput.value = categoryCode;
        newCodeInput.value = categoryCode;     // Fixed: sets code input properly
        newNameInput.value = categoryName;     // Fixed: sets name input properly
        descriptionInput.value = categoryDescription;

        editModal.show();
    });
});

    // Auto-Search Debounce
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
    document.getElementById('deleteCategoryName').textContent = '"' + name + '"';
    const deleteForm = document.getElementById('deleteCategoryForm');
    deleteForm.action = '/categories/' + id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}
</script>
@endsection