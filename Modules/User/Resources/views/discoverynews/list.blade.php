@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Discovery News</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">News</li>
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
                <h3 class="card-title">Discovery circle</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/discover-alysei/discovery-circle/add')}}">
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
                            <th>Name</th>
                            <th>Image</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($discoverynews)
                            @foreach($discoverynews as $key=>$new)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$new->discover_alysei_id}}"></td>
                                <td>
                                    {{ $new->title }}
                                </td>
                                <td>
                                    <img src="{{ $new->attachment->base_url }}{{ $new->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/discover-alysei/discovery-circle/edit', [$new->discover_alysei_id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/discover-alysei/discovery-circle/delete', [$new->discover_alysei_id])}}"
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
            {{$discoverynews->links()}}
            </div>
        </div>
    </div>
</section>
@endsection
