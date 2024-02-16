@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Products</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Products</li>
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
                <h3 class="card-title">Products</h3>
                <!-- <div style="float:right;">
                    <a class="nav-link" href="{{url('dashboard/walkthrough/add')}}">
                        Add New
                    </a>
                </div> -->
                <div style="float:right;">
                        <form action="" method="get" id="searchFilter">
                            @if(isset($_GET['page']))
                            <input type="hidden" name="page" value="{{$_GET['page']}}">
                            @endif
                            <div class="form-group">
                                <select class="form-control submitForm" name="filter">
                                    <option value="">All</option>
                                @if($stores)
                                    @foreach($stores as $key=>$store)
                                        @if(isset($_GET['filter']))
                                            <option {{$_GET['filter'] == $store->marketplace_store_id ? 'selected' : ''}} value="{{$store->marketplace_store_id}}">{{$store->name}}</option>
                                        @else
                                            <option value="{{$store->marketplace_store_id}}">{{$store->name}}</option>
                                        @endif
                                    @endforeach
                                @endif
                                </select>
                            </div>
                        </form>
                </div>
                <div style="float:right;position: relative;top: 8px;right: 10px;">
                    <div class="form-group">
                        <label>Select Store</label>
                    </div>
                </div>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"># </th>
                            <th>Product name</th>
                            <th>Store name</th>
                            <th>Available for simple</th>
                            <th>Product price</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($products)
                            @foreach($products as $key=>$product)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id="{{$product->marketplace_product_id}}"></td>
                                <td>
                                    {{ $product->title }}
                                </td>
                                <td> {{ $product->store->name }}</td>
                                <td>{{ $product->available_for_sample }}</td>
                                <td>${{$product->product_price}}</td>
                                <td>
                                    <div class="form-group">
                                        <select name="status" class="form-control product_status" data-id="{{$product->marketplace_product_id}}">
                                            <option {{$product->status == 0 ? 'selected' : ''}} value="0">Inactive</option>
                                            <option {{$product->status == 1 ? 'selected' : ''}} value="1">Active</option>
                                        </select>
                                        </div>
                                    </td>
                                <td>
                                    <a class="fa fa-eye" href="{{url('dashboard/marketplace/product/view', [$product->marketplace_product_id])}}"
                                        title="View"></a> |
                                    <a onclick="return confirm('Are you sure? You want to delete it.')" class="fa fa-trash" href="{{url('dashboard/marketplace/product/delete', [$product->marketplace_product_id])}}"
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
                {{$products->appends($_GET)->links()}}
            </div>
        </div>
    </div>
</section>
@endsection
