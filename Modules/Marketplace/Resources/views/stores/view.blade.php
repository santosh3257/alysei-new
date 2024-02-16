@extends('admin.layouts.app')

@section('content')
<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>View Marketplace Store</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace')}}">Marketplace</a></li>
                    <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace/stores')}}">Stores</a></li>
                    <li class="breadcrumb-item active">View</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>


<section class="content">
    <div class="container-fluid">
        <div class="col-md-12">
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

            <!-- general form elements -->
            <div class="card card-primary">

                <div class="card-header">
                    <h3 class="card-title">View Store</h3>
                </div>

                <!-- /.card-header -->
                <!-- form start -->
                <form method="post" action="{{url('dashboard/marketplace/store/approve',['id'=>$id])}}"
                    enctype='multipart/form-data'>
                    {{ csrf_field() }}
                    <div class="card-body">

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="name" name="name" min="3" max="50"
                                value="{{ $store->name }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="exampleFormControlTextarea1">Description</label>
                            <textarea class="form-control" id="exampleFormControlTextarea1" rows="3"
                                disabled>{{ $store->description}}</textarea>
                        </div>

                        <div class="form-group">
                            <label for="name">Website</label>
                            <input type="text" class="form-control" id="name" name="name" min="3" max="50"
                                value="{{ $store->website }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="name">Phone</label>
                            <input type="text" class="form-control" id="name" name="name" min="3" max="50"
                                value="{{ $store->phone }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="name">Location</label>
                            <input type="text" class="form-control" id="name" name="name" min="3" max="50"
                                value="{{ $store->location }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="name">Latitude</label>
                            <input type="text" class="form-control" id="name" name="name" min="3" max="50"
                                value="{{ $store->lattitude }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="name">Longitude</label>
                            <input type="text" class="form-control" id="name" name="name" min="3" max="50"
                                value="{{ $store->longitude }}" disabled>
                        </div>

                        <div class="form-group">
                            <label for="name">Logo Image</label>
                            <img src="{{ $store->logo->base_url }}{{ $store->logo->attachment_url }}" width="75px">
                        </div>

                        <div class="form-group">
                            <label for="name">Banner Image</label>
                            <img src="{{ $store->banner->base_url }}{{ $store->banner->attachment_url }}" width="75px">
                        </div>

                        <div class="form-group">
                            <label for="name">Status</label>
                            @switch($store->status)
                            @case('1')
                            Approved
                            @break

                            @case('2')
                            Disabled
                            @break

                            @default
                            Pending
                            @endswitch
                        </div>
                        <div class="card-footer">
                          @if($store->status == '0' )
                          <button type="submit" class="btn btn-primary" name="approved">Approve</button>
                          @endif
                      </div>
                        
                    </div>
                    <!-- /.card-body -->

                    
                </form>
                <div class="card-body">
                @if($store->firstProduct)
                        <h4>1st uploaded Product</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Title</label>
                                    <input type="text" class="form-control" id="title" placeholder="Enter Title"
                                        name="title" min="3" max="50" value="{{$store->firstProduct->title}}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="state">Discription</label>
                                    <textarea class="form-control">{{$store->firstProduct->description}}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Quantity Available</label>
                                    <input type="text" value="{{$store->firstProduct->quantity_available}}" class="form-control">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Min order quantity</label>
                                    <input type="text" value="{{$store->firstProduct->min_order_quantity}}" class="form-control">

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Handling instruction</label>
                                    <textarea class="form-control">{{$store->firstProduct->handling_instruction}}</textarea>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Dispatch instruction</label>
                                    <textarea class="form-control">{{$store->firstProduct->dispatch_instruction}}</textarea>

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Available for sample</label>
                                    <input type="text" value="{{$store->firstProduct->available_for_sample}}" class="form-control">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Product price</label>
                                    <input type="text" value="{{$store->firstProduct->product_price}}" class="form-control">

                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Unit</label>
                                    <input type="text" value="{{$store->firstProduct->unit}}" class="form-control">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state">Status</label>
                                    @if(empty($store->firstProduct->deleted_at))
                                    <input type="text" value="{{$store->firstProduct->status == 0 ? 'Inactive' : 'Active'}}"
                                        class="form-control">
                                    @else
                                    <input type="text" value="deleted"
                                        class="form-control">
                                    @endif

                                </div>
                            </div>
                        </div>
                        <div class="row">
                        @if($store->firstProduct->product_gallery)
                          @foreach($store->firstProduct->product_gallery as $key=>$gallery)
                          <div class="col-md-3">
                            <div class="form-group">
                            <img src="{{ $gallery->base_url }}{{ $gallery->attachment_url }}" width="100%">
                            </div>
                          </div>
                          @endforeach
                        @endif
                        </div>
                @else
                <h4>1st Product not found</h4>
                @endif
                </div>
            </div>
            <!-- /.card -->
        </div>
    </div>
</section>
@endsection