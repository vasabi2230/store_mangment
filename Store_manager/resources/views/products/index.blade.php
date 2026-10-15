@extends('layouts.app')

@section('title', 'Products - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <h3 class="fw-bold text-dark">Product Inventory</h3>
    <div class="d-flex gap-2"> 
        <div class="dropdown">
         <button class="btn btn-primary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
            <i class="fa-solid fa-bolt me-1"></i> Actions
        </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a href="{{ route('products.create') }}" class="dropdown-item"><i class="fa-solid fa-plus me-1"></i> Add Product</a></li>
                <li><a href="javascript:void(0)" id="exportExcelBtn" class="dropdown-item"><i class="fa-solid fa-file-lines me-2"></i>Export To Excel File</a></li>
                <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#importExcelModal"><i class="fa-solid fa-file-arrow-down me-2"></i>Import From Excel File</button></li>
            </ul>
                
        </div>
        
        {{-- <a href="javascript:void(0)" id="exportExcelBtn" class="btn btn-outline-success">
            <i class="fa-solid fa-file-excel me-1"></i> Export Excel
        </a> --}}
        {{-- <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importExcelModal">
            <i class="fa-solid fa-file-import me-1"></i> Import Excel
        </button> --}}
        {{-- <a href="{{ route('products.create') }}" class="dropdown-item">
            <i class="fa-solid fa-plus me-1"></i> Add Product
        </a> --}}
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

<!-- TOP CONTROLS: OUTSIDE OF BULK FORM -->
<div class="">
   <p>You can download the table as excel & You can import data using excel file.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-3 bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                    <i class="fa-solid fa-barcode fs-5"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Products</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_products']) }}</div>
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
                    <div class="text-muted small">Total Spend</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($stats['total_spend'], 2) }}</div>
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
                    <div class="text-muted small">Total Quantity</div>
                    <div class="fs-4 fw-bold text-dark">{{ $stats['total_qty'] }}</div>
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
                <form id="autoSearchForm" action="{{ route('products.index') }}" method="GET" class="m-0">
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
    <!-- BULK DELETE FORM -->
    <form id="bulkForm" action="{{ route('products.bulkDelete') }}" method="POST">
        @csrf
        <div class="card-header bg-white d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-start fw-bold text-secondary fs-6">Product List</h5>
            <button type="submit" class="btn btn-danger btn-sm rounded-2 d-none" id="bulkDeleteBtn" onclick="return confirm('Are you sure you want to delete selected items?')">
                <i class="fa-solid fa-trash "></i> Delete Selected
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 ">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3"><input class="form-check-input" type="checkbox" id="selectAll"></th>
                            <th>Image</th>
                            <th class="ps-3">Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th>Cost</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                       @forelse($products as $product)
                
                        <tr>
                            <td class="ps-3">
                                <input class="form-check-input product-checkbox" type="checkbox" name="selected_products[]" value="{{ $product->id }}">
                            </td>
                            
                            <!-- Image Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                @if($product->image)
                                    <img src="{{ asset($product->image) }}" class="rounded border" style="width: 45px; height: 45px; object-fit: cover;">
                                @else
                                    <div class="bg-light rounded border d-flex align-items-center justify-content-center text-muted" style="width: 45px; height: 45px;">
                                        <i class="fa-solid fa-image"></i>
                                    </div>
                                @endif
                            </td>

                            <!-- Code Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                <span class="badge bg-light text-dark border">{{ $product->code }}</span>
                            </td>

                            <!-- Name Cell -->
                            <td>
                                <a href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                    {{ $product->name }}
                                </a>
                            </td>

                            <!-- Category Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                <span class="badge bg-info text-dark">{{ $product->category->name ?? 'N/A' }}</span>
                            </td>

                            <!-- Unit Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                {{ $product->unit->name ?? 'N/A' }}
                            </td>

                            <!-- Cost Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                ${{ number_format($product->cost, 2) }}
                            </td>

                            <!-- Price Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-danger text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                ${{ number_format($product->price, 2) }}
                            </td>

                            <!-- Quantity Cell -->
                            <td href="javascript:void(0)" 
                                class="fw-semibold text-decoration-none view-product-btn"
                                style="cursor:pointer"
                                data-id="{{ $product->id }}"
                                data-name="{{ $product->name }}"
                                data-code="{{ $product->code }}"
                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                data-cost="${{ number_format($product->cost, 2) }}"
                                data-price="${{ number_format($product->price, 2) }}"
                                data-qty="{{ $product->quantity }}"
                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                {{ $product->quantity }}pcs
                            </td>

                            <!-- Action Dropdown Item -->
                            <td class="text-center">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-3" type="button" data-bs-toggle="dropdown">
                                        Action
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>  
                                            <a href="javascript:void(0)" 
                                                class="dropdown-item view-product-btn"
                                                data-id="{{ $product->id }}"
                                                data-name="{{ $product->name }}"
                                                data-code="{{ $product->code }}"
                                                data-category="{{ $product->category->name ?? 'N/A' }}"
                                                data-unit="{{ $product->unit->name ?? 'N/A' }}"
                                                data-cost="${{ number_format($product->cost, 2) }}"
                                                data-price="${{ number_format($product->price, 2) }}"
                                                data-qty="{{ $product->quantity }}"
                                                data-description="{{ $product->description ?? 'No description provided.' }}"
                                                data-image="{{ $product->image ? asset($product->image) : '' }}">
                                                <i class="fa-solid fa-eye me-2"></i>View Product
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item" href="{{ route('products.edit', $product->id) }}"><i class="fa-solid fa-pen me-2"></i> Edit</a></li>
                                        <li>
                                            <button type="button" 
                                                    class="dropdown-item text-danger" 
                                                    onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')">
                                                <i class="fa-solid fa-trash me-2"></i> Delete
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                    @endforelse
                    </tbody>
                    <tfoot class="table-light" style="border-top: 2px solid #dee2e6; border-bottom: 2px solid #dee2e6;">
                        <th></th>
                        <th style="color:rgba(0, 0, 0, 0.625)">Image</th>
                        <th style="color:rgba(0, 0, 0, 0.625)" class="ps-3">Code</th>
                        <th style="color:rgba(0, 0, 0, 0.625)">Name</th>
                        <th style="color:rgba(0, 0, 0, 0.625)">Category</th>
                        <th style="color:rgba(0, 0, 0, 0.625)">Unit</th>
                        <th style="color:rgba(0, 0, 0, 0.625)">Cost</th>
                        <th style="color:rgba(0, 0, 0, 0.625)">{{ number_format($stats['total_spend'], 2) }}</th>
                        <th style="color:rgba(0, 0, 0, 0.625)">{{$stats['total_qty'] }}</th>
                        <th style="color:rgba(0, 0, 0, 0.625)" class="text-center">Actions</th>
                        </tfoot>
                </table>
            </div>
        </div>

        {{-- @if($products->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="small text-muted">
                        Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
                    </div>
                    <div>{{ $products->links('pagination::bootstrap-4') }}</div>
                </div>
            </div>
        @endif --}}
        @if($products->hasPages())
    <div class="card-footer bg-white py-3 border-top-0">
        <div class="d-flex justify-content-between align-items-center">
            <div class="small text-muted">
                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
            </div>
            <div>
                {{ $products->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>
@endif
    </div>
</form>

<!-- Modal & Delete Dialog Definitions -->
<div class="modal fade" id="productDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalProductName">Product Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-4">
                    <div class="col-md-5 text-center">
                        <div id="modalImageContainer" class="bg-light rounded-3 p-3 d-flex align-items-center justify-content-center" style="min-height: 220px; max-height: 260px;">
                            <img id="modalProductImage" src="" class="img-fluid rounded" style="max-height: 220px; object-fit: contain;">
                            <div id="modalImagePlaceholder" class="text-muted d-none">
                                <i class="fa-solid fa-image fs-1 opacity-25"></i>
                                <p class="small mb-0">No Image Available</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="mb-3">
                            <span class="badge px-3 py-2 fs-6 bg-dark" id="modalProductCode"></span>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <span class="text-muted small d-block">Category</span>
                                <strong id="modalProductCategory" class="text-dark"></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Unit</span>
                                <strong id="modalProductUnit" class="text-dark"></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Cost Price</span>
                                <strong id="modalProductCost" class="text-dark"></strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted small d-block">Selling Price</span>
                                <strong id="modalProductPrice" class="text-success fs-5"></strong>
                            </div>
                            <div class="col-12">
                                <span class="text-muted small d-block">Quantity in Stock</span>
                                <span id="modalProductQty" class="badge bg-success-subtle text-success border border-success px-3 py-1 fw-bold"></span>
                            </div>
                        </div>
                        <div>
                            <span class="text-muted small d-block mb-1">Description</span>
                            <p id="modalProductDescription" class="text-secondary small bg-light p-3 rounded-3 mb-0" style="min-height: 70px;"></p>
                        </div>
                    </div>
                </div>
            </div>
           <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-danger" id="modalDeleteBtn">
                    <i class="fa-solid fa-trash me-2"></i>Delete
                </button>
                
                <a href="#" class="btn btn-primary px-4 rounded-3" id="modalEditBtn">
                    <i class="fa-solid fa-pen me-1"></i> Edit
                </a>
                
                <button type="button" class="btn btn-secondary px-4 rounded-3" data-bs-dismiss="modal">Close</button>
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-body p-4 text-center">
                <i class="fa-solid fa-triangle-exclamation text-danger fs-1 mb-3"></i>
                <h4 class="fw-bold text-dark">Are you sure?</h4>
                <p class="text-muted mb-4">You are about to delete product <strong id="deleteProductName" class="text-dark"></strong>. This action cannot be undone.</p>
                <form id="deleteProductForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-light px-4 border rounded-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger px-4 rounded-3">Yes, Delete It</button>
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
<h5 class="modal-title fw-bold">Import Products from Excel</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data">
@csrf
<div class="modal-body p-4">
<div class="mb-3">
<label class="form-label fw-semibold">Choose Excel File (.xlsx, .xls, .csv)</label>
<input type="file" name="excel_file" class="form-control" required>
</div>
<div class="p-3 bg-light rounded-3 small text-muted">
<strong class="d-block text-dark mb-1">Expected Excel Columns:</strong>
<code>code</code>, <code>name</code>, <code>cost_price</code>, <code>selling_price</code>, <code>quantity</code>, <code>description</code>
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
        function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1); // Reset to first page
    window.location.href = url.toString();
}
// Global delete trigger function (Accessible anywhere)
function confirmDelete(id, name) {
    // Hide detail modal if open
    const detailModalEl = document.getElementById('productDetailModal');
    const detailModal = bootstrap.Modal.getInstance(detailModalEl);
    if (detailModal) {
        detailModal.hide();
    }

    document.getElementById('deleteProductName').textContent = '"' + name + '"';
    document.getElementById('deleteProductForm').action = '/products/' + id;
    
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const exportBtn = document.getElementById('exportExcelBtn');
    const detailModal = new bootstrap.Modal(document.getElementById('productDetailModal'));

    // Live Search Handler
    const searchInput = document.getElementById('liveSearchInput');
    const searchForm = document.getElementById('autoSearchForm');
    let timer = null;

    if (searchInput && searchForm) {
        // Keep cursor at the end of input after page refresh
        if (searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Auto submit form 400ms after user stops typing
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                searchForm.submit();
            }, 400);
        });
    }
    // Checkbox State Handler
    function toggleBulkState() {
        const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
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

    // Export Logic
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            const selected = Array.from(checkboxes)
                .filter(cb => cb.checked)
                .map(cb => cb.value);

            if (selected.length === 0) {
                const container = document.getElementById('jsAlertContainer');
                container.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>Please select at least one product to export.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `;
                container.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            let url = "{{ route('products.export') }}";
            url += '?ids=' + selected.join(',');
            window.location.href = url;
        });
    }



    // Modal View Details
    document.querySelectorAll('.view-product-btn').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.id;
            const productName = this.dataset.name;

            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductCode').textContent = 'Product Code: ' + this.dataset.code;
            document.getElementById('modalProductCategory').textContent = this.dataset.category;
            document.getElementById('modalProductUnit').textContent = this.dataset.unit;
            document.getElementById('modalProductCost').textContent = this.dataset.cost;
            document.getElementById('modalProductPrice').textContent = this.dataset.price;
            document.getElementById('modalProductQty').textContent = this.dataset.qty + ' pcs available';
            document.getElementById('modalProductDescription').textContent = this.dataset.description;

            const imgElement = document.getElementById('modalProductImage');
            const placeholder = document.getElementById('modalImagePlaceholder');

            if (this.dataset.image) {
                imgElement.src = this.dataset.image;
                imgElement.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                imgElement.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }

            document.getElementById('modalEditBtn').href = `/products/${productId}/edit`;

            // Connect Modal Delete Button to Delete Dialog
            document.getElementById('modalDeleteBtn').onclick = function () {
                confirmDelete(productId, productName);
            };

            detailModal.show();
        });
    });
});
</script>
@endsection