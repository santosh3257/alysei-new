@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Hub</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users')}}">Users</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/users/hubs')}}">Diets</a></li>
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
                <h3 class="card-title">Edit Hub</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/user/hub/update',['id'=>$id])}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" class="form-control" id="title" placeholder="Enter Name" name="title" min="3" max="50" value="{{ $hub->title }}" required>
                  </div>
                  <div class="form-group">
                    <label>Location/City/Address</label>
                    <input type="text" name="autocomplete" value="{{ $hub->autocomplete }}" id="autocomplete" class="form-control" placeholder="Choose Location" disabled>
                </div>
                 <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <select name="radius" class="form-control mapRadius">
                        <option {{$hub->radius == 5 ? 'selected' : ''}} value="5">5 (Miles)</option>
                        <option {{$hub->radius == 10 ? 'selected' : ''}} value="10">10 (Miles)</option>
                        <option {{$hub->radius == 20 ? 'selected' : ''}} value="20">20 (Miles)</option>
                        <option {{$hub->radius == 30 ? 'selected' : ''}} value="30">30 (Miles)</option>
                        <option {{$hub->radius == 40 ? 'selected' : ''}} value="40">40 (Miles)</option>
                        <option {{$hub->radius == 50 ? 'selected' : ''}} value="50">50 (Miles)</option>
                        <option {{$hub->radius == 60 ? 'selected' : ''}} value="60">60 (Miles)</option>
                        <option {{$hub->radius == 70 ? 'selected' : ''}} value="70">70 (Miles)</option>
                        <option {{$hub->radius == 80 ? 'selected' : ''}} value="80">80 (Miles)</option>
                        <option {{$hub->radius == 90 ? 'selected' : ''}} value="90">90 (Miles)</option>
                        <option {{$hub->radius == 100 ? 'selected' : ''}} value="100">100 (Miles)</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label for="country">Country</label>
                      <input type="text" name="country" value="{{ $hub->country->name }}" class="form-control" id="country" readonly/>
                    </div>

                    <div class="form-group">
                      <label for="state">State</label>
                      <input type="text" name="state" value="{{ $hub->state->name }}" class="form-control" id="state" readonly/>
                    </div>
                    <div class="form-group">
                      <label for="state">City</label>
                      <input type="text" name="city" value="{{ $hub->city->name }}" class="form-control" id="city" readonly/>
                    </div>
                    <input type="hidden" id="latitude" name="latitude" value="{{$hub->latitude}}">
                    <input type="hidden" name="longitude" id="longitude" value="{{$hub->longitude}}">
                  </div>
                  <div class="col-md-8">
                      <!-- /.card-body -->
                      <div class="hub_map">
                        <div id="map"></div>
                      </div>
                  </div>
                </div>
                  <div class="form-group">
                    <label for="hubsImage">Hub Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg" >
                        <label class="custom-file-label" for="hubsImage">Choose file</label>
                      </div>
                    </div>
                    <div>
                      <img src="{{ $hub->attachment->base_url }}{{ $hub->attachment->attachment_url }}" width="75px">
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
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.0.0/jquery.min.js"></script>
<script type="text/javascript"
        src="https://maps.google.com/maps/api/js?key=AIzaSyBVNQEQqCG-NFtXnwT7g4BAwT6yWN67J68&libraries=places"></script>
<script>

var address = "<?php echo $hub->autocomplete; ?>";
var lat = "<?php echo $hub->latitude; ?>";
var lang = "<?php echo $hub->longitude; ?>";
var miles = <?php echo $hub->radius; ?>;
drawCircle(address, lat, lang, miles);

