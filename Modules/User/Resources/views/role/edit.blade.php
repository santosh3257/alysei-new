@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Role</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users/roles')}}">Roles</a></li>
          <li class="breadcrumb-item active">Role Edit</li>
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
                <h3 class="card-title">Edit Role</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/user/role/update',$role->role_id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                <div class="form-group">
                        <label for="name">Name</label>
                        <input type="text" class="form-control" id="title" placeholder="Enter Name" name="name" value="{{$role->name}}" max="50" required>
                  </div>
                  <div class="form-group">
                        <label for="name">Display name</label>
                        <input type="text" class="form-control" id="title" placeholder="Enter Display name" name="display_name" value="{{$role->display_name}}" min="3" max="50">
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
                        <option {{$role->type == $typeName ? 'selected' : ''}} value="{{ $typeName }}"> {{$type}} </option>
                        @endforeach
                    </select>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="state">Description</label>
                        <textarea name="description_en" class="form-control">{{$role->description_en}}</textarea>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="state">Description(Italian)</label>
                        <textarea name="description_it" class="form-control">{{$role->description_it}}</textarea>
                      </div>
                    </div>
                  </div>




                  <div class="form-group">
                    <label for="state">Order</label>
                    <select class="form-control" id="order" name="order">
                      @for($i=0; $i<=10; $i++)
                        <option {{ $role->order == $i ? 'selected' : ''}} value="{{ $i }}"> {{$i}} </option>
                      @endfor
                    </select>
                  </div>

                  
                  <div class="form-group">
                    <label for="IngredientsImage">Role Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" >
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                    </div>
                    @if(!empty($role->image_id))
                    <img src="{{ $role->attachment->base_url }}{{ $role->attachment->attachment_url }}" width="75px">
                    @endif
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

