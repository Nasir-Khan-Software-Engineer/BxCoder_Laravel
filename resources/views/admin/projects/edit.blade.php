@extends('admin.layouts.main-layout')

@section('content')
<div class="view-container">
    <div class="card full-height-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Edit Project</h3>
            <a href="{{ route('admin.projects.index') }}" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-arrow-left"></i> Back to List
            </a>
        </div>

        <div class="card-body">
            <form action="{{ route('admin.projects.update', $project->id) }}" method="POST">

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif


                @csrf
                @method('PUT')

                <div class="form-group mb-3">
                    <label>Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $project->title) }}" required>
                    @error('title') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Categories <span class="text-danger">*</span></label>
                    <select name="categories[]" class="form-control" multiple required>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ in_array($category->id, old('categories', $project->categories->pluck('id')->toArray())) ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted">You can select multiple categories</small>
                    @error('categories') <small class="text-danger">{{ $message }}</small> @enderror
                </div>

                <div class="form-group mb-3">
                    <label>Keywords</label>
                    <input type="text" name="keywords" class="form-control" value="{{ old('keywords', $project->keywords) }}">
                </div>

                <div class="form-group mb-3">
                    <label>Short Description</label>
                    <textarea name="short_description" class="form-control" rows="3">{{ old('short_description', $project->short_description) }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label>Details</label>
                    <textarea name="details" class="form-control" rows="6">{{ old('details', $project->details) }}</textarea>
                </div>

                <div class="form-group mb-3">
                    <label>Source Code Link</label>
                    <input type="text" name="source_code_link" class="form-control" value="{{ old('source_code_link', $project->source_code_link) }}">
                </div>

                <div class="form-group mb-3">
                    <label>Video Link</label>
                    <input type="text" name="video_link" class="form-control" value="{{ old('video_link', $project->video_link) }}">
                </div>

                <div class="form-group mb-3">
                    <label>Documentation Link</label>
                    <input type="text" name="documentation_link" class="form-control" value="{{ old('documentation_link', $project->documentation_link) }}">
                </div>

                <div class="text-right">
                    <button type="submit" class="btn thm-btn-bg thm-btn-text-color">
                        <i class="fa-solid fa-floppy-disk"></i> Update Project
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection