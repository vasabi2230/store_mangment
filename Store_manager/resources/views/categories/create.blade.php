@extends('layouts.app')

@section('title', 'Add Category - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Add New Category</h3>
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Categories
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="code" class="form-label fw-semibold">Category Code</label>
                    <input type="text" class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code') }}" placeholder="e.g. #.......">
                    @error('code')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Laptops & Computers" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="e.g. All kinds of laptops and computer accessories">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save me-1"></i> Save Category
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection