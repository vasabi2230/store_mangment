@extends('layouts.app')
<style>
    .image-upload-wrapper {
    position: relative;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 30px 20px;
    background-color: #f8fafc;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-upload-wrapper:hover {
    border-color: #0d6efd;
    background-color: #f1f5f9;
}

/* Hide the actual file input over the box */
.image-upload-wrapper input[type="file"] {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    cursor: pointer;
    z-index: 2;
}

/* Preview Styles */
.image-preview-container {
    position: relative;
    z-index: 3; /* Render above file input */
}

.image-preview-container img {
    max-height: 140px;
    border-radius: 8px;
    object-fit: cover;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.btn-remove {
    position: absolute;
    top: -10px;
    right: -10px;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 26px;
    height: 26px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
}
</style>
@section('title', 'Add Product - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Add New Product</h3>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Products
    </a>
</div>

<div class="card p-4">
    <!-- Notice enctype="multipart/form-data" added below -->
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row g-3">
            <div class="col-md-6">
                <label for="code" class="form-label fw-semibold">Product Code / Barcode <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="code" name="code" placeholder="e.g. PRD-1001" required>
            </div>

            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Product Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" placeholder="e.g. MacBook Pro 16 Inch" required>
            </div>

            <div class="col-md-6">
                <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                <select class="form-select js-example-basic-single" id="category_id" name="category_id" required>
                    <option value="">-- Select Category --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6">
                <label for="unit_id" class="form-label fw-semibold">Unit of Measure <span class="text-danger">*</span></label>
                <select class="form-select js-example-basic-single" id="unit_id" name="unit_id" required>
                    <option value="">-- Select Unit --</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-4">
                <label for="cost" class="form-label fw-semibold">Cost Price ($) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="cost" name="cost" placeholder="0.00" required>
                </div>
            </div>

            <div class="col-md-4">
                <label for="price" class="form-label fw-semibold">Selling Price ($) <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="price" name="price" placeholder="0.00" required>
                </div>
            </div>

            <div class="col-md-4">
                <label for="quantity" class="form-label fw-semibold">Initial Stock Quantity <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="quantity" name="quantity" placeholder="0" required>
            </div>
            {{-- <div class="col-md-12">
                <label for="image" class="form-label fw-semibold">Product Image</label>
                <input type="file" class="form-control" id="image" name="image" accept="image/*">
            </div> --}}
             <div class="col-md-12">
                <label for="image" class="form-label fw-semibold">Product Image</label>
                
                <div class="image-upload-wrapper">
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(event)">
                    <div class="upload-content text-center" id="uploadContent">
                        <i class="fa-solid fa-cloud-arrow-up fs-2 text-primary mb-2"></i>
                        <h6 class="fw-bold mb-1">Click to upload or drag & drop</h6>
                        <p class="text-muted small mb-0">PNG, JPG, WEBP or GIF (Max: 2MB)</p>
                    </div>
                    <!-- Image Preview Container -->
                    <div class="image-preview-container d-none" id="previewContainer">
                        <img id="imagePreview" src="#" alt="Image Preview">
                        <button type="button" class="btn-remove" style="z-index: 999" onclick="removeImage()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <label for="description" class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                <textarea type="text" name="descritpion" id="description" class="form-control" style="height: 100px" ></textarea>
            </div>
        </div>

        <hr class="my-4">

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check me-1"></i> Save Product
            </button>
            <a href="{{ route('products.index') }}" class="btn btn-light border">Cancel</a>
        </div>
    </form>
</div>
<script>
function previewImage(event) {
    const input = event.target;
    const uploadContent = document.getElementById('uploadContent');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('imagePreview');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImage.src = e.target.result;
            uploadContent.classList.add('d-none');
            previewContainer.classList.remove('d-none');
        }

        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage() {
    const input = document.getElementById('image'); // Updated to target 'logo'
    const uploadContent = document.getElementById('uploadContent');
    const previewContainer = document.getElementById('previewContainer');
    const previewImage = document.getElementById('imagePreview');

    if (input) {
        input.value = ''; // Clears the selected file
    }
    
    previewImage.src = '#';
    previewContainer.classList.add('d-none');
    uploadContent.classList.remove('d-none');
}
</script>
@endsection