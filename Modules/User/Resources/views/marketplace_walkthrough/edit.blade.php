@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Marketplace Walkthrough</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/walkthrough')}}">Marketplace Walkthrough</a></li>
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
                <h3 class="card-title">Edit Marketplace Walkthrough</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/market-place/walkthrough/update',$walkthrough->walk_through_screen_id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Title</label>
                          <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title_en" min="3" max="50" value="{{$walkthrough->title_en}}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Title(Italian):</label>
                          <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title_it" min="3" max="50" value="{{$walkthrough->title_it}}">
                        </div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="col-md-6">
                      <div class="form-group">
                          <label>Description:</label>
                            <textarea name="description_en" class="form-control" required>{{$walkthrough->description_en}}
                            </textarea> 
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group">
                          <label>Description(Italian):</label>
                            <textarea name="description_it" class="form-control" required>{{$walkthrough->description_it}}
                            </textarea> 
                      </div>
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Role</label>
                      <select class="form-control" name="role_id">
                        <option value="0">Select role</option>
                        @foreach($roles as $key=>$role)
                          <option {{ $walkthrough->role_id == $role->role_id ? 'selected' : ''}} value="{{$role->role_id}}">{{$role->name}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                     <div class="form-group">
                      <label for="state">Order</label>
                      <select class="form-control" name="order">
                        @for($i=1; $i<=5;$i++)
                          <option {{ $walkthrough->order == $i ? 'selected' : ''}} value="{{$i}}">{{$i}}</option>
                        @endfor
                      </select>
                    </div>
                  </div>
                </div>
                  <div class="form-group">
                    <label for="IngredientsImage">Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" >
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                    </div>
                    <div>
                      @if(!empty($walkthrough->image_id))
                      <img src="{{ $walkthrough->attachment->base_url }}{{ $walkthrough->attachment->attachment_url }}" width="75px">
                      @endif
                    </div>
                  </div>

              <hr />
                  <div>
                    <span style="font-size:18px">Bullet Points</span> 
                    <a href="javascript:void(0)" class="btn btn-primary add_bullets">+</a>
                  </div>

                  <div class="bullet_points">
                    @foreach($points as $key =>  $point)
                    <div class="bullet_point">
                      <label>Point {{ $key + 1}}</label>
                      <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                              <label for="name">Title</label>
                              <input type="text" class="form-control" name="point_title_en[]" placeholder="Enter Title" name="title_en" min="3" max="50" value="{{$point->title_en}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                              <label for="name">Title(Italian):</label>
                              <input type="text" class="form-control" name="point_title_it[]" placeholder="Enter Title" name="title_it" min="3" max="50" value="{{$point->title_it}}">
                            </div>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                          <div class="form-group">
                              <label>Description:</label>
                                <textarea  name="point_description_en[]" class="form-control" required>{{$point->description_en}}</textarea> 
                          </div>
                        </div>
                        <div class="col-md-6">
                          <div class="form-group">
                              <label>Description(Italian):</label>
                                <textarea name="point_description_it[]" class="form-control" required>{{ $point->description_it}}</textarea> 
                          </div>
                        </div>
                      </div>
                      <div class="form-group">
                        <label for="IngredientsImage">Icon</label>
                        <div class="input-group">
                          <div class="custom-file">
                            <input type="file" class="" name="icons[]" accept="image/png, image/jpeg">
                          </div>
                          <div>
                            @if(!empty($point->icon_id))
                            <img src="{{ $point->attachment->base_url }}{{ $point->attachment->attachment_url }}" width="75px">
                            @endif
                            <input type="hidden" value="{{ $point->icon_id }}" name="icon_ids[]">
                          </div>
                        </div>
                      </div>
                    </div>
                    @endforeach
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
<script type="text/javascript">
  $(function(){

    $("body").on("click",".add_bullets",function(){
        var pointCount = $(".bullet_points").find(".bullet_point").length + 1;
        var text = '<div class="bullet_point"><label> Point '+pointCount+'</label><a href="javascript:void(0) "class="remove_point" style:"color:red"> Remove (-)</a><div class="row"><div class="col-md-6"><div class="form-group"><label for="name">Title</label><input type="text" class="form-control" id="point_title_en" placeholder="Enter Title" name="point_title_en[]" min="3" max="50"></div></div><div class="col-md-6"><div class="form-group"><label for="name">Title(Italian):</label><input type="text" class="form-control" name="point_title_it[]" placeholder="Enter Title" min="3" max="50"></div></div></div><div class="row"><div class="col-md-6"><div class="form-group"><label>Description:</label><textarea  name="point_description_en[]" class="form-control" required></textarea></div></div><div class="col-md-6"><div class="form-group"><label>Description(Italian):</label><textarea name="point_description_it[]" class="form-control" required></textarea></div></div></div><div class="form-group"><label for="IngredientsImage">Icon</label><div class="input-group"><div class="custom-file"><input type="file" class="" name="icons[]" accept="image/png, image/jpeg" required ></div></div></div></div></div>';

        $(".bullet_points").append(text);


    });

    $("body").on("click",".remove_point",function(){
      $(this).parent().remove();
    });
  })
</script>
@endsection                        

