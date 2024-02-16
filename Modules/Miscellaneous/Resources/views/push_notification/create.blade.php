@extends('admin.layouts.app')

@section('content')
<section class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1>Send Notification</h1>
      </div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="{{url('dashboard')}}">Home</a></li>
          <li class="breadcrumb-item"><a href="{{url('dashboard/push-notifications')}}">All Notification</a></li>
          <li class="breadcrumb-item active">Send</li>
        </ol>
      </div>
    </div>
  </div><!-- /.container-fluid -->
</section>
<style>
  .notifications .btn-group {
    width: 100% !important;
    text-align: left;
}
.notifications ul.multiselect-container {
    width: 100%;
    padding: 15px;
    overflow-y: scroll;
    max-height: 300px;
}
.notifications .multiselect.dropdown-toggle {
    text-align: left;
    overflow: hidden;
}
.notifications .dropdown-toggle::after {
    position: absolute;
    right: 10px;
    top: 15px;
}
button.btn.btn-default.multiselect-clear-filter {
    display: none;
}
li.multiselect-item.filter {
    margin-bottom: 10px;
}
</style>
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

              <!-- /.card-header -->
              <!-- form start -->
              <form method="post" action="{{url('dashboard/push-notification/save')}}" enctype='multipart/form-data'>
                {{ csrf_field() }}
                <div class="card-body">
                  <div class="row">
                  <div class="col-md-6">
                      <div class="form-group notifications">
                        <label for="name">Notification Title(English)</label>
                        <input type="text" class="form-control" name="cron_job_title" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="form-group notifications">
                        <label for="name">Notification Title(Italian)</label>
                        <input type="text" class="form-control" name="cron_job_title_it" required>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="form-group">
                          <label for="name">
                            Notification Type
                          </label>
                          <select id="notificationType" class="form-control" name="notificationType">
                            <option value="role">Role Wise</option>
                            <option value="user">User Wise</option>
                          </select>
                      </div>
                    </div>
                    <div class="col-md-8">
                      <div class="form-group notifications roles">
                        <label for="name">Select Roles</label>
                        <select id="multiselect" multiple="multiple" class="form-control" name="roles[]">
                          @if($roles)
                            @foreach($roles as $key=>$role)
                            <option value="{{$role->role_id}}">{{$role->name}}</option>
                            @endforeach
                          @endif
                      </select>
                      </div>
                      <div class="form-group notifications users" style='display:none;'>
                        <label for="name">Select Users</label>
                        <select id="multiselects" multiple="multiple" class="form-control" name="users[]">
                          @if($users)
                            @foreach($users as $key=>$user)
                            <option value="{{$user->user_id}}">
                              @if($user->first_name != '')
                                {{$user->first_name}} {{$user->last_name}}
                              @else
                              {{$user->name}}
                              @endif
                              ({{$user->email}})
                            </option>
                            @endforeach
                          @endif
                      </select>
                      </div>
                    </div>
                    <!-- <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Message Body(English)</label>
                          <textarea class="form-control" placeholder="Write Message" name="message_en" required>{{old('message_en')}}</textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                          <label for="name">Message Body(Italian)</label>
                          <textarea class="form-control" placeholder="Write Message" name="message_it" required></textarea>
                        </div>
                    </div>
                </div> -->
                <!-- <div class="form-group">
                    <label for="IngredientsImage">Upload Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="image" name="image" accept="image/png, image/jpeg">
                        <label class="custom-file-label" for="IngredientsImage">Choose file</label>
                      </div>
                </div> -->
                <!-- /.card-body -->

                <div class="card-footer text-center">
                  <button type="submit" class="btn btn-primary">Send</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
      </div>
  </div>
</section>
@endsection            

