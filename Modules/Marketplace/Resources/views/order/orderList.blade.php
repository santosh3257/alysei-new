@extends('admin.layouts.app')

@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Orders</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item active">Orders</li>
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
                <form action="" method="get" id="searchFilter">
                    <div class="row">
                        <div class="col-md-4">
                                <label>Select Store</label>
                                @if(isset($_GET['page']))
                                <input type="hidden" name="page" value="{{$_GET['page']}}">
                                @endif
                                <div class="form-group">
                                    <select class="form-control submitForms" name="filter">
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
                        </div>
                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-6"><label>From Date</label><div class="form-group">
                                @if(isset($_GET['from']))
                                    <input class="form-control" type="date" name="from" value="{{$_GET['from']}}">
                                    @else
                                    <input class="form-control" type="date" name="from" value="">
                                @endif
                                </div></div>
                                <div class="col-md-6"><label>To Date</label><div class="form-group">
                                @if(isset($_GET['to']))
                                    <input class="form-control" type="date" name="to" value="{{$_GET['to']}}">
                                @else
                                    <input class="form-control" type="date" name="to" value="">
                                @endif
                                </div></div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                            <label>&nbsp;</label>
                                <button class="form-control" type="submit" name="">Filter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row card-body">
                    <div class="col-md-3"><div class="border-1">Sold Quantity : {{$soldQuantity}}</div></div>
                    <div class="col-md-3"><div class="border-1">Total Revenue : ${{number_format($totalSum,2)}}</div></div>
                    <div class="col-md-3"><div class="border-1">Total Orders : {{$totalOrder}}</div></div>
                    <div class="col-md-3"><div class="border-1">Total Stores : {{count($storeCount)}}</div></div>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th style="width: 4.75em;"># </th>
                            <th>ID</th>
                            <th>Product Name</th>
                            <th>Store name</th>
                            <th>Quantity</th>
                            <th>Paid Amount</th>
                            <th>O. Status</th>
                            <th>P. Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($orders)
                            @foreach($orders as $key=>$order)
                            <tr role="row">
                                <td><input type="checkbox" name="" class="singleSelect" data-id=""></td>
                                <td>{{$order->order_id}}</td>
                                <td>
                                    <?php 
                                    $title = '';
                                    if($order->productItemInfo){
                                        foreach($order->productItemInfo as $key=>$product){
                                            
                                            if($product->productInfo){
                                                $title .= $product->productInfo->title.', ';
                                            }
                                        }
                                    }
                                    ?>
                                    {{rtrim($title,", ")}}
                                </td>
                                <td>{{$order->getStore->name}}</td>
                                <td>{{$order->num_items_sold}}</td>
                                <td>{{$order->currency.$order->net_total}}</td>
                                <td>{{$order->status}}</td>
                                <td>@if($order->transactionInfo) {{$order->transactionInfo->status}} @endif</td>
                                <td style="text-align:center;">
                                    <a class="fa fa-eye" href="{{url('dashboard/marketplace/order/view', [$order->order_id])}}"
                                        title="View"></a>

                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
               


            </div>
            <!-- /.card-body -->
            <div class="card-footer clearfix">
                {{$orders->appends($_GET)->links()}}
            </div>
        </div>
    </div>
</section>
@endsection
