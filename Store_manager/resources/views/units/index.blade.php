@extends('layouts.app')

@section('title', 'Units - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 shadow-sm border-0 p-2">
    <h3 class="fw-bold text-dark">Unit Management</h3>
    <a href="{{ route('units.create') }}" class="btn btn-secondary">
        <i class="fa-solid fa-plus me-1"></i> Add Unit
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Unit Name</th>
                        <th>Description</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($units as $unit)
                        <tr>
                            <td class="ps-4 font-monospace">{{ $unit->id }}</td>
                            <td class="fw-semibold">{{ $unit->name }}</td>
                            <td>{{ $unit->description ?? 'N/A' }}</td>
                            <td class="text-end pe-4">
                                <button type="button" 
                                        class="edit-unit-btn btn btn-sm btn-outline-secondary me-1"
                                        data-id="{{ $unit->id }}"
                                        data-name="{{ addslashes($unit->name) }}"
                                        data-description="{{ addslashes($unit->description ?? '') }}">
                                    <i class="fa-solid fa-pen "></i>
                                </button>
                                <button type="button" 
                                        class=" btn btn-sm btn-outline-danger"
                                        onclick="confirmDelete({{ $unit->id }}, '{{ addslashes($unit->name) }}')">
                                    <i class="fa-solid fa-trash "></i>
                                </button>
                        </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No units found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-0">Are you sure you want to delete unit <strong id="deleteUnitName"></strong>?</p>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <form id="deleteUnitForm" action="" method="POST">
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
<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Edit Unit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUnitForm" action="" method="POST">
                @csrf
                @method('PUT')   
                <div class="p-4">
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
                            <input type="text" class="form-control" id="modalNewName" name="name" placeholder="Enter new Unit name...">
                        </div>
                    </div>

                    <div>
                        <label for="modalDescription" class="form-label fw-semibold">Description</label>
                        <textarea class="form-control" id="modalDescription" name="description" rows="3" placeholder="Enter unit description..."></textarea>
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

<script>
    function confirmDelete(id, name) {
    document.getElementById('deleteUnitName').textContent = '"' + name + '"';
    const deleteForm = document.getElementById('deleteUnitForm');
    deleteForm.action = '/units/' + id;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    deleteModal.show();
}
document.addEventListener('DOMContentLoaded', function () {
    const editModalElement = document.getElementById('editUnitModal');
    if (!editModalElement) return;

    const editModal = new bootstrap.Modal(editModalElement);
    const editForm = document.getElementById('editUnitForm');
    const originalNameInput = document.getElementById('modalOriginalName');
    const newNameInput = document.getElementById('modalNewName');
    const descriptionInput = document.getElementById('modalDescription');

    document.querySelectorAll('.edit-unit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const unitId = this.dataset.id;
            const unitName = this.dataset.name;
            const unitDescription = this.dataset.description;

            editForm.action = '/units/' + unitId;
            originalNameInput.value = unitName;    
            newNameInput.value = unitName;   
            descriptionInput.value = unitDescription;
            editModal.show();
        });
    });
});
</script>
@endsection