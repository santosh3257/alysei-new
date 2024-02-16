@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Discovery circle</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/discover-alysei/discovery-circle')}}">Discovery circle</a></li>
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
                <h3 class="card-title">Add circle</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/discover-alysei/discovery-circle/store')}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Title</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" min="3" max="50" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Title(Italian)</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title_it" min="3" max="50" required>
                            </div>
                        </div>
                    </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="state">Status</label>
                        <select class="form-control" name="status">
                            <option value="1">Publish</option>
                            <option value="0">Draft</option>
                        </select>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                        <label for="state">Category</label>
                        <select class="form-control" name="category_id">
                          <option value="">Select Category</option>
                          @if($postCategories)
                            @foreach($postCategories as $idx=>$cat)
                            <option value="{{$cat->id}}">{{$cat->cat_name}}</option>
                            @endforeach
                          @endif
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="IngredientsImage">News Image</label>
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
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script>
$(document).on("change",'#image', function(){
    var fileName = document.getElementById('image').files[0].name;
    $('.custom-file-label').html(fileName);
    //console.log(fileName);
});
</script>

@endsection            

