@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Add Role</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users/roles')}}">Roles</a></li>
          <li class="breadcrumb-item active">Add</li>
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
                <h3 class="card-title">Add Role</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/user/role/store')}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="title" placeholder="Enter Name" name="name" min="3" max="50" required>
                  </div>
                  <div class="form-group">
                        <label for="name">Display name</label>
                        <input type="text" class="form-control" id="title" placeholder="Enter Display name" name="display_name" min="3" max="50">
                  </div>
                  <div class="form-group">
                    <label for="state">Role type</label>
                    <select class="form-control" id="type" name="type">
                        @php 
                        $roleTypes = array("super admin","admin","member","subscription"); @endphp
                        @foreach($roleTypes as $type)
                        @php
                        $typeName = str_replace(' ', '_', $type);
                        @endphp
                        <option value="{{ $typeName }}"> {{$type}} </option>
                        @endforeach
                    </select>
                  </div>
                  <div class="form-group">
                    <label for="state">Description</label>
                    <textarea name="description" class="form-control"></textarea>
                  </div>


                  <div class="form-group">
                    <label for="state">Order</label>
                    <select class="form-control" id="order" name="order">
                      @for($i=0; $i<=10; $i++)
                        <option value="{{ $i }}"> {{$i}} </option>
                      @endfor
                    </select>
                  </div>

                  
                  <div class="form-group">
                    <label for="IngredientsImage">Role Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" required >
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                    </div>
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

