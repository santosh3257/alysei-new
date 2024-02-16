@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Marketplace Banners</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Banners</li>
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
                <h3 class="card-title">Banner</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/marketplace/banner/add')}}">
                        Add New
                    </a>
                </div>
                <div style="float:right;">
                        <form action="" method="get" id="searchFilter">
                            @if(isset($_GET['page']))
                            <input type="hidden" name="page" value="{{$_GET['page']}}">
                            @endif
                            <div class="form-group">
                                <select class="form-control submitForm" name="filter">
                                @if(isset($_GET['filter']))
                                    <option {{$_GET['filter'] == 'all' ? 'selected' : ''}} value="all">All</option>
                                    <option {{$_GET['filter'] == '1' ? 'selected' : ''}} value="1">Top</option>
                                    <option {{$_GET['filter'] == '2' ? 'selected' : ''}} value="2">Bottom</option>
                                @else
                                    <option value="all">All</option>
                                    <option value="1">Top</option>
                                    <option value="2">Bottom</option>
                                @endif
                                </select>
                            </div>
                        </form>
                </div>
                <div style="float:right;position: relative;top: 8px;right: 10px;">
                    <div class="form-group">
                        <label>Select Position</label>
                    </div>
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
                        @if($banners)
                            @foreach($banners as $key=>$banner)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$banner->marketplace_banner_id}}"></td>
                                <td>
                                    {{ $banner->title }}
                                </td>
                                <td>
                                    @if($banner->attachment)
                                    <img src="{{ $banner->attachment->base_url }}{{ $banner->attachment->attachment_url }}"
                                        width="50px">
                                    @endif
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/marketplace/banner/edit', [$banner->marketplace_banner_id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/marketplace/banner/delete', [$banner->marketplace_banner_id])}}"
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
                {{$banners->links()}}
            </div>
        </div>
    </div>
</section>
@endsection
