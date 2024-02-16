@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Alysei Marketplace Walkthrough</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Marketplace Walkthrough</li>
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
                <h3 class="card-title">Marketplace Walkthrough</h3>
                <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/market-place/walkthrough/add')}}">
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
                                    <option value="">All</option>
                                    @if(isset($_GET['filter']))
                                    <option {{$_GET['filter'] == 'alysei' ? 'selected' : ''}} value="alysei">Alysei</option>
                                    @else
                                    <option value="alysei">Alysei</option>
                                    @endif
                                    @if($roles)
                                        @foreach($roles as $key=>$role)
                                            @if(isset($_GET['filter']))
                                            <option {{$_GET['filter'] == $role->role_id ? 'selected' : ''}} value="{{$role->role_id}}">{{$role->name}}</option>
                                            @else
                                            <option value="{{$role->role_id}}">{{$role->name}}</option>
                                            @endif
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </form>
                </div>
                <div style="float:right;position: relative;top: 8px;right: 10px;">
                    <div class="form-group">
                        <label>Select Role</label>
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
                        @if($walkthroughs)
                            @foreach($walkthroughs as $key=>$walkthrough)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$walkthrough->walk_through_screen_id}}"></td>
                                <td>
                                    {{ $walkthrough->title_en }}
                                </td>
                                <td>
                                    <img src="{{ $walkthrough->attachment->base_url }}{{ $walkthrough->attachment->attachment_url }}"
                                        width="50px">
                                </td>
                                <td>
                                    <a class="fa fa-edit" href="{{url('dashboard/market-place/walkthrough/edit', [$walkthrough->walk_through_screen_id])}}"
                                        title="Edit"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/market-place/walkthrough/delete', [$walkthrough->walk_through_screen_id])}}"
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
                {{$walkthroughs->links()}}
            </div>
        </div>
    </div>
</section>
@endsection
