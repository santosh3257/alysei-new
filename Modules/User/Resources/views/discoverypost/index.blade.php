@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Discovery Posts</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Posts</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>

<section class="content">
    <div class="container-fluid">
        @if (\Session::has('success'))
              <div class="alert alert-success alert-dismissible fade show">
                  {!! \Session::get('success') !!}
              </div>
          @endif

          @if (\Session::has('error'))
              <div class="alert alert-danger alert-dismissible fade show">
                  {!! \Session::get('error') !!}
              </div>
          @endif
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Discovery Posts</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/discover-alysei/discovery-post/create')}}">
                        Add New
                    </a>
                </div>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();"
                                    class="allSelect"> All </th>
                            <th>Title</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Status</th>
                            <th>Category</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($discoverPosts)
                            @foreach($discoverPosts as $key=>$post)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$post->id}}"></td>
                                <td>
                                    {{ $post->title }}
                                </td>
                                <td>
                                {{ $post->email }}
                                </td>
                                <td>
                                +{{ $post->country_code }} {{ $post->phone_number }}
                                </td>
                                <td>
                                {{ $post->status == 1 ? 'active' : 'inactive' }}
                                </td>
                                <td>
                                    {{ $post->category->cat_name }}
                                </td>
                                <td>
                                <img src="{{ $post->attachment->base_url }}{{ $post->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/discover-alysei/discovery-post/edit', [$post->id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/discover-alysei/discovery-post/delete', [$post->id])}}"
                                        title="Delete"></a>

                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
            {{$discoverPosts->links()}}
            </div>
        </div>
    </div>
</section>
@endsection
