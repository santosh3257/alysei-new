@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>View Product</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace/products')}}">Products</a></li>
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
                <h3 class="card-title">View Product</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/marketplace/product/update',$product->marketplace_banner_id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="name">Title</label>
                          <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" min="3" max="50" value="{{$product->title}}">
                        </div>
                    </div>
                  </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">User</label>
                      <input type="text" value="{{$product->user->name}}" class="form-control">
                      
                    </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                      <label for="state">Store</label>
                      <input type="text" value="{{$product->store->name}}" class="form-control">
                      
                    </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="state">Discription</label>
                      <textarea class="form-control">{{$product->description}}</textarea>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Quantity Available</label>
                      <input type="text" value="{{$product->quantity_available}}" class="form-control">
                      
                    </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                      <label for="state">Min order quantity</label>
                      <input type="text" value="{{$product->min_order_quantity}}" class="form-control">
                      
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Handling instruction</label>
                      <textarea class="form-control">{{$product->handling_instruction}}</textarea>
                      
                    </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                      <label for="state">Dispatch instruction</label>
                      <textarea class="form-control">{{$product->dispatch_instruction}}</textarea>
                      
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Available for sample</label>
                      <input type="text" value="{{$product->available_for_sample}}" class="form-control">
                      
                    </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                      <label for="state">Product price</label>
                      <input type="text" value="{{$product->product_price}}" class="form-control">
                      
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Unit</label>
                      <input type="text" value="{{$product->unit}}" class="form-control">
                      
                    </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                      <label for="state">Status</label>
                      <input type="text" value="{{$product->status == 0 ? 'Inactive' : 'Active'}}" class="form-control">
                      
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Product Category</label>
                      <input type="text" value="" class="form-control">
                      
                    </div>
                  </div>
                  
                </div>
                <div class="row">
                @if($product->product_gallery)
                  @foreach($product->product_gallery as $key=>$gallery)
                  <div class="col-md-3">
                    <div class="form-group">
                    <img src="{{ $gallery->base_url }}{{ $gallery->attachment_url }}" width="100%">
                    </div>
                  </div>
                  @endforeach
                @endif
                </div>
                <!-- <div class="row">

                  <div class="col-md-3">
                    <div class="form-group">
                    </div>
                  </div>
                </div> -->

              </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <!-- <button type="submit" class="btn btn-primary">Update</button> -->
                </div>
              </form>
            </div>
            <!-- /.card -->
      </div>
  </div>
</section>
@endsection            

