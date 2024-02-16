@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Marketplace Region</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace')}}">Marketplace</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace/regions')}}">Regions</a></li>
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
                <h3 class="card-title">Edit region</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/marketplace/region/update',['id'=>$id])}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">

                  <div class="form-group cousines_list">
                    <label>Select Parent</label>
                    <select class="form-control" name="cousin_id">
                      @foreach($cousines as $cousine)
                        <option value="{{ $cousine->cousin_id }}" {{ ($cousine->cousin_id == $region->cousin_id) ? 'selected': '' }}>
                          {{ $cousine->name }}
                        </option>
                      @endforeach
                    </select>
                  </div>
                  <div class="row">
                    <div class="col-md-12">
                      <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name" min="3" max="50" value="{{ $region->name }}" required>
                      </div>
                    </div>
                    <!-- <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Name(Italian)</label>
                          <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name_it" min="3" max="50" value="{{ $region->name_it }}" required>
                        </div>
                      </div> -->
                  </div>
                  <div class="form-group">
                    <label for="regionsImage">region Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" >
                        <label class="custom-file-label" for="regionsImage">Choose file</label>
                      </div>
                    </div>
                    <div>
                      <img src="{{ $region->attachment->base_url }}{{ $region->attachment->attachment_url }}" width="75px">
                    </div>
                  </div>
                
                </div>
                <!-- /.card-body -->

                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Update</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
      </div>
  </div>
</section>
@endsection            

  