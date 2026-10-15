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
        min-height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .image-upload-wrapper:hover {
        border-color: #0d6efd;
        background-color: #f1f5f9;
    }

    /* Absolute file input overlay */
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
        z-index: 3;
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
        z-index: 4;
    }
</style>
@section('title', 'Edit Branch')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Edit Branch</h3>
    <a href="{{ route('branches.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Branch
    </a>
</div>

@if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            <strong>Please fix the errors below:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="{{ route('branches.update', $branch->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Branch Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Phone Number<span class="text-danger">*</span></label>
                    <input type="number" name="phone" class="form-control" value="{{ old('phone', $branch->phone) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Address<span class="text-danger">*</span></label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $branch->address) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Email<span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $branch->email) }}">
                </div>
                
               <div class="col-md-12">
                    <label for="logo" class="form-label fw-semibold">Branch Logo</label>
                    <div class="image-upload-wrapper">
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*" onchange="previewImage(event)">

                        <!-- Upload Placeholder -->
                        <div class="upload-content text-center {{ $branch->logo ? 'd-none' : '' }}" id="uploadContent">
                            <i class="fa-solid fa-cloud-arrow-up fs-2 text-primary mb-2"></i>
                            <h6 class="fw-bold mb-1">Click to upload or drag & drop</h6>
                            <p class="text-muted small mb-0">PNG, JPG, WEBP or GIF (Max: 2MB)</p>
                        </div>

                        <!-- Image Preview Container -->
                        <div class="image-preview-container {{ $branch->logo ? '' : 'd-none' }}" id="previewContainer">
                            <img id="imagePreview" src="{{ $branch->logo ? asset($branch->logo) : '#' }}" alt="Image Preview">
                            <button type="button" class="btn-remove" onclick="removeImage(event)">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </div>
                </div>
            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary px-4 fw-bold">
                    <i class="fa-solid fa-save me-1"></i> Update Product
                </button>
            </div>
        </form>
    </div>
</div>
<script>
    function previewImage(event) {
        const input = event.target;
        const uploadContent = document.getElementById('uploadContent');
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');

        if (input.files && input.files[0]) {
            const reader = new FileReader();

            reader.onload = function(e) {
                imagePreview.src = e.target.result;
                uploadContent.classList.add('d-none');
                previewContainer.classList.remove('d-none');
            };

            reader.readAsDataURL(input.files[0]);
        }
    }

    function removeImage(event) {
        event.stopPropagation(); // Stops file picker from re-opening
        
        const input = document.getElementById('logo');
        const uploadContent = document.getElementById('uploadContent');
        const previewContainer = document.getElementById('previewContainer');
        const imagePreview = document.getElementById('imagePreview');

        input.value = ''; // Clear selected file input
        imagePreview.src = '#';
        previewContainer.classList.add('d-none');
        uploadContent.classList.remove('d-none');
    }
</script>
@endsection