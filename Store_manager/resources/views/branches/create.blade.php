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
@section('title', 'Add Branch - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Add New Branch</h3>
    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Branch
    </a>
</div>

<div class="card p-4">
    <!-- Notice enctype="multipart/form-data" added below -->
    <form action="{{ route('branches.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Input branch name here..." required>
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label fw-semibold">Phone Number<span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="phone" name="phone" placeholder="e.g. 011112222" required>
            </div>
            <div class="col-md-6">
                <label for="address" class="form-label fw-semibold">Address<span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="address" name="address" placeholder="Input address here..." required>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold">Email<span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" placeholder="e.g. you@gmail.com">
            </div>

           <div class="col-md-12">
                <label for="logo" class="form-label fw-semibold">Branch Logo</label>
                
                <div class="image-upload-wrapper">
                    <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previewImage(event)">
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
        </div>

        <hr class="my-4">

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fa-solid fa-check me-1"></i> Save Product
            </button>
            <a href="{{ route('branches.index') }}" class="btn btn-light border">Cancel</a>
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
    const input = document.getElementById('logo'); // Updated to target 'logo'
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