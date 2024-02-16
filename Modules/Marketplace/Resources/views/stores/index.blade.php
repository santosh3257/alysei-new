@extends('admin.layouts.app')

@section('content')

<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Marketplace Stores</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace')}}">Marketplace</a></li>
          <li class="breadcrumb-item active">Stores</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>

<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header">
        <h3>Stores</h3>
        
      <!-- /.card-header -->
      <div class="card-body">
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

        <form class="form-inline" method="get" action="{{url('dashboard/marketplace/stores')}}" >
          <div class="form-group mb-2">
            <input type="text"  class="form-control" id="StoreName" placeholder="Store Name" name="name" value="{{ isset($name) ? $name : '' }}">
          </div>
          <div class="form-group mx-sm-3 mb-2">
            <label for="inputPassword2" class="sr-only">Status</label>
            <select class="form-control" id="status" name="status">
              <option value="-1">Choose Status</option>
              <option value="0" {{ (isset($status) && $status == 0) ? "selected" : "" }}>Pending</option>
              <option value="1" {{ (isset($status) && $status == 1) ? "selected" : "" }}>Approved</option>
              <option value="2" {{ (isset($status) && $status == 2) ? "selected" : "" }}>Disabled</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary mb-2" name="search">Search</button>
          <a href="{{url('dashboard/marketplace/stores')}}" class="btn btn-primary mb-2 ml-2" name="clear">Clear</a>
        </form>

        <br />

        <table class="table table-bordered table-striped">
          <thead>
            <tr>
              <th style="width: 4.75em;"><input type="checkbox" name="" onclick="selectAll();" class="allSelect">  All </th>
              <th>Name</th>
              <th>Websites</th>
              <th>Phone</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
              @forelse($stores as $store)  
              <tr role="row">
                  <td ><input type="checkbox" name="" class="singleSelect" data-id="{{$store->marketplace_store_id}}"></td>
                  <td>                  
                    {{ $store->name }}
                  </td>
                  <td>
                    {{ $store->website }}
                  </td>
                  <td>
                    {{ $store->phone }}
                  </td>
                  <td>
                    <div class="form-group">
                      <select name="status" class="form-control store_status" data-id="{{$store->marketplace_store_id}}">
                        <option {{$store->status == 0 ? 'selected' : ''}} value="0">Pending</option>
                        <option {{$store->status == 1 ? 'selected' : ''}} value="1">Approved</option>
                        <option {{$store->status == 2 ? 'selected' : ''}} value="2">Disabled</option>
                      </select>
                    </div>
                  </td>
                  <td>
                      <a class="fa fa-eye" href="{{url('dashboard/marketplace/store/view', [$store->marketplace_store_id])}}" title="Edit"></a> |
                      <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/marketplace/store/delete', [$store->marketplace_store_id])}}"
                                        title="Delete"></a></td>
                      
                  </td>
              </tr>
              @empty
                <tr role="row">
                  <td colspan="6">                  
                    No Record Found
                  </td>
              </tr>
              @endforelse
          </tbody>
        </table>
      </div>
      <!-- /.card-body -->
      <div class="card-footer clearfix">
        {{$stores->appends($_GET)->links()}}
      </div>
    </div>
  </div>
</section>
@endsection            