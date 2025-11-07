@extends('admin.layouts.main-layout')

@section('content')

<div class="view-container mb-2">
    <div class="card full-height-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex gap-2 align-items-center">
                <h3>Post List</h3>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <input type="text" class="form-control data-table-search" id="searchPost" placeholder="Search Post">
                <div class="vr mx-1"></div>
                <div class="text-right">
                    <a href="{{ route('admin.posts.create') }}" class="btn btn-sm thm-btn-bg thm-btn-text-color" id="createPostBtn">Create New Post</a>
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




            <table class="table table-bordered" id="postTable">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 10%;">ID</th>
                        <th class="text-center" style="width: 35%;">TITLE</th>
                        <th class="text-center" style="width: 20%;">CREATED ON</th>
                        <th class="text-center" style="width: 20%;">CREATED BY</th>
                        <th class="text-center" style="width: 15%;">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($posts as $post)
                    <tr>
                        <td class="text-center align-middle">{{ $post->id }}</td>
                        <td class="align-middle text-center">{{ $post->title }}</td>
                        <td class="text-center align-middle">
                            <div class="text-center align-middle">
                                {{ $post->formatedCreatedAt }}
                            </div>
                        </td>
                        <td class="text-center align-middle">{{ $post->creator?->name }}</td>
                        <td class="text-center align-middle">
                            <a href="{{ route('admin.posts.edit', ['post' => $post->id]) }}" class="btn btn-sm thm-btn-bg thm-btn-text-color">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>

                            <button data-id="{{ $post->id }}" class="btn btn-sm thm-btn-bg thm-btn-text-color delete-post">
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
@vite(['resources/js/post-script.js'])
<script>
let PostUrls = {
    'deletePost': "{{ route('admin.posts.destroy', ['post' => 'postId']) }}"
};

$(document).ready(function() {
    BxCoder.Datatable.initDataTable('#postTable', {
        order: [
            [0, 'desc']
        ],
        columns: [{
                type: 'num',
                orderable: true
            },
            {
                type: 'string',
                orderable: true
            },
            {
                type: 'string',
                orderable: true
            },
            {
                type: 'string',
                orderable: true
            },
            {
                type: 'string',
                orderable: false
            },
        ]
    });

    $("#searchPost").on("keyup search input paste cut", function() {
        BxCoder.Datatable.filter($(this).val());
    });

    $('#postTable').on("click", ".delete-post", function() {
        BxCoder.Datatable.selectRow(this);
        if (confirm("Are you sure you want to delete this post?")) {
            BxCoder.Post.deletePost($(this).data('id'));
        }
    });
});
</script>
@endsection