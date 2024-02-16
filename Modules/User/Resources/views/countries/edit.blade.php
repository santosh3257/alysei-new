@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Update Country</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users/countries')}}">Countries</a></li>
          <li class="breadcrumb-item active">Update</li>
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
                <h3 class="card-title">Update Country</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/user/country/update',$country->id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                <div class="form-group">
                        <label for="name">Country Name</label>
                        <input type="text" class="form-control" id="title" placeholder="Country Name" name="country_name" min="3" max="50" value=" {{ $country->name}}" required>
                  </div>
                  <div class="form-group">
                        <label for="name">Status</label>
                        <select class="form-control" name="country_status" id="countryStatus">
                            <option hidden>Choose Country Status</option>
                            <option value="1" {{$country->status == 1 ? 'selected' : ''}}>Active</option>
                            <option value="0" {{$country->status == 0 ? 'selected' : ''}}>Inactive</option>
                        </select>
                  </div>
                  <div class="form-group">
                    <label for="IngredientsImage">Country Flag</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg">
                        <label class="custom-file-label" for="CountryImage">Choose file</label>
                      </div>
                    </div>
                    <img src="{{ $country->flagImg->base_url }}{{ $country->flagImg->attachment_url }}" width="75px">
                  </div>
              </div>
                <!-- /.card-body -->
                <input type="hidden" name="prv_img" value="{{ $country->flagImg->attachment_url }}">
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

