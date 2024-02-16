@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Add dicovery circle</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/discovery-circle')}}">Discovery circle</a></li>
          <li class="breadcrumb-item active">Edit News</li>
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
                <h3 class="card-title">Edit discovery circle</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/discover-alysei/discovery-circle/update',$news->discover_alysei_id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Title</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" min="3" max="50" value="{{$news->title}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="name">Title(Italian)</label>
                                <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title_it" min="3" max="50" value="{{$news->title_it}}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                      <div class="form-group">
                          <label for="state">Status</label>
                          <select class="form-control" name="status">
                              <option {{$news->status == '1' ? 'selected' : ''}} value="1">Publish</option>
                              <option {{$news->status == '0' ? 'selected' : ''}} value="0">Draft</option>
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
                            <option {{$news->category_id == $cat->id ? 'selected' : ''}} value="{{$cat->id}}">{{$cat->cat_name}}</option>
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
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" >
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                    </div>
                    <div>
                      @if(!empty($news->image_id))
                      <img src="{{ $news->attachment->base_url }}{{ $news->attachment->attachment_url }}" width="75px">
                      @endif
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
 <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script>
$(document).on("change",'#image', function(){
    var fileName = document.getElementById('image').files[0].name;
    $('.custom-file-label').html(fileName);
    //console.log(fileName);
});
</script>

@endsection            

