@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Recipe Course</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe')}}">Recipe</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/recipe/courses')}}">Courses</a></li>
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
                <h3 class="card-title">Edit Course</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/recipe/course/update',['id'=>$id])}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Name</label>
                          <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name_en" min="3" max="50" value="{{ $course->name_en }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Name(Italian)</label>
                          <input type="text" class="form-control" id="name" placeholder="Enter Name" name="name_it" min="3" max="50" value="{{ $course->name_it }}">
                        </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="name">Priority</label>
                    <input type="number" class="form-control" id="priority" placeholder="Enter Priority" name="priority" value="{{ $course->priority }}" required>
                  </div>

                  <div class="form-check hidden">
                    <input type="checkbox" class="form-check-input" id="Featured"  name="featured" {{ ($course->featured == '1') ? "checked" : "" }}>
                    <label class="form-check-label" for="Featured" >Featured</label>
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

  