@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Edit Field</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/registration/fields')}}">Register fields</a></li>
          <li class="breadcrumb-item active">Edit Field</li>
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
                <h3 class="card-title">Edit Field</h3>
              </div>

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/registration/field/update',$field->user_field_id)}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Title*</label>
                                <input type="text" class="form-control" name="title_en" value="{{$field->title_en}}" required />
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Title(Italian)</label>
                                <input type="text" class="form-control" name="title_it" value="{{$field->title_it}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Placeholder</label>
                                <input type="text" class="form-control" name="placeholder_en" value="{{$field->placeholder_en}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Placeholder(Italian)</label>
                                <input type="text" class="form-control" name="placeholder_it" value="{{$field->placeholder_it}}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Hint</label>
                                <textarea class="form-control" name="hint_en">{{$field->hint_en}}</textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Hint(Italian)</label>
                                <textarea class="form-control" name="hint_it">{{$field->hint_it}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Field Type</label>
                                <select class="form-control" name="type" required>
                                    <option value="">Select Type</option>
                                    <option {{$field->type == 'text' ? 'selected' : ''}} value="text">Text</option>
                                    <option {{$field->type == 'checkbox' ? 'selected' : ''}} value="checkbox">Checkbox</option>
                                    <option {{$field->type == 'select' ? 'selected' : ''}} value="select">Select</option>
                                    <option {{$field->type == 'radio' ? 'selected' : ''}} value="radio">Radio</option>
                                    <option {{$field->type == 'multiselect' ? 'selected' : ''}} value="multiselect">Multiselect</option>
                                    <option {{$field->type == 'email' ? 'selected' : ''}} value="email">Email</option>
                                    <option {{$field->type == 'password' ? 'selected' : ''}} value="password">Password</option>
                                    <option {{$field->type == 'terms' ? 'selected' : ''}} value="terms">Terms</option>
                                    <option {{$field->type == 'map' ? 'selected' : ''}} value="map">Map</option>
                                    <option {{$field->type == 'hidden' ? 'selected' : ''}} value="hidden">Hidden</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Do you want to required it?</label>
                                <select class="form-control" name="required" required>
                                    <option value="">Select option</option>
                                    <option {{$field->required == 'yes' ? 'selected' : ''}} value="yes">Yes</option>
                                    <option {{$field->required == 'no' ? 'selected' : ''}} value="no">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">It will be conditional?</label>
                                <select class="form-control" name="conditional" required>
                                    <option value="">Select option</option>
                                    <option {{$field->conditional == 'yes' ? 'selected' : ''}} value="yes">Yes</option>
                                    <option {{$field->conditional == 'no' ? 'selected' : ''}} value="no">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">It required on update?</label>
                                <select class="form-control" name="require_update" required>
                                    <option value="">Select option</option>
                                    <option {{$field->require_update == 'true' ? 'selected' : ''}} value="true">Yes</option>
                                    <option {{$field->require_update == 'false' ? 'selected' : ''}} value="false">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Do you want to display on registration?</label>
                                <select class="form-control" name="display_on_registration" required>
                                    <option value="">Select option</option>
                                    <option {{$field->display_on_registration == 'true' ? 'selected' : ''}} value="true">Yes</option>
                                    <option {{$field->display_on_registration == 'false' ? 'selected' : ''}} value="false">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="state">Do you want to display on dashboard?</label>
                                <select class="form-control" name="display_on_dashboard" required>
                                    <option value="">Select option</option>
                                    <option {{$field->display_on_dashboard == 'true' ? 'selected' : ''}} value="true">Yes</option>
                                    <option {{$field->display_on_dashboard == 'false' ? 'selected' : ''}} value="false">No</option>
                                </select>
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

