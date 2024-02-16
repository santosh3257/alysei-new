@extends('admin.layouts.app')
@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Banner</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/marketplace/banners')}}">Banners</a></li>
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
                <h3 class="card-title">Edit Banner</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/marketplace/banner/update',$banner->marketplace_banner_id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                          <label for="name">Title</label>
                          <input type="text" class="form-control" id="title" placeholder="Enter Title" name="title" min="3" max="50" value="{{$banner->title}}">
                        </div>
                    </div>
                  </div>
                
                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="state">Banner Type</label>
                      <select class="form-control" name="type">
                        <option {{$banner->type == 1 ? 'selected' : ''}} value="1">For Top</option>
                        <option {{$banner->type == 2 ? 'selected' : ''}} value="2">For Bottom</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-12">
                     <div class="form-group">
                      <label for="state">Status</label>
                      <select class="form-control" name="status">
                        <option {{$banner->status == 0 ? 'selected' : ''}} value="0">Inactive</option>
                        <option {{$banner->status == 1 ? 'selected' : ''}} value="1">Active</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div>
                  @if(!empty($banner->image_id))
                  <img src="{{ $banner->attachment->base_url }}{{ $banner->attachment->attachment_url }}" width="75px">
                  @endif
                </div>
                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group" style="margin:0px;">
                      <label for="IngredientsImage">Click in the box to upload a new image (Image should be PNG, JPG and JPEG format only)</label>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <div class="input-group browseImage">
                          <div id="image-cropper" style="border:1px solid #ccc; margin: 5px;"></div>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div id="image-cropper-result"><img></div>
                    <input type="hidden" name="crop_image" value="" class="crop_image">
                  </div>
                </div>
                <p><input type="button" value="Get cropped image" id="image-getter"></p>
                
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
<script type="text/javascript">
$.ajaxSetup({
headers: {
    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
}
 });


 var crop = cropper(document.getElementById('image-cropper'), {
    area: [ 480, 320 ],
    crop: [ 359, 242 ],
})

document.getElementById('image-getter').onclick = function() {
  $(".crop_image").val(document.getElementById('image-cropper').crop.getCroppedImage().src);
    document.getElementById('image-cropper-result').children[0].src = document.getElementById('image-cropper').crop.getCroppedImage().src;  
}
// var resize = $('#upload-demo').croppie({
//     enableExif: false,
//     enableOrientation: false,    
//     viewport: { // Default { width: 100, height: 100, type: 'square' } 
//         width: 359,
//         height: 242,
//         /* type: 'circle' */ //square
//         type: 'square'
//     },
//     boundary: {
//         width: 400,
//         height: 400
//     }
// });
// $('#image').on('change', function () {
//   $('#upload-demo').css("display","block");
//   $('.upload-image').css("display","block");
//   var reader = new FileReader();
//     reader.onload = function (e) {
//       resize.croppie('bind',{
//         url: e.target.result
//       }).then(function(){
//         console.log('jQuery bind complete');
//       });
//     }
//     reader.readAsDataURL(this.files[0]);
// });
// $('.upload-image').on('click', function (ev) {
//   ev.preventDefault();
//   resize.croppie('result', {
//     type: 'canvas',
//     size: 'viewport',
//     quality: 1
//   }).then(function (img) {
//     html = '<img src="' + img + '" />';
//     $('#upload-demo').css("display","none");
//     $('#preview-crop-image').css("display","block");
//     $('.upload-image').css("display","none");
//     $("#preview-crop-image").html(html);
//     $(".crop_image").val(img);
//   });
// });
</script>
@endsection            

