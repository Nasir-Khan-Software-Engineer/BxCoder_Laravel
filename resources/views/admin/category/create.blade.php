@extends('admin.layouts.main-layout')

@section('content')
<div class="view-container">
    <div class="card full-height-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Create New Category</h3>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.categories.store') }}" method="POST">
                @csrf

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="form-group mb-3">
                    <label>Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control"
                        value="{{ old('name') }}" required>
                    @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" class="form-control"
                        value="{{ old('slug') }}" required>
                    <small class="text-muted">Example: web-development</small>
                    @error('slug') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Keywords</label>
                    <input type="text" name="keywords" class="form-control"
                        value="{{ old('keywords') }}" placeholder="E.g., beauty, haircare, nails">
                    <small class="text-muted">Separate multiple keywords using commas.</small>
                </div>

                <div class="form-group mb-3">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea>
                </div>

                <div class="text-right">
                    <button type="submit" class="btn thm-btn-bg thm-btn-text-color">
                        <i class="fa-solid fa-floppy-disk"></i> Save
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
@vite(['resources/js/category-script.js'])

<script>
    // Auto-generate slug from name
    $("input[name='name']").on("keyup", function() {
        let text = $(this).val().toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
        $("input[name='slug']").val(text);
    });
</script>
@endsection
