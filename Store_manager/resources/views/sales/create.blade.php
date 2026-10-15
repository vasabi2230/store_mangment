@extends('layouts.app')

@section('title', 'Add Sales - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Add New Sale</h3>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Sales
    </a>
</div>

<div class="row">
    <div class="col-md-12">

        <!-- Validation Error Display -->
        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-1 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Server / Database Error Display -->
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-xmark me-2"></i>
                <strong>Could not save:</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card p-4 shadow-sm border-0" style="overflow: visible !important;">
            <form action="{{ route('sales.store') }}" method="POST" id="saleForm">
                @csrf

                <!-- Branch, Customer & Date Row -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Branch <span class="text-danger">*</span></label>
                        <select class="js-example-basic-single form-select" name="branch_id" required>
                            <option value="">-- Select Branch --</option>
                            @foreach($branches ?? [] as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Customer <span class="text-danger">*</span></label>
                        <select class="js-example-basic-single form-select" name="customer_id" required>
                            <option value="">-- Select Customer --</option>
                            @foreach($customers ?? [] as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Sale Date <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="date" class="form-control" value="{{ old('date', date('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>

                <!-- 1. Search Box & Dropdown Container -->
                <div class="position-relative mb-4" style="z-index: 1050;">
                    <label for="product_search" class="form-label fw-bold">Add Product</label>
                    <div class="input-group" style="height: 50px; box-shadow: 0px 0px 0px 3px gray; padding: 3px; border-radius: 5px;">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                        </span>
                        <input type="text" 
                                id="product_search" 
                                class="form-control border-start-0 ps-0" 
                                placeholder="Please select Branch & Customer first..." 
                                autocomplete="off"
                                disabled>
                    </div>
                    
                    <!-- Custom Dropdown Results -->
                    <div id="autocomplete_results" class="shadow-lg border rounded w-100 bg-white mt-1" 
                         style="display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 9999; max-height: 250px; overflow-y: auto;">
                        @foreach($products ?? [] as $product)
                            @php $catName =$product->category->name ?? 'N/A'; @endphp
                            <div class="search-item p-3 border-bottom d-flex justify-content-between align-items-center" 
                                 style="cursor: pointer;"
                                 data-id="{{ $product->id }}"
                                 data-name="{{ $product->name }}"
                                 data-code="{{ $product->code }}"
                                 data-price="{{ $product->price }}"
                                 data-qty="{{ $product->quantity }}">
                                <div>
                                    <strong class="text-primary">[{{ $product->code }}]</strong> 
                                    <span class="fw-bold text-dark me-2">{{ $product->name }}</span>
                                    <small class="text-muted">({{ $catName }})</small>
                                </div>
                                <div>
                                    <span class="badge bg-secondary">Stock: {{ $product->quantity }}</span>
                                    <span class="badge bg-success ms-1">${{ number_format($product->price, 2) }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 2. Dynamic Table -->
                <div class="table-responsive mb-4">
                    <table class="table table-bordered table-hover align-middle mb-0" id="sale_table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;" class="text-center">NO</th>
                                <th style="width: 35%;">Product (Code - Name)</th>
                                <th style="width: 15%;" class="text-end">Price ($)</th>
                                <th style="width: 15%;" class="text-center">Quantity</th>
                                <th style="width: 15%;" class="text-end">Subtotal ($)</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="sale_entries">
                            <!-- Dynamic rows appended here -->
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4" class="text-end text-uppercase">Grand Total ($):</td>
                                <td id="grand_total_display" class="text-end text-primary fs-5">0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Complete Sale
                    </button>
                    <a href="{{ route('sales.index') }}" class="btn btn-outline-danger px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Price & Quantity Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="editModalLabel">Edit Item Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="edit_row_id">
        <div class="row mb-3 align-items-center">
          <div class="col-md-2">
            <label for="edit_qty" class="form-label fw-semibold mb-0">Quantity <span class="text-danger">*</span></label>
          </div>
          <div class="col-md-10">
            <input type="number" step="1" min="1" id="edit_qty" class="form-control" placeholder="1" required>
          </div>
        </div>
        <div class="row mb-3 align-items-center">
          <div class="col-md-2">
            <label for="edit_price" class="form-label fw-semibold mb-0">Price ($) <span class="text-danger">*</span></label>
          </div>
          <div class="col-md-10">
            <input type="number" step="0.01" id="edit_price" class="form-control text-start" placeholder="0.00" required>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="save_modal_changes">Save changes</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const branchSelect = document.querySelector('select[name="branch_id"]');
    const customerSelect = document.querySelector('select[name="customer_id"]');
    const input = document.getElementById('product_search');
    const results = document.getElementById('autocomplete_results');
    const entriesBody = document.getElementById('sale_entries');
    const grandTotalDisplay = document.getElementById('grand_total_display');
    const searchItems = document.querySelectorAll('.search-item');

    // Modal elements
    const editModalEl = document.getElementById('editModal');
    const editModal = new bootstrap.Modal(editModalEl);
    const editRowIdInput = document.getElementById('edit_row_id');
    const editQtyInput = document.getElementById('edit_qty');
    const editPriceInput = document.getElementById('edit_price');
    const saveModalBtn = document.getElementById('save_modal_changes');

    function toggleSearchState() {
        const branchSelected = branchSelect && branchSelect.value.trim() !== '';
        const customerSelected = customerSelect && customerSelect.value.trim() !== '';

        if (branchSelected && customerSelected) {
            input.disabled = false;
            input.placeholder = "Please type product code or name...";
        } else {
            input.disabled = true;
            input.value = '';
            results.style.display = 'none';
            input.placeholder = "Please select Branch & Customer first...";
        }
    }

    if (branchSelect) branchSelect.addEventListener('change', toggleSearchState);
    if (customerSelect) customerSelect.addEventListener('change', toggleSearchState);

    if (window.jQuery) {
        window.jQuery('select[name="branch_id"], select[name="customer_id"]').on('change select2:select select2:clear', function() {
            toggleSearchState();
        });
    }

    toggleSearchState();

    // 1. Live Search Logic
    input.addEventListener('input', function () {
        const text = this.value.toLowerCase().trim();
        let matches = 0;

        if (text.length > 0) {
            searchItems.forEach(item => {
                const code = String(item.dataset.code || '').toLowerCase();
                const name = String(item.dataset.name || '').toLowerCase();

                if (code.includes(text) || name.includes(text)) {
                    item.style.setProperty('display', 'flex', 'important');
                    matches++;
                } else {
                    item.style.setProperty('display', 'none', 'important');
                }
            });

            results.style.display = matches > 0 ? 'block' : 'none';
        } else {
            results.style.display = 'none';
        }
    });

    // 2. Add Row on Search Click
    searchItems.forEach(item => {
        item.addEventListener('click', function () {
            const id = this.dataset.id;
            const code = this.dataset.code;
            const name = this.dataset.name;
            const price = parseFloat(this.dataset.price) || 0;

            input.value = '';
            results.style.display = 'none';

            const existingRow = document.getElementById(`row_item_${id}`);
            if (existingRow) {
                const qtyInput = existingRow.querySelector('.row-qty');
                qtyInput.value = parseInt(qtyInput.value || 0) + 1;
                calculateRowSubtotal(existingRow);
                return;
            }

            const tr = document.createElement('tr');
            tr.className = 'sale-row';
            tr.id = `row_item_${id}`;
            
            tr.innerHTML = `
                <td class="row-number text-center align-middle fw-bold text-muted"></td>
                <td class="align-middle">
                    <strong>[${code}]</strong> ${name}
                    <input type="hidden" name="items[${id}][product_id]" value="${id}">
                    <input type="hidden" name="items[${id}][subtotal]" class="row-subtotal-input" value="${price.toFixed(2)}">
                </td>
                <td class="align-middle">
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-edit-modal" title="Edit Details">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </button>
                        <input type="number" step="0.01" min="0" name="items[${id}][unit_price]" class="form-control text-end row-price" value="${price.toFixed(2)}" required>
                    </div>
                </td>
                <td class="align-middle">
                    <input type="number" name="items[${id}][quantity]" class="form-control text-center row-qty" value="1" min="1" required>
                </td>
                <td class="align-middle text-end row-subtotal fw-bold">
                    ${price.toFixed(2)}
                </td>
                <td class="text-center align-middle">
                    <button type="button" class="btn btn-outline-danger btn-sm delete-row">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
            `;

            entriesBody.appendChild(tr);
            recalculateRowNumbers();
            calculateGrandTotal();
        });
    });

    // 3. Event Delegation
    entriesBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('row-price') || e.target.classList.contains('row-qty')) {
            calculateRowSubtotal(e.target.closest('tr'));
        }
    });

    entriesBody.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.delete-row');
        if (deleteBtn) {
            deleteBtn.closest('tr').remove();
            recalculateRowNumbers();
            calculateGrandTotal();
            return;
        }

        const editBtn = e.target.closest('.btn-edit-modal');
        if (editBtn) {
            const row = editBtn.closest('tr');
            
            editRowIdInput.value = row.id;
            editQtyInput.value = row.querySelector('.row-qty').value;
            editPriceInput.value = row.querySelector('.row-price').value;

            editModal.show();
        }
    });

    // 4. Save Modal Changes
    saveModalBtn.addEventListener('click', function () {
        const rowId = editRowIdInput.value;
        const targetRow = document.getElementById(rowId);

        if (targetRow) {
            const newQty = parseInt(editQtyInput.value) || 1;
            const newPrice = parseFloat(editPriceInput.value) || 0;

            targetRow.querySelector('.row-qty').value = newQty;
            targetRow.querySelector('.row-price').value = newPrice.toFixed(2);

            calculateRowSubtotal(targetRow);
            editModal.hide();
        }
    });

    // 5. Close Dropdown on Outside Click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#product_search') && !e.target.closest('#autocomplete_results')) {
            results.style.display = 'none';
        }
    });

    function calculateRowSubtotal(row) {
        const qty = parseFloat(row.querySelector('.row-qty').value) || 0;
        const price = parseFloat(row.querySelector('.row-price').value) || 0;
        const subtotal = qty * price;
        
        row.querySelector('.row-subtotal').textContent = subtotal.toFixed(2);
        
        // Update hidden subtotal input
        const subtotalInput = row.querySelector('.row-subtotal-input');
        if (subtotalInput) {
            subtotalInput.value = subtotal.toFixed(2);
        }

        calculateGrandTotal();
    }

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.row-subtotal').forEach(cell => {
            total += parseFloat(cell.textContent) || 0;
        });
        grandTotalDisplay.textContent = total.toFixed(2);
    }

    function recalculateRowNumbers() {
        document.querySelectorAll('.row-number').forEach((cell, index) => {
            cell.textContent = index + 1;
        });
    }

    // 6. Block submit when the item table is empty
    document.getElementById('saleForm').addEventListener('submit', function (e) {
        if (entriesBody.querySelectorAll('tr').length === 0) {
            e.preventDefault();
            alert('Please add at least one product before saving.');
        }
    });

    // 7. Re-add the rows after a failed submit (withInput)
    const oldItems = @json(old('items', []));
    Object.keys(oldItems).forEach(function (pid) {
        const src = document.querySelector('.search-item[data-id="' + pid + '"]');
        if (!src) return;
        src.click();
        const row = document.getElementById('row_item_' + pid);
        if (!row) return;
        row.querySelector('.row-price').value = parseFloat(oldItems[pid].unit_price || oldItems[pid].price || 0).toFixed(2);
        row.querySelector('.row-qty').value = parseInt(oldItems[pid].quantity || 1);
        calculateRowSubtotal(row);
    });
});
</script>
@endsection