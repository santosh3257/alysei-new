@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Add Hub Info Icon</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/hub-infoicon')}}">Hub Info Icon</a></li>
          <li class="breadcrumb-item active">Edit Hub Info Icon</li>
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
                <h3 class="card-title">Add Hub Info Icon</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/hub-infoicon/update',$data->id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                
                  <div class="form-group">
                    <label for="state">Role</label>
                    <select class="form-control" id="type" name="role_id" required>
                       <option value="">Select role</option>
                       @if($roles)
                        @foreach($roles as $key=>$role)
                          <option {{$data->role_id == $role->role_id ? 'selected' : ''}}  value="{{$role->role_id}}" required>{{$role->name}}</option>
                        @endforeach
                       @endif
                    </select>
                  </div>
                  <div class="row">
                      <div class="col-md-6">
                          <div class="form-group">
                            <label>Message En:</label>
                            <textarea rows="4" cols="50" name="message_en" required>{{$data->message_en}}</textarea>
                          </div>
                      </div>
                      <div class="col-md-6">
                          <div class="form-group">
                            <label>Message It:</label>
                             <textarea rows="4" cols="50" name="message_it" required>{{$data->message_it}}</textarea> 
                          </div>
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