var componentForm = {
        administrative_area_level_1: 'long_name',
        locality: 'long_name',
        country: 'long_name',
    };
 google.maps.event.addDomListener(window, 'load', initialize);
    function initialize() {
        var input = document.getElementById('autocomplete');
        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
            //console.log(place);
          for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
               
                if (addressType == "administrative_area_level_1") {
                    $('#state').val(place.address_components[i][componentForm[addressType]]);
                    
                }
                else if (addressType == "locality") {
                   $('#city').val(place.address_components[i][componentForm[addressType]]);
                }
                else if (addressType == "country") {
                    //$('#country').val(place.address_components[i][componentForm[addressType]]);
                    $('#country').val(place.address_components[i][componentForm[addressType]] == 'Puerto Rico' ? 'United States' : place.address_components[i][componentForm[addressType]]);
                }
            }
            $('#latitude').val(place.geometry['location'].lat().toFixed(3));
            $('#longitude').val(place.geometry['location'].lng().toFixed(3));
            $("#latitudeArea").removeClass("d-none");
            $("#longtitudeArea").removeClass("d-none");
            drawCircle(place.formatted_address, place.geometry['location'].lat().toFixed(3), place.geometry['location'].lng().toFixed(3), $('.mapRadius').val());
        });
    }

  var componentForm = {
        administrative_area_level_1: 'long_name',
        locality: 'long_name',
        country: 'long_name',
    };
function drawCircle(address, lat, lang, miles) {
    geocoder = new google.maps.Geocoder();
    var meters = miles*1609.344;
    var contentCenter = '<span class="infowin">'+address+' (draggable)</span>';
    var latLngCenter = new google.maps.LatLng(lat, lang),
        map = new google.maps.Map(document.getElementById('map'), {
            zoom: 10,
            center: latLngCenter,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            mapTypeControl: false
        }),
        markerCenter = new google.maps.Marker({
            position: latLngCenter,
            title: 'Location',
            map: map,
            draggable: true
        }),
        infoCenter = new google.maps.InfoWindow({
            content: contentCenter
        }),
       
        // Assumes that your map is signed to the var "map"
        // Also assumes that your marker is named "marker"
        circle = new google.maps.Circle({
            map: map,
            clickable: false,
            // metres
            radius: meters,
            fillColor: '#007bff',
            fillOpacity: .6,
            strokeColor: '#313131',
            strokeOpacity: .4,
            strokeWeight: .8
        });
    // attach circle to marker
    circle.bindTo('center', markerCenter, 'position');

    var
    // get the Bounds of the circle
    bounds = circle.getBounds();
  

    // get some latLng object and Question if it's contained in the circle:
    google.maps.event.addListener(markerCenter, 'dragend', function() {
        latLngCenter = new google.maps.LatLng(markerCenter.position.lat(), markerCenter.position.lng());
        bounds = circle.getBounds();
        var lat = markerCenter.position.lat().toFixed(3);
        var lng = markerCenter.position.lng().toFixed(3);
        $('#latitude').val(lat);
        $('#longitude').val(lng);
        $("#latitudeArea").removeClass("d-none");
        $("#longtitudeArea").removeClass("d-none");
        $.get({ url: `https://maps.googleapis.com/maps/api/geocode/json?latlng=${lat},${lng}&sensor=false&key=AIzaSyBVNQEQqCG-NFtXnwT7g4BAwT6yWN67J68`, success(data) {
              //console.log(data.results);
             var address = data.results[0].formatted_address;
             for (var i = 0; i < data.results[0].address_components.length; i++) {
                var addressType = data.results[0].address_components[i].types[0];
               
                if (addressType == "administrative_area_level_1") {
                    $('#state').val(data.results[0].address_components[i][componentForm[addressType]]);
                    
                }
                else if (addressType == "locality") {
                   $('#city').val(data.results[0].address_components[i][componentForm[addressType]]);
                }
                else if (addressType == "country") {
                    //$('#country').val(data.results[0].address_components[i][componentForm[addressType]]);
                    $('#country').val(place.address_components[i][componentForm[addressType]] == 'Puerto Rico' ? 'United States' : place.address_components[i][componentForm[addressType]]);
                }
            }
             $('#autocomplete').val(address);

        }});
        
        // console.log("lat : "+markerCenter.position.lat().toFixed(3));
        // console.log("lat : "+markerCenter.position.lng().toFixed(3));
        // console.log(geocodePosition(markerCenter.position.lat().toFixed(3)));
    });

   

    google.maps.event.addListener(markerCenter, 'click', function() {
        infoCenter.open(map, markerCenter);
    });
}
</script>
<style>
  .hub_map {
    position: relative;
    overflow: hidden;
    width: 100%;
    height: 400px;
}
div#map {
    position: initial !important;
}
</style>
@endsection            

  