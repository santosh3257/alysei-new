@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Recipe Quantity</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe/unit-quantity')}}">Quantity</a></li>
          <li class="breadcrumb-item active">Edit</li>
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
                <h3 class="card-title">Edit Quantity</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/recipe/unit-quantity/update',['id'=>$quantity->id])}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Quantity Name(English)</label>
                        <input type="text" class="form-control" id="title" placeholder="Enter Name" name="name_en" min="3" max="50" value="{{$quantity->name_en}}" required>
                    </div>
                    <div class="form-group">
                        <label for="name">Quantity Name(Italian)</label>
                        <input type="text" class="form-control" id="title" placeholder="Enter Name" name="name_it" min="3" max="50" value="{{$quantity->name_it}}" required>
                    </div>
                    <div class="form-group">
                        <label for="state">Status</label>
                        <select class="form-control" name="status">
                            <option value="publish" {{ $quantity->status == 'publish' ? 'selected' : '' }}>Publish</option>
                            <option value="draft" {{ $quantity->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>

                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Submit</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
      </div>
  </div>
</section>
@endsection            

  