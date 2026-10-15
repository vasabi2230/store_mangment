@extends('layouts.app')

@section('title', 'Add Unit - Store Manager')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold text-dark">Add New Unit</h3>
    <a href="{{ route('units.index') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i> Back to Units
    </a>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card p-4">
            <form action="{{ route('units.store') }}" method="POST">
                @csrf
                <div class="row">
                <div class="col-6 mb-3">
                    <label for="name" class="form-label fw-semibold">Unit Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="e.g. Pcs, Box, Kg, Set" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-6 mb-3">
                    <label for="description" class="form-label fw-semibold">Description</label>
                    <input type="text" class="form-control" id="description" name="description" value="{{old('description')}}" placeholder="e.g. Use For.....">
                </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save me-1"></i> Save Unit
                    </button>
                    <a href="{{ route('units.index') }}" class="btn btn-light border">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection