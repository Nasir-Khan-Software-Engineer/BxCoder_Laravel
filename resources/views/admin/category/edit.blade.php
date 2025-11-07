@extends('admin.layouts.main-layout')

@section('content')
<div class="view-container">
    <div class="card full-height-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Edit Category</h3>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">

                @csrf
                @method('PUT')

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-group mb-3">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                        value="{{ old('name', $category->name) }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control"
                        value="{{ old('slug', $category->slug) }}" required>
                    <small class="text-muted">Slug should be unique (e.g. web-development)</small>
                    @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Keywords</label>
                    <input type="text" name="keywords" class="form-control"
                        value="{{ old('keywords', $category->keywords) }}">
                    <small class="text-muted">Separate keywords with commas</small>
                </div>

                <div class="form-group mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description', $category->description) }}</textarea>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn thm-btn-bg thm-btn-text-color">
                        <i class="fa-solid fa-floppy-disk"></i> Update Category
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection
