@extends('admin.layouts.main-layout')

@section('content')

<div class="view-container mb-2">
    <div class="card full-height-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2 align-items-center">
                <h3>Category List</h3>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control data-table-search" id="searchCategory" placeholder="Search Category">
                <div class="vr mx-1"></div>
                <div class="text-right">
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-sm thm-btn-bg thm-btn-text-color" id="createCategoryBtn">Create New Category</a>
                </div>
            </div>
        </div>

        <div class="card-body p-1">

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif

            <table class="table table-bordered" id="categoryTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 8%;">ID</th>
                        <th class="text-center" style="width: 20%;">Name</th>
                        <th class="text-center" style="width: 20%;">Slug</th>
                        <th class="text-center" style="width: 20%;">Keywords</th>
                        <th class="text-center" style="width: 27%;">Description</th>
                        <th class="text-center" style="width: 20%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td class="text-center align-middle">{{ $category->id }}</td>
                        <td class="text-center align-middle">{{ $category->name }}</td>
                        <td class="text-center align-middle">{{ $category->slug }}</td>
                        <td class="text-center align-middle">{{ $category->keywords }}</td>
                        <td class="align-middle">{{ Str::limit($category->description, 50) }}</td>
                        <td class="text-center align-middle">
                            <a href="{{ route('admin.categories.edit', ['category' => $category->id]) }}" class="btn btn-sm thm-btn-bg thm-btn-text-color">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <button data-id="{{ $category->id }}" class="btn btn-sm thm-btn-bg thm-btn-text-color delete-category">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

@endsection

@section('script')
@vite(['resources/js/category-script.js'])
<script>

let CategoryUrls = {
    'deleteCategory': "{{ route('admin.categories.destroy', ['category' => 'categoryId']) }}"
};

$(document).ready(function() {
    BxCoder.Datatable.initDataTable('#categoryTable', {
        order: [[0, 'desc']]
    });

    $("#searchCategory").on("keyup search input paste cut", function() {
        BxCoder.Datatable.filter($(this).val());
    });

    $('#categoryTable').on("click", ".delete-category", function() {
        BxCoder.Datatable.selectRow(this);
        if (confirm("Are you sure you want to delete this category?")) {
            BxCoder.Category.deleteCategory($(this).data('id'));
        }
    });
});
</script>
@endsection
